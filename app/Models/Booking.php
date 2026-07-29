<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Booking extends Model
{
    use LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'booking_number',
        'client_id',
        'plot_id',
        'booking_date',
        'booking_type',
        'total_price',
        'discount',
        'final_price',
        'booking_amount',
        'sales_person',
        'remarks',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'total_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'final_price' => 'decimal:2',
            'booking_amount' => 'decimal:2',
        ];
    }

    /**
     * A booking belongs to one client.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * A booking belongs to one plot.
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * A booking has at most one installment plan.
     */
    public function installmentPlan(): HasOne
    {
        return $this->hasOne(InstallmentPlan::class);
    }

    /**
     * A booking can have many payments recorded against it over time.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * A cancelled booking is really just an "updated" event underneath
     * (the status field changed) -- this gives it a more specific,
     * readable description on the Activity Log page instead of the
     * generic "Booking updated" whenever the change was a cancellation.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('booking')
            ->setDescriptionForEvent(function (string $eventName) {
                if ($eventName === 'updated' && $this->status === 'Cancelled') {
                    return 'Booking cancelled';
                }

                return "Booking {$eventName}";
            });
    }
}
