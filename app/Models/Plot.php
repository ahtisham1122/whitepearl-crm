<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plot extends Model
{
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
}
