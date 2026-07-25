<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Plot extends Model
{
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'block_id',
        'plot_number',
        'street',
        'plot_size',
        'category',
        'corner_plot',
        'park_facing',
        'main_boulevard',
        'plot_price',
        'development_charges',
        'extra_charges',
        'total_price',
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
            'corner_plot' => 'boolean',
            'park_facing' => 'boolean',
            'main_boulevard' => 'boolean',
            'plot_price' => 'decimal:2',
            'development_charges' => 'decimal:2',
            'extra_charges' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * A plot belongs to one block.
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * A plot can have many bookings over time (past cancellations plus
     * the current one), which is why this is hasMany and not hasOne.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * The plot's current, non-cancelled booking, if any. This is a "one of
     * many" relationship: among all of this plot's bookings, take the most
     * recent one that is still Active.
     */
    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class)->where('status', 'Active')->latestOfMany();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('plot')
            ->setDescriptionForEvent(fn (string $eventName) => "Plot {$eventName}");
    }
}
