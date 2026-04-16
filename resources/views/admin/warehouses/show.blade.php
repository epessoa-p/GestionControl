@extends('layouts.app')

@section('title', 'Detalle almacén')

@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="mb-1">{{ $warehouse->name }}</h1><p class="text-muted mb-0">Detalle del almacén.</p></div><div class="d-flex gap-2"><a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-primary">Editar</a><a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary">Volver</a></div></div>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm"><div class="card-body"><p><strong>Empresa:</strong> {{ $warehouse->company?->name }}</p><p><strong>Sucursal principal:</strong> {{ $warehouse->primaryBranch?->name ?: 'No asignada' }}</p><p><strong>Código:</strong> {{ $warehouse->code }}</p><p><strong>Ubicación:</strong> {{ $warehouse->location ?: '-' }}</p><p><strong>Descripción:</strong> {{ $warehouse->description ?: '-' }}</p></div></div>
        </div>
    </div>
</div>
@endsection