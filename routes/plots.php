<?php

use App\Http\Controllers\PlotController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::resource('plots', PlotController::class)->except('show');
});
