@extends('layouts.app')

@section('title', 'Reinicio de sistema')

@section('page')
<div class="container-fluid" style="max-width:900px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-exclamation-octagon-fill text-danger me-2"></i>Reinicio de datos</h1>
            <p class="text-muted mb-0">Elimina los datos transaccionales conservando los catálogos base.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    @if($companies->isEmpty())
        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>No hay empresas registradas.</div>
    @else

    <div class="card border-danger shadow-sm mb-4">
        <div class="card-header bg-danger text-white">
            <i class="bi bi-shield-exclamation me-1"></i> Acción irreversible
        </div>
        <div class="card-body">

            <form action="{{ route('system-reset.run') }}" method="POST" id="resetForm">
                @csrf

                {{-- Selección de empresa --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Empresa a reiniciar <span class="text-danger">*</span></label>
                    <select name="company_id" id="companySelect" class="form-select">
                        <option value="" data-name="">— Selecciona una empresa —</option>
                        @foreach($companies as $co)
                            <option value="{{ $co->id }}" data-name="{{ $co->name }}"
                                {{ (string)old('company_id', $current?->id) === (string)$co->id ? 'selected' : '' }}>
                                {{ $co->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Solo se eliminarán los datos de la empresa seleccionada.</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-3 h-100">
                            <div class="px-3 py-2 bg-success-subtle text-success fw-semibold border-bottom">
                                <i class="bi bi-check-circle me-1"></i> Se conserva
                            </div>
                            <ul class="list-unstyled mb-0 p-3 small">
                                <li><i class="bi bi-dot"></i> Sucursales</li>
                                <li><i class="bi bi-dot"></i> Almacenes</li>
                                <li><i class="bi bi-dot"></i> Personal</li>
                                <li><i class="bi bi-dot"></i> Clientes</li>
                                <li><i class="bi bi-dot"></i> Proveedores</li>
                                <li><i class="bi bi-dot"></i> Unidades de medida</li>
                                <li><i class="bi bi-dot"></i> Cajas <span class="text-muted">(sin sesiones)</span></li>
                                <li><i class="bi bi-dot"></i> Cuentas de tesorería <span class="text-muted">(saldo al inicial)</span></li>
                                <li><i class="bi bi-dot"></i> Productos <span class="text-muted">(stock en 0)</span></li>
                                <li><i class="bi bi-dot"></i> Usuarios, roles y permisos</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 h-100">
                            <div class="px-3 py-2 bg-danger-subtle text-danger fw-semibold border-bottom">
                                <i class="bi bi-trash me-1"></i> Se elimina
                            </div>
                            <ul class="list-unstyled mb-0 p-3 small">
                                <li><i class="bi bi-dot"></i> Ventas, cotizaciones, devoluciones, cuentas por cobrar</li>
                                <li><i class="bi bi-dot"></i> Compras (órdenes, recepciones, devoluciones, cuentas por pagar)</li>
                                <li><i class="bi bi-dot"></i> Entradas, salidas y traspasos</li>
                                <li><i class="bi bi-dot"></i> Producción, recetas y gastos indirectos</li>
                                <li><i class="bi bi-dot"></i> Sesiones y movimientos de caja</li>
                                <li><i class="bi bi-dot"></i> Movimientos de tesorería</li>
                                <li><i class="bi bi-dot"></i> Pedidos / órdenes de operación</li>
                                <li><i class="bi bi-dot"></i> Stock por almacén (a 0)</li>
                                <li><i class="bi bi-dot"></i> Maquinaria, promotores, plantillas</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Para confirmar, escribe el nombre exacto de la empresa:
                        <span class="text-danger" id="targetNameLabel">—</span>
                    </label>
                    <input type="text" name="confirm_name" id="confirmName" class="form-control" autocomplete="off"
                           placeholder="Escribe el nombre de la empresa seleccionada">
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="understand" value="1" id="understand">
                    <label class="form-check-label" for="understand">
                        Entiendo que esta acción es <strong>irreversible</strong> y eliminará los datos indicados.
                    </label>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-danger" id="resetBtn" disabled
                            onclick="return confirm('Esta acción eliminará los datos de la empresa seleccionada y no se puede deshacer. ¿Continuar?')">
                        <i class="bi bi-trash3 me-1"></i> Reiniciar datos
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sel   = document.getElementById('companySelect');
    const name  = document.getElementById('confirmName');
    const chk   = document.getElementById('understand');
    const btn   = document.getElementById('resetBtn');
    const label = document.getElementById('targetNameLabel');
    if (!sel || !btn) return;

    function target() {
        const opt = sel.options[sel.selectedIndex];
        return (opt && opt.dataset.name) ? opt.dataset.name : '';
    }
    function refresh() {
        const t = target();
        label.textContent = t || '—';
        btn.disabled = !(t !== '' && name.value.trim() === t && chk.checked);
    }
    sel.addEventListener('change', refresh);
    name.addEventListener('input', refresh);
    chk.addEventListener('change', refresh);
    refresh();
});
</script>
@endpush
@endsection
