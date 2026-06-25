{{-- Aviso intuitivo cuando el usuario no tiene su caja abierta.
     Espera (opcional) la variable $assignedRegister con la caja asignada al usuario. --}}
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div class="mb-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                  style="width:84px;height:84px;background:#fff7ed;border:2px solid #fed7aa;">
                <i class="bi bi-cash-stack" style="font-size:2.4rem;color:#f59e0b;"></i>
            </span>
        </div>
        <h4 class="fw-bold mb-2">Necesitas abrir tu caja para vender</h4>
        <p class="text-muted mb-4 mx-auto" style="max-width:460px;">
            Por seguridad, debes tener tu caja abierta antes de registrar ventas.
            Así cada venta queda asociada a tu turno y al arqueo de caja.
        </p>

        @if(!empty($assignedRegister))
            <a href="{{ route('cash-registers.open-session-form', $assignedRegister) }}" class="btn btn-warning btn-lg">
                <i class="bi bi-unlock-fill me-1"></i> Abrir mi caja · {{ $assignedRegister->name }}
            </a>
        @else
            <div class="alert alert-warning d-inline-block mb-0">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                No tienes una caja asignada. Contacta al administrador.
            </div>
            <div class="mt-3">
                <a href="{{ route('cash-registers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-cash-stack me-1"></i> Ver cajas
                </a>
            </div>
        @endif
    </div>
</div>
