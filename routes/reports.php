<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('reports')->name('reports.')->group(function () {
    $reports = [
        'clients' => 'clients',
        'plots' => 'plots',
        'blocks' => 'blocks',
        'collection' => 'collection',
        'outstanding' => 'outstanding',
        'due-installments' => 'dueInstallments',
        'defaulters' => 'defaulters',
        'sales' => 'sales',
    ];

    foreach ($reports as $uri => $action) {
        Route::get($uri, [ReportController::class, $action])->name($uri);
        Route::get("{$uri}/print", [ReportController::class, $action.'Print'])->name("{$uri}.print");
        Route::get("{$uri}/export/{format}", [ReportController::class, $action.'Export'])
            ->whereIn('format', ['excel', 'pdf'])
            ->name("{$uri}.export");
    }
});
