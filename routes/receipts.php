<?php

use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('receipts', [ReceiptController::class, 'index'])->name('receipts.index');
});
