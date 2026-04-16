@extends('layouts.app')
@section('title', 'Abrir sesión de caja')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-unlock text-success me-2"></i>Abrir sesión</h1>
            <p class="text-muted mb-0">{{ $cashRegister->name }} — Asigna personal y monto de apertura</p>
        </div>
        <a href="{{ route('cash-registers.show', $cashRegister) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-cash-stack me-2 text-primary"></i>Datos de apertura</h5>
                </div>
                <div class="card-body p-4 pt-3">
                    <form action="{{ route('cash-registers.open-session', $cashRegister) }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Personal asignado <span class="text-danger">*</span></label>
                            <select name="personal_id" class="form-select @error('personal_id') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                @foreach($personals as $p)
                                    <option value="{{ $p->id }}" {{ (string)old('personal_id') === (string)$p->id ? 'selected' : '' }}>{{ $p->full_name }}</option>
                                @endforeach
                            </select>
                            @error('personal_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Monto de apertura <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="opening_amount" class="form-control @error('opening_amount') is-invalid @enderror" value="{{ old('opening_amount', 0) }}" required>
                            </div>
                            @error('opening_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <hr class="my-2">
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-success" type="submit"><i class="bi bi-unlock me-1"></i> Abrir caja</button>
                            <a href="{{ route('cash-registers.show', $cashRegister) }}" class="btn btn-light border">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
