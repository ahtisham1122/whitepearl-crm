<?php

namespace App\Console\Commands;

use App\Exceptions\DryRunAbort;
use App\Services\BlockAImportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportBlockACommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'import:blocka
        {--dry-run : Preview the import and print a report -- nothing is saved}
        {--path=storage/imports/blockA_import_ready.csv : Path to the CSV file, relative to the project root}
        {--import-date= : Date recorded on every legacy payment/installment (YYYY-MM-DD). Defaults to today.}';

    /**
     * @var string
     */
    protected $description = 'Import Block A plots, clients, bookings, and legacy payments from the cleaned CSV.';

    public function handle(BlockAImportService $importer): int
    {
        $path = base_path($this->option('path'));
        $dryRun = (bool) $this->option('dry-run');
        $importDate = $this->option('import-date')
            ? Carbon::parse($this->option('import-date'))
            : today();

        $this->newLine();
        $this->line($dryRun
            ? '<fg=yellow;options=bold>DRY RUN -- nothing will be saved.</>'
            : '<fg=red;options=bold>REAL IMPORT -- this WILL write to the database.</>');
        $this->line("File:              {$path}");
        $this->line('Import date:       '.$importDate->format('d M Y').' (used on every legacy payment/installment)');
        $this->newLine();

        $this->line('Checking the file for problems before touching the database...');
        $issues = $importer->validate($path);

        if (! empty($issues)) {
            $this->newLine();
            $this->error('Import stopped -- '.count($issues).' problem(s) found. Nothing was created.');
            foreach ($issues as $issue) {
                $this->line("  - {$issue}");
            }

            return self::FAILURE;
        }

        $this->info('File looks clean.');

        if (! $dryRun) {
            $this->newLine();
            $this->warn('This will permanently create real plots, clients, bookings, and payments in the live database.');

            if (! $this->confirm('Are you sure you want to run the REAL import now?', false)) {
                $this->line('Cancelled. Nothing was touched.');

                return self::FAILURE;
            }
        }

        $report = null;

        try {
            DB::transaction(function () use ($importer, $path, $importDate, $dryRun, &$report) {
                $report = $importer->import($path, $importDate);

                if ($dryRun) {
                    // Discard everything this transaction just did -- see
                    // DryRunAbort's docblock for why this is safe/expected.
                    throw new DryRunAbort();
                }
            });
        } catch (DryRunAbort) {
            // Expected for a dry run; the report was already captured above.
        }

        $this->printReport($report, $dryRun);

        if ($dryRun) {
            $this->newLine();
            $this->comment('This was a DRY RUN. Nothing was saved -- the database is untouched.');
            $this->comment('When you\'re ready, run: php artisan import:blocka');
        } else {
            $this->newLine();
            $this->info('Real import complete and committed.');
        }

        return self::SUCCESS;
    }

    private function printReport(array $report, bool $dryRun): void
    {
        $verb = $dryRun ? 'WOULD CREATE' : 'CREATED';

        $this->newLine();
        $this->line("<options=bold>{$verb}:</>");
        $this->table(['Thing', 'Count'], [
            ['Blocks', $report['blocks_created']],
            ['Plots', $report['plots_created']],
            ['Clients (new)', $report['clients_created']],
            ['Clients (reused -- already owned another plot)', $report['clients_reused']],
            ['Bookings', $report['bookings_created']],
            ['Installment plans', $report['installment_plans_created']],
            ['Installments -- Paid', $report['installments_paid']],
            ['Installments -- Pending (remaining balance)', $report['installments_pending']],
            ['Payments -- Booking Payment (downpayments)', $report['payments_booking']],
            ['Payments -- Installment Payment', $report['payments_installment']],
        ]);

        $this->newLine();
        $this->line('<options=bold>TOTAL MONEY RECORDED AS COLLECTED:</> Rs. '.number_format($report['total_collected'], 2));
        $this->line('(down payments + every non-zero installment amount, client-attached rows only)');

        $this->newLine();
        $this->line('<options=bold>CLIENT LOGINS:</>');
        $this->line('  '.count($report['clients_with_login']).' client(s) would get an auto-generated portal password.');

        if (! empty($report['clients_without_login'])) {
            foreach ($report['clients_without_login'] as $client) {
                $this->line("  {$client['name']} ({$client['cnic']}, {$client['client_id']}) -- record created, NO working login (placeholder CNIC).");
            }
        }

        if (! $dryRun && ! empty($report['clients_with_login'])) {
            $this->newLine();
            $this->line('<options=bold;fg=yellow>GENERATED PASSWORDS -- save these now, they cannot be recovered later:</>');
            $this->table(
                ['Client ID', 'Name', 'CNIC (username)', 'Password'],
                array_map(fn ($c) => [$c['client_id'], $c['name'], $c['cnic'], $c['password']], $report['clients_with_login'])
            );

            $this->writeCredentialsFile($report['clients_with_login']);
        }

        if (! empty($report['notes'])) {
            $this->newLine();
            $this->line('<options=bold>NOTES:</>');
            foreach (array_unique($report['notes']) as $note) {
                $this->line("  - {$note}");
            }
        }

        $this->newLine();
        $this->line('<options=bold>ASSUMPTIONS APPLIED (review before trusting the data further):</>');
        foreach ($this->assumptions() as $assumption) {
            $this->line("  - {$assumption}");
        }
    }

    /**
     * @return list<string>
     */
    private function assumptions(): array
    {
        return [
            'plot_size: not in the CSV -- every imported plot is set to "Unknown" (fix later via Edit Plot).',
            'category: not in the CSV -- every imported plot defaults to "Residential" (fix later via Edit Plot).',
            'phone: not in the CSV -- every imported client gets a placeholder "N/A" (fix later via Edit Client).',
            'payment_method: not in the CSV -- every legacy payment is recorded as "Cash".',
            'Booking status: Sold/Fully Paid/Disputed rows -> Active; Cancelled rows -> Cancelled.',
            'Every legacy installment/payment is dated using the single import date above -- the CSV has no real historical dates.',
            'A late fee in the CSV is added to the plot\'s pending "remaining balance" installment, never recorded as its own installment.',
        ];
    }

    /**
     * Console scrollback is not a safe place to leave 40+ passwords. Save
     * them to a local, git-ignored file (storage/imports is already
     * excluded from version control) so nothing is lost, then say so
     * loudly -- this file must be deleted once the passwords have been
     * shared with clients.
     */
    private function writeCredentialsFile(array $clients): void
    {
        $path = storage_path('imports/blockA_generated_logins_'.now()->format('Y-m-d_His').'.csv');

        $lines = ["client_id,name,cnic,password"];
        foreach ($clients as $c) {
            $lines[] = implode(',', [$c['client_id'], '"'.str_replace('"', '""', $c['name']).'"', $c['cnic'], $c['password']]);
        }

        file_put_contents($path, implode(PHP_EOL, $lines).PHP_EOL);

        $this->newLine();
        $this->warn("Passwords were also saved to: {$path}");
        $this->warn('This file is NOT sent to git, but it is plain text. Delete it once you have shared the logins with clients.');
    }
}
