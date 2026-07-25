<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/blocks.php';
require __DIR__.'/plots.php';
require __DIR__.'/clients.php';
require __DIR__.'/bookings.php';
require __DIR__.'/installment-plans.php';
require __DIR__.'/payments.php';
require __DIR__.'/receipts.php';
require __DIR__.'/reports.php';
require __DIR__.'/activity-logs.php';
