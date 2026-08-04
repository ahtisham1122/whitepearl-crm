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
        'plot-detail' => 'plotDetail',
        'installments-progress' => 'installmentsProgress',
        'late-fees' => 'lateFees',
        'client-payment-status' => 'clientPaymentStatus',
    ];

    foreach ($reports as $uri => $action) {
        Route::get($uri, [ReportController::class, $action])->name($uri);
        Route::get("{$uri}/print", [ReportController::class, $action.'Print'])->name("{$uri}.print");
        Route::get("{$uri}/export/{format}", [ReportController::class, $action.'Export'])
            ->whereIn('format', ['excel', 'pdf'])
            ->name("{$uri}.export");
    }

    // Applying a suggested late fee is a write action, admin-confirmed,
    // one booking at a time -- not part of the generic read-only loop above.
    Route::post('late-fees/{booking}/apply', [ReportController::class, 'applyLateFee'])->name('late-fees.apply');

    // Individual client statement -- reached by clicking a client on the
    // All Clients report. Scoped to a booking (not just the client) since
    // the statement is really "one plot's installment schedule"; the
    // picker route works out which booking that is first.
    Route::get('clients/{client}/statement', [ReportController::class, 'clientStatementPicker'])->name('client-statement.picker');
    Route::get('statements/{booking}', [ReportController::class, 'clientStatement'])->name('client-statement');
    Route::get('statements/{booking}/print', [ReportController::class, 'clientStatementPrint'])->name('client-statement.print');
    Route::get('statements/{booking}/export/{format}', [ReportController::class, 'clientStatementExport'])
        ->whereIn('format', ['excel', 'pdf'])
        ->name('client-statement.export');
});
