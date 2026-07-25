<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
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
}
