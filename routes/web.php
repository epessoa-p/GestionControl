<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\CargoController;
use App\Http\Controllers\Admin\PersonalController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\MeasurementUnitController;
use App\Http\Controllers\DocumentTemplates\DocumentTemplateController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\DepartureController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\PromoterController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Company selection (multi-empresa)
    Route::get('/select-company', [LoginController::class, 'selectCompany'])->name('select-company');
    Route::post('/set-company/{companyId}', [LoginController::class, 'setCompany'])->name('set-company');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Super Admin - Company Management
    Route::middleware('check-role:super_admin')->prefix('admin/companies')->name('companies.')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('index');
        Route::get('/create', [CompanyController::class, 'create'])->name('create');
        Route::post('/', [CompanyController::class, 'store'])->name('store');
        Route::get('/{company}', [CompanyController::class, 'show'])->name('show');
        Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
        Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
        Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
    });

    // User Management
    Route::prefix('admin/users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/assign-role/{company}/{role}', [UserController::class, 'assignRole'])->name('assign-role');
    });

    // Role Management (Super Admin only)
    Route::prefix('admin/roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}', [RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/cargos')->name('cargos.')->group(function () {
        Route::get('/', [CargoController::class, 'index'])->name('index');
        Route::get('/create', [CargoController::class, 'create'])->name('create');
        Route::post('/', [CargoController::class, 'store'])->name('store');
        Route::get('/role-permissions/{role}', [CargoController::class, 'rolePermissions'])->name('role-permissions');
        Route::get('/{cargo}/edit', [CargoController::class, 'edit'])->name('edit');
        Route::put('/{cargo}', [CargoController::class, 'update'])->name('update');
        Route::delete('/{cargo}', [CargoController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/personal')->name('personal.')->group(function () {
        Route::get('/', [PersonalController::class, 'index'])->name('index');
        Route::get('/create', [PersonalController::class, 'create'])->name('create');
        Route::post('/', [PersonalController::class, 'store'])->name('store');
        Route::get('/{personal}/edit', [PersonalController::class, 'edit'])->name('edit');
        Route::put('/{personal}', [PersonalController::class, 'update'])->name('update');
        Route::delete('/{personal}', [PersonalController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/branches')->name('branches.')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::get('/create', [BranchController::class, 'create'])->name('create');
        Route::post('/', [BranchController::class, 'store'])->name('store');
        Route::get('/{branch}', [BranchController::class, 'show'])->name('show');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/measurement-units')->name('measurement-units.')->group(function () {
        Route::get('/', [MeasurementUnitController::class, 'index'])->name('index');
        Route::get('/create', [MeasurementUnitController::class, 'create'])->name('create');
        Route::post('/', [MeasurementUnitController::class, 'store'])->name('store');
        Route::get('/{measurementUnit}/edit', [MeasurementUnitController::class, 'edit'])->name('edit');
        Route::put('/{measurementUnit}', [MeasurementUnitController::class, 'update'])->name('update');
        Route::delete('/{measurementUnit}', [MeasurementUnitController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/warehouses')->name('warehouses.')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::get('/create', [WarehouseController::class, 'create'])->name('create');
        Route::post('/', [WarehouseController::class, 'store'])->name('store');
        Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('show');
        Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
        Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
    });

    // ─── Seguimientos ───
    Route::prefix('trackings')->name('trackings.')->group(function () {
        Route::get('/', [TrackingController::class, 'index'])->name('index');
        Route::get('/create', [TrackingController::class, 'create'])->name('create');
        Route::post('/', [TrackingController::class, 'store'])->name('store');
        Route::get('/{tracking}', [TrackingController::class, 'show'])->name('show');
        Route::get('/{tracking}/edit', [TrackingController::class, 'edit'])->name('edit');
        Route::put('/{tracking}', [TrackingController::class, 'update'])->name('update');
        Route::delete('/{tracking}', [TrackingController::class, 'destroy'])->name('destroy');
    });

    // ─── Entradas ───
    Route::prefix('entries')->name('entries.')->group(function () {
        Route::get('/', [EntryController::class, 'index'])->name('index');
        Route::get('/create', [EntryController::class, 'create'])->name('create');
        Route::post('/', [EntryController::class, 'store'])->name('store');
        Route::get('/{entry}', [EntryController::class, 'show'])->name('show');
        Route::post('/{entry}/confirm', [EntryController::class, 'confirm'])->name('confirm');
        Route::post('/{entry}/cancel', [EntryController::class, 'cancel'])->name('cancel');
        Route::delete('/{entry}', [EntryController::class, 'destroy'])->name('destroy');
    });

    // ─── Salidas ───
    Route::prefix('departures')->name('departures.')->group(function () {
        Route::get('/', [DepartureController::class, 'index'])->name('index');
        Route::get('/create', [DepartureController::class, 'create'])->name('create');
        Route::post('/', [DepartureController::class, 'store'])->name('store');
        Route::get('/{departure}', [DepartureController::class, 'show'])->name('show');
        Route::post('/{departure}/confirm', [DepartureController::class, 'confirm'])->name('confirm');
        Route::post('/{departure}/cancel', [DepartureController::class, 'cancel'])->name('cancel');
        Route::delete('/{departure}', [DepartureController::class, 'destroy'])->name('destroy');
    });

    // ─── Cajas ───
    Route::prefix('cash-registers')->name('cash-registers.')->group(function () {
        Route::get('/', [CashRegisterController::class, 'index'])->name('index');
        Route::get('/create', [CashRegisterController::class, 'create'])->name('create');
        Route::post('/', [CashRegisterController::class, 'store'])->name('store');
        Route::get('/{cashRegister}', [CashRegisterController::class, 'show'])->name('show');
        Route::get('/{cashRegister}/edit', [CashRegisterController::class, 'edit'])->name('edit');
        Route::put('/{cashRegister}', [CashRegisterController::class, 'update'])->name('update');
        Route::delete('/{cashRegister}', [CashRegisterController::class, 'destroy'])->name('destroy');
        Route::get('/{cashRegister}/open-session', [CashRegisterController::class, 'openSessionForm'])->name('open-session-form');
        Route::post('/{cashRegister}/open-session', [CashRegisterController::class, 'openSession'])->name('open-session');
    });

    Route::prefix('cash-sessions')->name('cash-sessions.')->group(function () {
        Route::get('/{cashSession}', [CashRegisterController::class, 'sessionDetail'])->name('show');
        Route::post('/{cashSession}/close', [CashRegisterController::class, 'closeSession'])->name('close');
        Route::post('/{cashSession}/movement', [CashRegisterController::class, 'addMovement'])->name('add-movement');
    });

    // ─── Caja Chica ───
    Route::prefix('petty-cash')->name('petty-cash.')->group(function () {
        Route::get('/', [PettyCashController::class, 'index'])->name('index');
        Route::get('/create', [PettyCashController::class, 'create'])->name('create');
        Route::post('/', [PettyCashController::class, 'store'])->name('store');
        Route::get('/{pettyCash}', [PettyCashController::class, 'show'])->name('show');
        Route::post('/{pettyCash}/movement', [PettyCashController::class, 'addMovement'])->name('add-movement');
        Route::delete('/{pettyCash}', [PettyCashController::class, 'destroy'])->name('destroy');
    });

    // ─── Producción ───
    Route::prefix('productions')->name('productions.')->group(function () {
        Route::get('/', [ProductionController::class, 'index'])->name('index');
        Route::get('/create', [ProductionController::class, 'create'])->name('create');
        Route::post('/', [ProductionController::class, 'store'])->name('store');
        Route::get('/{production}', [ProductionController::class, 'show'])->name('show');
        Route::post('/{production}/status', [ProductionController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{production}', [ProductionController::class, 'destroy'])->name('destroy');
    });

    // ─── Promotores ───
    Route::prefix('promoters')->name('promoters.')->group(function () {
        Route::get('/', [PromoterController::class, 'index'])->name('index');
        Route::get('/create', [PromoterController::class, 'create'])->name('create');
        Route::post('/', [PromoterController::class, 'store'])->name('store');
        Route::get('/{promoter}', [PromoterController::class, 'show'])->name('show');
        Route::get('/{promoter}/edit', [PromoterController::class, 'edit'])->name('edit');
        Route::put('/{promoter}', [PromoterController::class, 'update'])->name('update');
        Route::delete('/{promoter}', [PromoterController::class, 'destroy'])->name('destroy');
    });

    // ─── Ventas ───
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/create', [SaleController::class, 'create'])->name('create');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::post('/{sale}/complete', [SaleController::class, 'complete'])->name('complete');
        Route::post('/{sale}/cancel', [SaleController::class, 'cancel'])->name('cancel');
        Route::post('/{sale}/installments/{installment}/pay', [SaleController::class, 'payInstallment'])->name('pay-installment');
        Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('destroy');
    });

    // ─── Traspasos entre Almacenes ───
    Route::prefix('transfers')->name('transfers.')->group(function () {
        Route::get('/', [WarehouseTransferController::class, 'index'])->name('index');
        Route::get('/create', [WarehouseTransferController::class, 'create'])->name('create');
        Route::post('/', [WarehouseTransferController::class, 'store'])->name('store');
        Route::get('/{transfer}', [WarehouseTransferController::class, 'show'])->name('show');
        Route::post('/{transfer}/dispatch', [WarehouseTransferController::class, 'dispatch'])->name('dispatch');
        Route::post('/{transfer}/complete', [WarehouseTransferController::class, 'complete'])->name('complete');
        Route::post('/{transfer}/cancel', [WarehouseTransferController::class, 'cancel'])->name('cancel');
        Route::delete('/{transfer}', [WarehouseTransferController::class, 'destroy'])->name('destroy');
    });

    // ─── Órdenes / Pedidos ───
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
    });

    // ─── Comisiones ───
    Route::prefix('commissions')->name('commissions.')->group(function () {
        Route::get('/', [CommissionController::class, 'index'])->name('index');
        Route::post('/{commission}/mark-paid', [CommissionController::class, 'markPaid'])->name('mark-paid');
        Route::post('/mark-paid-bulk', [CommissionController::class, 'markPaidBulk'])->name('mark-paid-bulk');
        Route::delete('/{commission}', [CommissionController::class, 'destroy'])->name('destroy');
    });

    // ─── Reportes ───
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/commissions', [ReportController::class, 'commissions'])->name('commissions');
        Route::get('/cash-movements', [ReportController::class, 'cashMovements'])->name('cash-movements');
        Route::get('/production', [ReportController::class, 'production'])->name('production');
    });

    // Document Templates
    Route::prefix('document-templates')->name('document-templates.')->group(function () {
        Route::get('/', [DocumentTemplateController::class, 'index'])->name('index');
        Route::get('/create', [DocumentTemplateController::class, 'create'])->name('create');
        Route::post('/', [DocumentTemplateController::class, 'store'])->name('store');
        Route::get('/{documentTemplate}/download/word', [DocumentTemplateController::class, 'downloadWord'])->name('download.word');
        Route::get('/{documentTemplate}/export/pdf', [DocumentTemplateController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/{documentTemplate}', [DocumentTemplateController::class, 'show'])->name('show');
        Route::get('/{documentTemplate}/edit', [DocumentTemplateController::class, 'edit'])->name('edit');
        Route::put('/{documentTemplate}', [DocumentTemplateController::class, 'update'])->name('update');
        Route::delete('/{documentTemplate}', [DocumentTemplateController::class, 'destroy'])->name('destroy');
    });
});

// Fallback
Route::redirect('/', '/dashboard');

