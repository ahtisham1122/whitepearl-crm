<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'full_name',
        'father_name',
        'cnic',
        'date_of_birth',
        'phone',
        'whatsapp',
        'email',
        'occupation',
        'address',
        'city',
        'country',
        'profile_photo',
        'notes',
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
            'date_of_birth' => 'date',
        ];
    }

    /**
     * A client has one nominee.
     */
    public function nominee(): HasOne
    {
        return $this->hasOne(Nominee::class);
    }

    /**
     * A client can have many documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ClientDocument::class);
    }
}
