<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The header (bell icon) is included on every logged-in page via
        // layouts.app, so a view composer runs this once, right before
        // that partial renders, and hands it the badge count + preview
        // list -- no controller needs to remember to pass this data in.
        View::composer('partials.header', function ($view) {
            $headerData = app(NotificationService::class)->getHeaderData();

            $view->with('notificationCount', $headerData['count']);
            $view->with('notificationAlerts', $headerData['alerts']);
        });
    }
}
