<?php

use App\Http\Controllers\Sales\SalesDashboardController;
use App\Http\Controllers\Sales\PosController;
use App\Http\Controllers\Sales\SalesQuotationController;
use App\Http\Controllers\Sales\SalesReturnController;
use App\Http\Controllers\Sales\ReceivablesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    // ── Dashboard de ventas ───────────────────────────────────
    Route::get('/ventas/dashboard', [SalesDashboardController::class, 'index'])->name('sales.dashboard');

    // ── POS ───────────────────────────────────────────────────
    Route::get('/pos',  [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');

    // ── Cotizaciones de venta ─────────────────────────────────
    Route::prefix('ventas/cotizaciones')->name('sales-quotations.')->group(function () {
        Route::get('/',                        [SalesQuotationController::class, 'index'])->name('index');
        Route::get('/create',                  [SalesQuotationController::class, 'create'])->name('create');
        Route::post('/',                       [SalesQuotationController::class, 'store'])->name('store');
        Route::get('/{salesQuotation}',        [SalesQuotationController::class, 'show'])->name('show');
        Route::get('/{salesQuotation}/edit',   [SalesQuotationController::class, 'edit'])->name('edit');
        Route::put('/{salesQuotation}',        [SalesQuotationController::class, 'update'])->name('update');
        Route::post('/{salesQuotation}/status',  [SalesQuotationController::class, 'updateStatus'])->name('update-status');
        Route::post('/{salesQuotation}/convert', [SalesQuotationController::class, 'convertToSale'])->name('convert');
        Route::delete('/{salesQuotation}',     [SalesQuotationController::class, 'destroy'])->name('destroy');
    });

    // ── Devoluciones de venta ─────────────────────────────────
    Route::prefix('ventas/devoluciones')->name('sales-returns.')->group(function () {
        Route::get('/',                    [SalesReturnController::class, 'index'])->name('index');
        Route::get('/create',              [SalesReturnController::class, 'create'])->name('create');
        Route::post('/',                   [SalesReturnController::class, 'store'])->name('store');
        Route::get('/{salesReturn}',       [SalesReturnController::class, 'show'])->name('show');
        Route::post('/{salesReturn}/confirm', [SalesReturnController::class, 'confirm'])->name('confirm');
        Route::post('/{salesReturn}/cancel',  [SalesReturnController::class, 'cancel'])->name('cancel');
        Route::delete('/{salesReturn}',    [SalesReturnController::class, 'destroy'])->name('destroy');
    });

    // ── Cuentas por Cobrar ────────────────────────────────────
    Route::get('/ventas/cuentas-por-cobrar', [ReceivablesController::class, 'index'])->name('receivables.index');
});
