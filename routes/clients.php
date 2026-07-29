<?php

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::resource('clients', ClientController::class);

    Route::delete('clients/{client}/documents/{document}', [ClientController::class, 'destroyDocument'])
        ->name('clients.documents.destroy');

    // One action covers both cases: a client with no Client Portal login
    // yet gets one, and a client who already has one gets a fresh password
    // (the old one stops working immediately).
    Route::post('clients/{client}/generate-login', [ClientController::class, 'generateLogin'])
        ->name('clients.generate-login');
});
