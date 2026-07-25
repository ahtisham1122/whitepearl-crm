<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Laravel already fires this event the moment a user successfully
     * logs in (built into the framework's auth system) -- we just tell
     * Laravel to also run this extra step whenever that happens.
     */
    public function handle(Login $event): void
    {
        activity('auth')
            ->causedBy($event->user)
            ->withProperties(['ip' => request()->ip()])
            ->log('User logged in');
    }
}
