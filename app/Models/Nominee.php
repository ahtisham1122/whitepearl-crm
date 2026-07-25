<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nominee extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'name',
        'relationship',
        'cnic',
        'phone',
        'email',
        'address',
    ];

    /**
     * A nominee belongs to one client.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
