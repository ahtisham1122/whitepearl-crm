<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InstallmentPlan extends Model
{
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'plan_type',
        'number_of_installments',
        'start_date',
        'total_amount',
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
            'start_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * A plan belongs to one booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * A plan has many installments (its schedule of due payments).
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class)->orderBy('installment_number');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('installment_plan')
            ->setDescriptionForEvent(function (string $eventName) {
                if ($eventName === 'updated' && $this->status === 'Completed') {
                    return 'Installment plan completed';
                }

                return "Installment plan {$eventName}";
            });
    }
}
