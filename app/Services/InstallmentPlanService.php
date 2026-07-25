<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Installment;
use App\Models\InstallmentPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InstallmentPlanService
{
    /**
     * Create a plan for a booking and generate its full installment schedule.
     */
    public function generatePlan(array $data): InstallmentPlan
    {
        return DB::transaction(function () use ($data) {
            $booking = Booking::findOrFail($data['booking_id']);
            $remaining = round($booking->final_price - $booking->booking_amount, 2);

            if ($data['plan_type'] === 'Custom') {
                $rows = $this->pairCustomRows($data['installment_due_date'], $data['installment_amount']);

                $plan = InstallmentPlan::create([
                    'booking_id' => $booking->id,
                    'plan_type' => 'Custom',
                    'number_of_installments' => count($rows),
                    'start_date' => $data['start_date'],
                    'total_amount' => $remaining,
                    'status' => 'Active',
                ]);

                $this->createCustomInstallments($plan, $rows);

                return $plan;
            }

            $count = (int) $data['number_of_installments'];

            $plan = InstallmentPlan::create([
                'booking_id' => $booking->id,
                'plan_type' => $data['plan_type'],
                'number_of_installments' => $count,
                'start_date' => $data['start_date'],
                'total_amount' => $remaining,
                'status' => 'Active',
            ]);

            $this->createEvenInstallments($plan, $remaining, $count, $data['start_date'], $data['plan_type']);

            return $plan;
        });
    }

    /**
     * Split $remaining evenly across $count installments, spaced monthly
     * or quarterly starting from $startDate. Because the split doesn't
     * always divide evenly (e.g. 100 / 3), the last installment absorbs
     * whatever is left over so the schedule always adds up to exactly
     * $remaining -- never a cent more or less.
     */
    private function createEvenInstallments(InstallmentPlan $plan, float $remaining, int $count, string $startDate, string $planType): void
    {
        $baseAmount = round($remaining / $count, 2);
        $monthsToAdd = $planType === 'Quarterly' ? 3 : 1;
        $dueDate = Carbon::parse($startDate);
        $allocated = 0.0;

        for ($number = 1; $number <= $count; $number++) {
            $amount = $number === $count
                ? round($remaining - $allocated, 2)
                : $baseAmount;

            $allocated += $amount;

            Installment::create([
                'installment_plan_id' => $plan->id,
                'installment_number' => $number,
                'due_date' => $dueDate->copy(),
                'amount' => $amount,
                'late_fee' => 0,
                'paid_amount' => 0,
                'status' => 'Pending',
                'paid_date' => null,
            ]);

            $dueDate->addMonths($monthsToAdd);
        }
    }

    /**
     * Line up the parallel due-date and amount arrays from the Custom form
     * into one array of [due_date, amount] pairs, in submission order.
     */
    private function pairCustomRows(array $dueDates, array $amounts): array
    {
        return collect($dueDates)
            ->values()
            ->map(fn ($date, $index) => ['due_date' => $date, 'amount' => $amounts[$index]])
            ->all();
    }

    private function createCustomInstallments(InstallmentPlan $plan, array $rows): void
    {
        foreach ($rows as $index => $row) {
            Installment::create([
                'installment_plan_id' => $plan->id,
                'installment_number' => $index + 1,
                'due_date' => $row['due_date'],
                'amount' => $row['amount'],
                'late_fee' => 0,
                'paid_amount' => 0,
                'status' => 'Pending',
                'paid_date' => null,
            ]);
        }
    }
}
