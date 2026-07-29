<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A Client is both a business record (a person who bought a plot) AND,
 * separately, a login for the Client Portal. Implementing Authenticatable
 * here (the same interface App\Models\User implements for staff) lets a
 * Client log in through its own "client" guard -- completely separate from
 * staff logins -- without needing a second table. See config/auth.php for
 * the guard/provider wiring.
 */
class Client extends Model implements AuthenticatableContract
{
    use Authenticatable, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * 'password' is deliberately NOT listed here. It can only ever be set
     * by ClientService::generatePassword() (a direct property assignment,
     * not mass assignment) -- never through the client add/edit form.
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
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
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

    /**
     * A client can have many bookings over time (reservations and sales).
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * All payments this client has ever made, reached through their
     * bookings (Client -> Booking -> Payment). "Through" relationships let
     * us query a table we're not directly connected to, without manually
     * joining or looping over bookings ourselves.
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Booking::class);
    }

    /**
     * Which attributes to log, and what to call each event in the
     * Activity Log page. logOnlyDirty() means an update only records the
     * fields that actually changed, not the whole row every time.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('client')
            ->setDescriptionForEvent(fn (string $eventName) => "Client {$eventName}");
    }
}
