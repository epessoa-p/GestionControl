@extends('layouts.app')
@section('title', 'Reportes')
@section('page')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="mb-1">Reportes</h1>
        <p class="text-muted mb-0">Seleccione un reporte para generar.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <a href="{{ route('reports.sales') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-shadow">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-cart3 display-4 text-primary mb-3 d-block"></i>
                        <h5 class="fw-bold mb-1">Ventas</h5>
                        <p class="text-muted small mb-0">Reporte de ventas por período con detalle de productos, promotores y métodos de pago.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('reports.inventory') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-shadow">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-box-seam display-4 text-success mb-3 d-block"></i>
                        <h5 class="fw-bold mb-1">Inventario</h5>
                        <p class="text-muted small mb-0">Estado actual del inventario con stock, costos y valorización.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('reports.commissions') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-shadow">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-percent display-4 text-warning mb-3 d-block"></i>
                        <h5 class="fw-bold mb-1">Comisiones</h5>
                        <p class="text-muted small mb-0">Comisiones agrupadas por promotor con totales pendientes y pagados.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('reports.cash-movements') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-shadow">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-cash-stack display-4 text-info mb-3 d-block"></i>
                        <h5 class="fw-bold mb-1">Movimientos de caja</h5>
                        <p class="text-muted small mb-0">Sesiones de caja con montos de apertura, cierre y diferencias.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('reports.production') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-shadow">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-gear display-4 text-secondary mb-3 d-block"></i>
                        <h5 class="fw-bold mb-1">Producción</h5>
                        <p class="text-muted small mb-0">Órdenes de producción con materiales, costos y estados.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.hover-shadow { transition: box-shadow .2s, transform .2s; }
.hover-shadow:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; transform: translateY(-2px); }
</style>
@endsection
