<?php

use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:Super Admin|Admin'])->group(function () {
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});
