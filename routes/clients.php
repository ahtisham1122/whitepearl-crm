<?php

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::resource('clients', ClientController::class);

    Route::delete('clients/{client}/documents/{document}', [ClientController::class, 'destroyDocument'])
        ->name('clients.documents.destroy');
});
