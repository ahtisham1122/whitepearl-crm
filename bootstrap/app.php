<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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

        // The Cloudflare Tunnel (cloudflared) always connects to us from
        // 127.0.0.1 (it runs on the same machine as `php artisan serve`).
        // It terminates HTTPS at Cloudflare's edge and forwards plain HTTP
        // to us, but it does tell us the original scheme via the
        // X-Forwarded-Proto header. Without trusting that header, Laravel
        // thinks every request is plain http, so asset()/url() generate
        // http:// links -- which get silently blocked as "mixed content"
        // by the browser on the https:// tunnel page. Trusting the
        // loopback proxy for forwarded headers fixes that.
        $middleware->trustProxies(
            at: ['127.0.0.1', '::1'],
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        // Allow the app to be reached on 127.0.0.1/localhost (normal local
        // dev) as well as any *.trycloudflare.com tunnel hostname. This
        // only matters when APP_ENV is not "local" -- Laravel skips host
        // checking entirely in local env -- but it's here so the tunnel
        // keeps working if that ever changes.
        $middleware->trustHosts(at: [
            '^127\.0\.0\.1$',
            '^localhost$',
            '^.+\.trycloudflare\.com$',
        ], subdomains: false);

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
