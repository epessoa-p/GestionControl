@extends('layouts.app')

@section('title', 'Movimientos de inventario')

@include('layouts._compact_style')

@php
    $dirColors = ['entrada' => 'success', 'salida' => 'danger', 'traspaso' => 'info'];
    $dirLabels = ['entrada' => 'Entrada', 'salida' => 'Salida', 'traspaso' => 'Traspaso'];
    $srcColors = ['manual' => 'secondary', 'compra' => 'primary', 'produccion' => 'warning', 'venta' => 'success', 'traspaso' => 'info'];
    $srcLabels = ['manual' => 'Manual', 'compra' => 'Compra', 'produccion' => 'Producción', 'venta' => 'Venta', 'traspaso' => 'Traspaso'];
    $refRoutes = [
        'entry'      => 'entries.show',
        'reception'  => 'purchases.receptions.show',
        'production' => 'productions.show',
        'departure'  => 'departures.show',
        'sale'       => 'sales.show',
        'transfer'   => 'transfers.show',
    ];
@endphp

@section('page')
<div class="view-compact container-fluid">

    <div class="d-flex justify-content-between align-items-start mb-3 page-head">
        <div>
            <h1 class="mb-1"><i class="bi bi-arrow-down-up text-primary me-2"></i>Movimientos de inventario</h1>
            <p class="text-muted mb-0">Historial consolidado de entradas, salidas y traspasos confirmados.</p>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><div class="text-muted small">Entradas</div><div class="fw-bold fs-5 text-success">{{ $kpiEntradas }}</div></div>
                    <i class="bi bi-box-arrow-in-down fs-4 text-success opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><div class="text-muted small">Salidas</div><div class="fw-bold fs-5 text-danger">{{ $kpiSalidas }}</div></div>
                    <i class="bi bi-box-arrow-up fs-4 text-danger opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><div class="text-muted small">Traspasos</div><div class="fw-bold fs-5 text-info">{{ $kpiTraspasos }}</div></div>
                    <i class="bi bi-arrow-left-right fs-4 text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                {{-- Chips de tipo --}}
                <div class="col-12">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ request()->fullUrlWithQuery(['direction' => null]) }}"
                           class="btn {{ !$direction ? 'btn-dark' : 'btn-outline-secondary' }}">Todos</a>
                        @foreach($dirLabels as $val => $label)
                            <a href="{{ request()->fullUrlWithQuery(['direction' => $val]) }}"
                               class="btn {{ $direction === $val ? 'btn-'.$dirColors[$val] : 'btn-outline-secondary' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Origen</label>
                    <select name="source" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($srcLabels as $val => $label)
                            <option value="{{ $val }}" {{ $source === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Almacén</label>
                    <select name="warehouse_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($allWarehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)$warehouseId === (string)$wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Producto</label>
                    <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Nombre o SKU...">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-1 d-flex gap-1">
                    <button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-search"></i></button>
                    <a href="{{ route('inventory-movements.index') }}" class="btn btn-sm btn-outline-secondary">×</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Origen</th>
                            <th>Referencia</th>
                            <th>Producto</th>
                            <th class="text-end">Cantidad</th>
                            <th>Almacén</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $m)
                            <tr>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($m->movement_date)->format('d/m/Y') }}</td>
                                <td><span class="badge bg-{{ $dirColors[$m->direction] ?? 'secondary' }}">{{ $dirLabels[$m->direction] ?? $m->direction }}</span></td>
                                <td><span class="badge bg-{{ $srcColors[$m->source] ?? 'secondary' }} bg-opacity-75">{{ $srcLabels[$m->source] ?? $m->source }}</span></td>
                                <td>
                                    @if(isset($refRoutes[$m->reference_type]) && \Illuminate\Support\Facades\Route::has($refRoutes[$m->reference_type]))
                                        <a href="{{ route($refRoutes[$m->reference_type], $m->reference_id) }}" class="text-decoration-none fw-semibold">{{ $m->reference_number }}</a>
                                    @else
                                        <span class="fw-semibold">{{ $m->reference_number }}</span>
                                    @endif
                                </td>
                                <td>{{ $products[$m->product_id]->name ?? '—' }}</td>
                                <td class="text-end fw-semibold">
                                    @if($m->direction === 'entrada')
                                        <span class="text-success">+{{ number_format($m->quantity, 2) }}</span>
                                    @elseif($m->direction === 'salida')
                                        <span class="text-danger">−{{ number_format($m->quantity, 2) }}</span>
                                    @else
                                        <span class="text-info">{{ number_format($m->quantity, 2) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($m->direction === 'traspaso')
                                        <span class="text-nowrap">
                                            {{ $warehouses[$m->warehouse_id]->name ?? '?' }}
                                            <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                            {{ $warehouses[$m->to_warehouse_id]->name ?? '?' }}
                                        </span>
                                    @else
                                        {{ $warehouses[$m->warehouse_id]->name ?? '—' }}
                                    @endif
                                </td>
                                <td>{{ $users[$m->created_by]->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>No hay movimientos que coincidan con el filtro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-center">{{ $movements->links() }}</div>
</div>
@endsection
