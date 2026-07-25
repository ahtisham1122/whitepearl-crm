<?php

use App\Http\Controllers\BlockController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::resource('blocks', BlockController::class)->except('show');
});
