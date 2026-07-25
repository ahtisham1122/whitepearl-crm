<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDocument extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'document_type',
        'file_path',
        'original_name',
    ];

    /**
     * A document belongs to one client.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
