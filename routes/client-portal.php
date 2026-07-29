<?php

use App\Http\Controllers\ClientPortal\AuthController;
use App\Http\Controllers\ClientPortal\DashboardController;
use App\Http\Controllers\ClientPortal\ReceiptController;
use Illuminate\Support\Facades\Route;

// Everything here lives under /client/... on purpose -- bootstrap/app.php
// uses that prefix to decide whether a logged-out visitor belongs on the
// staff login page or the client login page.
Route::prefix('client')->name('client.')->group(function () {
    Route::middleware('guest:client')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->name('login.store');
        Route::get('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
    });

    Route::middleware('auth:client')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('receipts/{payment}/pdf', [ReceiptController::class, 'pdf'])
            ->whereNumber('payment')
            ->name('receipts.pdf');
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
    });
});
