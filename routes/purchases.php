<?php

use App\Http\Controllers\Purchases\SupplierController;
use App\Http\Controllers\Purchases\PurchaseRequestController;
use App\Http\Controllers\Purchases\PurchaseQuotationController;
use App\Http\Controllers\Purchases\PurchaseOrderController;
use App\Http\Controllers\Purchases\PurchaseReceptionController;
use App\Http\Controllers\Purchases\PurchaseReturnController;
use App\Http\Controllers\Purchases\AccountPayableController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('purchases')->name('purchases.')->group(function () {

    // ── Proveedores ───────────────────────────────────────────
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/',                [SupplierController::class, 'index'])->name('index');
        Route::get('/create',          [SupplierController::class, 'create'])->name('create');
        Route::post('/',               [SupplierController::class, 'store'])->name('store');
        Route::get('/{supplier}',      [SupplierController::class, 'show'])->name('show');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
        Route::put('/{supplier}',      [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}',   [SupplierController::class, 'destroy'])->name('destroy');
    });

    // ── Solicitudes de Compra ─────────────────────────────────
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/',                      [PurchaseRequestController::class, 'index'])->name('index');
        Route::get('/create',                [PurchaseRequestController::class, 'create'])->name('create');
        Route::post('/',                     [PurchaseRequestController::class, 'store'])->name('store');
        Route::get('/{purchaseRequest}',     [PurchaseRequestController::class, 'show'])->name('show');
        Route::post('/{purchaseRequest}/status', [PurchaseRequestController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{purchaseRequest}',  [PurchaseRequestController::class, 'destroy'])->name('destroy');
    });

    // ── Cotizaciones ──────────────────────────────────────────
    Route::prefix('quotations')->name('quotations.')->group(function () {
        Route::get('/',                        [PurchaseQuotationController::class, 'index'])->name('index');
        Route::get('/create',                  [PurchaseQuotationController::class, 'create'])->name('create');
        Route::post('/',                       [PurchaseQuotationController::class, 'store'])->name('store');
        Route::get('/{purchaseQuotation}',     [PurchaseQuotationController::class, 'show'])->name('show');
        Route::post('/{purchaseQuotation}/status',  [PurchaseQuotationController::class, 'updateStatus'])->name('update-status');
        Route::post('/{purchaseQuotation}/approve', [PurchaseQuotationController::class, 'approve'])->name('approve');
        Route::delete('/{purchaseQuotation}',  [PurchaseQuotationController::class, 'destroy'])->name('destroy');
    });

    // ── Órdenes de Compra ─────────────────────────────────────
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/',                   [PurchaseOrderController::class, 'index'])->name('index');
        Route::get('/create',             [PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/',                  [PurchaseOrderController::class, 'store'])->name('store');
        Route::get('/{purchaseOrder}',    [PurchaseOrderController::class, 'show'])->name('show');
        Route::get('/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
        Route::put('/{purchaseOrder}',    [PurchaseOrderController::class, 'update'])->name('update');
        Route::post('/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('destroy');
    });

    // ── Recepciones ───────────────────────────────────────────
    Route::prefix('receptions')->name('receptions.')->group(function () {
        Route::get('/',                        [PurchaseReceptionController::class, 'index'])->name('index');
        Route::get('/create',                  [PurchaseReceptionController::class, 'create'])->name('create');
        Route::post('/',                       [PurchaseReceptionController::class, 'store'])->name('store');
        Route::get('/{purchaseReception}',     [PurchaseReceptionController::class, 'show'])->name('show');
        Route::post('/{purchaseReception}/confirm', [PurchaseReceptionController::class, 'confirm'])->name('confirm');
        Route::post('/{purchaseReception}/cancel',  [PurchaseReceptionController::class, 'cancel'])->name('cancel');
        Route::delete('/{purchaseReception}',  [PurchaseReceptionController::class, 'destroy'])->name('destroy');
    });

    // ── Devoluciones ──────────────────────────────────────────
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/',                      [PurchaseReturnController::class, 'index'])->name('index');
        Route::get('/create',                [PurchaseReturnController::class, 'create'])->name('create');
        Route::post('/',                     [PurchaseReturnController::class, 'store'])->name('store');
        Route::get('/{purchaseReturn}',      [PurchaseReturnController::class, 'show'])->name('show');
        Route::post('/{purchaseReturn}/confirm', [PurchaseReturnController::class, 'confirm'])->name('confirm');
        Route::post('/{purchaseReturn}/cancel',  [PurchaseReturnController::class, 'cancel'])->name('cancel');
        Route::delete('/{purchaseReturn}',   [PurchaseReturnController::class, 'destroy'])->name('destroy');
    });

    // ── Cuentas por Pagar ─────────────────────────────────────
    Route::prefix('payables')->name('payables.')->group(function () {
        Route::get('/',                              [AccountPayableController::class, 'index'])->name('index');
        Route::get('/{accountPayable}',              [AccountPayableController::class, 'show'])->name('show');
        Route::post('/{accountPayable}/payments',    [AccountPayableController::class, 'addPayment'])->name('payments.store');
        Route::delete('/{accountPayable}/payments/{payment}', [AccountPayableController::class, 'deletePayment'])->name('payments.destroy');
    });
});
