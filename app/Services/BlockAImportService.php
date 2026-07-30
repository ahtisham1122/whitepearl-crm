<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Installment;
use App\Models\InstallmentPlan;
use App\Models\Payment;
use App\Models\Plot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Reads the cleaned Block A CSV and turns it into Block/Plot/Client/
 * Booking/InstallmentPlan/Installment/Payment rows.
 *
 * This class only ever CREATES rows -- it never updates or deletes an
 * existing one. It does not manage its own database transaction; the
 * console command that calls import() wraps it in one, so a dry run can
 * run this exact same code and then throw the whole thing away.
 */
class BlockAImportService
{
    /**
     * This CNIC is a known placeholder for a disputed plot with no real
     * identifying paperwork -- the client record is still created (so the
     * plot/booking/payment history is preserved), but it never gets a
     * working portal login.
     */
    private const DISPUTED_PLACEHOLDER_CNIC = 'NA-18-A';

    private const IMPORT_NOTE = 'Imported from Block A spreadsheet.';

    private const LEGACY_PAYMENT_METHOD = 'Cash';

    private const DEFAULT_PLOT_SIZE = 'Unknown';

    private const DEFAULT_CATEGORY = 'Residential';

    private const DEFAULT_PHONE = 'N/A';

    private const BLOCK_NAME = 'A';

    public function __construct(
        private readonly BookingService $bookingService,
        private readonly PaymentService $paymentService,
        private readonly ClientService $clientService,
    ) {
    }

    /**
     * Read-only sanity pass over the whole file. Returns a list of plain
     * English problem descriptions -- empty means the file is clean and
     * import() is safe to run. Nothing is written to the database here.
     */
    public function validate(string $path): array
    {
        $rows = $this->parseRows($path);
        $issues = [];
        $seenPlotNumbers = [];
        $trashedCnicsSeen = [];

        foreach ($rows as $row) {
            $line = $row['line'];
            $plotNumber = $row['plot_number'];

            if ($plotNumber === '') {
                $issues[] = "Line {$line}: missing plot_number.";

                continue;
            }

            if (isset($seenPlotNumbers[$plotNumber])) {
                $issues[] = "Line {$line}: plot_number '{$plotNumber}' is a duplicate of line {$seenPlotNumbers[$plotNumber]} in this same file.";
            }
            $seenPlotNumbers[$plotNumber] = $line;

            if (Plot::whereHas('block', fn ($q) => $q->where('name', self::BLOCK_NAME))
                ->where('plot_number', $plotNumber)
                ->exists()) {
                $issues[] = "Line {$line}: plot '{$plotNumber}' already exists in Block ".self::BLOCK_NAME.' -- has this file already been imported?';
            }

            if (! in_array($row['status'], Plot::STATUSES, true)) {
                $issues[] = "Line {$line} ({$plotNumber}): unrecognized status '{$row['status']}'. Allowed: ".implode(', ', Plot::STATUSES).'.';
            }

            if ($row['client_attached']) {
                if ($row['cnic'] === '') {
                    $issues[] = "Line {$line} ({$plotNumber}): client_attached is yes but CNIC is empty.";
                }

                if ($row['client_name'] === '') {
                    $issues[] = "Line {$line} ({$plotNumber}): client_attached is yes but client_name is empty.";
                }

                if ($row['cnic'] !== '' && ! isset($trashedCnicsSeen[$row['cnic']])) {
                    $trashedCnicsSeen[$row['cnic']] = true;

                    $trashedClient = Client::withTrashed()->where('cnic', $row['cnic'])->first();

                    if ($trashedClient && $trashedClient->trashed()) {
                        $issues[] = "Line {$line} ({$plotNumber}): CNIC '{$row['cnic']}' belongs to a deleted client ({$trashedClient->full_name}) -- restore that client first or handle this row manually.";
                    }
                }
            }
        }

        if ($rows->isEmpty()) {
            $issues[] = 'The file has no data rows.';
        }

        return $issues;
    }

    /**
     * Perform the import and return a report of what was created. The
     * caller decides whether the surrounding transaction actually commits
     * (real run) or gets rolled back (dry run) -- this method behaves
     * identically either way, which is what makes the dry-run preview
     * trustworthy.
     */
    public function import(string $path, Carbon $importDate): array
    {
        $rows = $this->parseRows($path);

        $report = [
            'blocks_created' => 0,
            'plots_created' => 0,
            'clients_created' => 0,
            'clients_reused' => 0,
            'bookings_created' => 0,
            'installment_plans_created' => 0,
            'installments_paid' => 0,
            'installments_pending' => 0,
            'payments_booking' => 0,
            'payments_installment' => 0,
            'total_collected' => 0.0,
            'clients_with_login' => [],
            'clients_without_login' => [],
            'notes' => [],
        ];

        $block = Block::firstOrCreate(['name' => self::BLOCK_NAME]);

        if ($block->wasRecentlyCreated) {
            $report['blocks_created']++;
        }

        foreach ($rows as $row) {
            $this->importRow($row, $block, $importDate, $report);
        }

        return $report;
    }

    private function importRow(array $row, Block $block, Carbon $importDate, array &$report): void
    {
        $plotNumber = $row['plot_number'];
        $isCompanyOwned = ! $row['client_attached'];

        $plot = Plot::create([
            'block_id' => $block->id,
            'plot_number' => $plotNumber,
            'plot_size' => self::DEFAULT_PLOT_SIZE,
            'category' => self::DEFAULT_CATEGORY,
            'plot_price' => $row['sale_price'],
            'development_charges' => 0,
            'extra_charges' => 0,
            'total_price' => $row['sale_price'],
            'status' => $row['status'],
            'remarks' => $isCompanyOwned ? $row['remarks'] : null,
        ]);
        $report['plots_created']++;

        if ($row['sale_price'] <= 0) {
            $report['notes'][] = "Plot {$plotNumber}: sale_price is 0 in the source file -- imported as-is (Rs. 0), fix the price later via Edit Plot if it should have one.";
        }

        if ($isCompanyOwned) {
            return;
        }

        $client = Client::where('cnic', $row['cnic'])->first();

        if ($client) {
            $report['clients_reused']++;
        } else {
            $client = Client::create([
                'client_id' => $this->clientService->generateClientId(),
                'full_name' => $row['client_name'],
                'cnic' => $row['cnic'],
                'phone' => self::DEFAULT_PHONE,
                'city' => $row['city'],
                'address' => $row['address'],
                'country' => 'Pakistan',
                'status' => 'Active',
                'notes' => self::IMPORT_NOTE.' ('.$importDate->format('d M Y').')',
            ]);
            $report['clients_created']++;

            if ($row['cnic'] === self::DISPUTED_PLACEHOLDER_CNIC) {
                $report['clients_without_login'][] = [
                    'client_id' => $client->client_id,
                    'name' => $client->full_name,
                    'cnic' => $client->cnic,
                ];
            } else {
                $password = $this->clientService->generatePassword($client);
                $report['clients_with_login'][] = [
                    'client_id' => $client->client_id,
                    'name' => $client->full_name,
                    'cnic' => $client->cnic,
                    'password' => $password,
                ];
            }
        }

        $bookingStatus = $row['status'] === 'Cancelled' ? 'Cancelled' : 'Active';

        $booking = Booking::create([
            'booking_number' => $this->bookingService->generateBookingNumber(),
            'client_id' => $client->id,
            'plot_id' => $plot->id,
            'booking_date' => $importDate,
            'booking_type' => 'Sale',
            'total_price' => $row['sale_price'],
            'discount' => 0,
            'final_price' => $row['sale_price'],
            'booking_amount' => $row['downpayment'],
            'sales_person' => null,
            'remarks' => $row['remarks'],
            'status' => $bookingStatus,
        ]);
        $report['bookings_created']++;

        $paidAmounts = array_values(array_filter($row['installments'], fn (float $amount) => $amount > 0));
        $totalPaidTowardPlan = array_sum($row['installments']);
        $remaining = round($row['sale_price'] - ($row['downpayment'] + $totalPaidTowardPlan), 2);
        $hasRemaining = $remaining > 0.01;

        $plan = InstallmentPlan::create([
            'booking_id' => $booking->id,
            'plan_type' => 'Custom',
            'number_of_installments' => count($paidAmounts) + ($hasRemaining ? 1 : 0),
            'start_date' => $importDate,
            'total_amount' => round($row['sale_price'] - $row['downpayment'], 2),
            'status' => $hasRemaining ? 'Active' : 'Completed',
        ]);
        $report['installment_plans_created']++;

        $installmentNumber = 0;
        $paidInstallments = [];

        foreach ($paidAmounts as $amount) {
            $installmentNumber++;

            $paidInstallments[] = Installment::create([
                'installment_plan_id' => $plan->id,
                'installment_number' => $installmentNumber,
                'due_date' => $importDate,
                'amount' => $amount,
                'late_fee' => 0,
                'paid_amount' => $amount,
                'status' => 'Paid',
                'paid_date' => $importDate,
            ]);
            $report['installments_paid']++;
        }

        // The plot's own late fee (currently only plot 2-A) is a charge on
        // top of what's still owed, not a payment -- it goes on the
        // pending "remaining balance" installment if there is one, or
        // (only if the plan is otherwise fully paid) onto the last paid
        // installment as a record that a fee was charged.
        if ($hasRemaining) {
            $installmentNumber++;

            Installment::create([
                'installment_plan_id' => $plan->id,
                'installment_number' => $installmentNumber,
                'due_date' => $importDate,
                'amount' => $remaining,
                'late_fee' => $row['late_fee'],
                'paid_amount' => 0,
                'status' => 'Pending',
                'paid_date' => null,
            ]);
            $report['installments_pending']++;
        } elseif ($row['late_fee'] > 0 && ! empty($paidInstallments)) {
            end($paidInstallments)->increment('late_fee', $row['late_fee']);
        }

        if ($row['downpayment'] > 0) {
            Payment::create([
                'receipt_number' => $this->paymentService->generateReceiptNumber(),
                'booking_id' => $booking->id,
                'installment_id' => null,
                'payment_date' => $importDate,
                'amount' => $row['downpayment'],
                'payment_method' => self::LEGACY_PAYMENT_METHOD,
                'payment_type' => 'Booking Payment',
                'late_fee' => 0,
                'reference_no' => null,
                'remarks' => self::IMPORT_NOTE,
                'is_legacy' => true,
            ]);
            $report['payments_booking']++;
            $report['total_collected'] += $row['downpayment'];
        }

        foreach ($paidInstallments as $installment) {
            Payment::create([
                'receipt_number' => $this->paymentService->generateReceiptNumber(),
                'booking_id' => $booking->id,
                'installment_id' => $installment->id,
                'payment_date' => $importDate,
                'amount' => $installment->amount,
                'payment_method' => self::LEGACY_PAYMENT_METHOD,
                'payment_type' => 'Installment Payment',
                'late_fee' => 0,
                'reference_no' => null,
                'remarks' => self::IMPORT_NOTE,
                'is_legacy' => true,
            ]);
            $report['payments_installment']++;
            $report['total_collected'] += (float) $installment->amount;
        }
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function parseRows(string $path): Collection
    {
        if (! is_file($path)) {
            throw new RuntimeException("Import file not found: {$path}");
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            throw new RuntimeException("Import file is empty: {$path}");
        }

        $header = array_map('trim', $header);
        $rows = collect();
        $lineNumber = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $columns = count($header) === count($data)
                ? array_combine($header, $data)
                : array_combine(array_slice($header, 0, count($data)), $data);

            $rows->push($this->normalizeRow($columns, $lineNumber));
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeRow(array $row, int $lineNumber): array
    {
        $installments = [];

        for ($i = 1; $i <= 8; $i++) {
            $installments[] = $this->toNumber($row["inst{$i}"] ?? '0');
        }

        return [
            'line' => $lineNumber,
            'plot_number' => trim($row['plot_number'] ?? ''),
            'status' => trim($row['status'] ?? ''),
            'client_attached' => strtolower(trim($row['client_attached'] ?? '')) === 'yes',
            'client_name' => trim($row['client_name'] ?? ''),
            'cnic' => trim($row['cnic'] ?? ''),
            'city' => trim($row['city'] ?? '') ?: null,
            'address' => trim($row['address'] ?? '') ?: null,
            'sale_price' => $this->toNumber($row['sale_price'] ?? '0'),
            'downpayment' => $this->toNumber($row['downpayment'] ?? '0'),
            'installments' => $installments,
            'late_fee' => $this->toNumber($row['late_fee'] ?? '0'),
            'remarks' => trim($row['remarks'] ?? '') ?: null,
        ];
    }

    private function toNumber(string $value): float
    {
        return (float) str_replace(',', '', $value);
    }
}
