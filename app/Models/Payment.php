<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'receipt_number',
        'booking_id',
        'installment_id',
        'payment_date',
        'amount',
        'payment_method',
        'payment_type',
        'late_fee',
        'reference_no',
        'remarks',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'late_fee' => 'decimal:2',
        ];
    }

    /**
     * A payment belongs to one booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * The specific installment this payment was applied to, if the admin
     * used Mode A ("pay a specific installment"). Null for Mode B.
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    /**
     * A payment IS a receipt (see receipt_number above) -- so logging a
     * payment being created also covers "Receipt generated" as one entry,
     * rather than two log rows for what is really one action.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('payment')
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Payment received',
                'deleted' => 'Payment deleted',
                default => "Payment {$eventName}",
            });
    }
}
