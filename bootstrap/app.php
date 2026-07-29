<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Without this, Laravel's default "auth" middleware always sends a
        // logged-out visitor to the staff login page, even on Client Portal
        // routes (they all start with /client/...) -- and "guest" would
        // send an already-logged-in client to the staff dashboard instead
        // of their own. Routing by URL prefix keeps the two logins apart.
        $middleware->redirectGuestsTo(
            fn ($request) => $request->is('client/*') ? route('client.login') : route('login')
        );
        $middleware->redirectUsersTo(
            fn ($request) => $request->is('client/*') ? route('client.dashboard') : route('dashboard')
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
