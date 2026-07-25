<?php

use App\Http\Controllers\InstallmentPlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('installment-plans', [InstallmentPlanController::class, 'index'])->name('installment-plans.index');
    Route::get('installment-plans/create', [InstallmentPlanController::class, 'create'])->name('installment-plans.create');
    Route::post('installment-plans', [InstallmentPlanController::class, 'store'])->name('installment-plans.store');
    Route::get('installment-plans/{installmentPlan}', [InstallmentPlanController::class, 'show'])->name('installment-plans.show');
});
