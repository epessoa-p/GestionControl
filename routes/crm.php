<?php

/**
 * ─── Rutas CRM ───────────────────────────────────────────────────────────────
 *
 * Agrupa todas las rutas del módulo CRM (clientes, contactos, actividades, etc.)
 * bajo el prefijo /crm y el middleware de autenticación.
 *
 * Registrado en bootstrap/app.php mediante el callback `then`.
 */

use App\Http\Controllers\Crm\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('crm')->name('crm.')->group(function () {

    // ── Clientes ─────────────────────────────────────────────
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/',               [ClientController::class, 'index'])->name('index');
        Route::get('/create',         [ClientController::class, 'create'])->name('create');
        Route::get('/search',         [ClientController::class, 'search'])->name('search');   // JSON autocomplete
        Route::post('/',              [ClientController::class, 'store'])->name('store');
        Route::get('/{client}',       [ClientController::class, 'show'])->name('show');
        Route::get('/{client}/edit',                              [ClientController::class, 'edit'])->name('edit');
        Route::put('/{client}',                                   [ClientController::class, 'update'])->name('update');
        Route::delete('/{client}',                                [ClientController::class, 'destroy'])->name('destroy');
        Route::delete('/{client}/photo',                          [ClientController::class, 'destroyPhoto'])->name('photo.destroy');
        Route::delete('/{client}/documents/{document}',           [ClientController::class, 'destroyDocument'])->name('documents.destroy');
    });

});
