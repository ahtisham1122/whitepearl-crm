<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'installment_plan_id',
        'installment_number',
        'due_date',
        'amount',
        'late_fee',
        'paid_amount',
        'status',
        'paid_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_date' => 'date',
            'amount' => 'decimal:2',
            'late_fee' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    /**
     * An installment belongs to one plan.
     */
    public function installmentPlan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class);
    }

    /**
     * The status to actually show on screen. "Overdue" is never stored in
     * the database -- it's worked out fresh every time from today's date,
     * so it's always correct without needing a daily background job to
     * keep it up to date.
     */
    public function displayStatus(): string
    {
        if ($this->status !== 'Paid' && $this->due_date->lt(today())) {
            return 'Overdue';
        }

        return $this->status;
    }
}
