{{-- Sección: Configuración de costos indirectos --}}
@php
    $selMethod = old('overhead_distribution_method',
        isset($company) ? ($company->overhead_distribution_method ?? 'manual') : 'manual');
    $selRate = old('overhead_fixed_rate',
        isset($company) ? ($company->overhead_fixed_rate ?? 0) : 0);

    $methods = [
        'manual' => [
            'icon'    => 'bi-pencil-square',
            'color'   => 'secondary',
            'title'   => 'Manual',
            'badge'   => 'Más flexible',
            'desc'    => 'Ingresas el monto de overhead directamente en cada orden de producción. Total control sin cálculo automático.',
            'example' => 'Ej: la producción de hoy tuvo $120 de gastos → ingresas $120 manualmente.',
        ],
        'por_unidades' => [
            'icon'    => 'bi-stack',
            'color'   => 'primary',
            'title'   => 'Por unidades producidas',
            'badge'   => 'Recomendado',
            'desc'    => 'El overhead del período se reparte proporcionalmente según cuántas unidades produce cada orden.',
            'example' => 'Ej: período $1,000 · total 500 u. → orden de 100 u. recibe $200.',
        ],
        'por_orden' => [
            'icon'    => 'bi-receipt',
            'color'   => 'info',
            'title'   => 'Por orden de producción',
            'badge'   => null,
            'desc'    => 'El overhead total del período se divide en partes iguales entre todas las órdenes, sin importar su tamaño.',
            'example' => 'Ej: período $900 · 3 órdenes → cada orden recibe $300.',
        ],
        'tasa_fija' => [
            'icon'    => 'bi-calculator',
            'color'   => 'success',
            'title'   => 'Tasa fija por unidad',
            'badge'   => null,
            'desc'    => 'Se aplica una tarifa fija configurable por cada unidad producida. Ideal cuando el overhead por unidad es conocido.',
            'example' => 'Ej: tasa $0.50/u. · orden 200 u. → overhead sugerido $100.',
        ],
    ];
@endphp

<input type="hidden" name="overhead_distribution_method" id="overheadMethodInput" value="{{ $selMethod }}">

<div class="overhead-method-grid row g-3 mb-3">
    @foreach($methods as $value => $m)
    <div class="col-md-6">
        <div class="overhead-card h-100 rounded-3 p-3 overhead-card--{{ $value }} {{ $selMethod === $value ? 'overhead-card--active' : '' }}"
             onclick="selectOverheadMethod('{{ $value }}')">
            <div class="d-flex align-items-start gap-3">
                {{-- Icon --}}
                <div class="overhead-card__icon rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 overhead-card__icon--{{ $m['color'] }}"
                     style="width:46px;height:46px;">
                    <i class="bi {{ $m['icon'] }} fs-5"></i>
                </div>
                {{-- Content --}}
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-bold small">{{ $m['title'] }}</span>
                        @if($m['badge'])
                            <span class="badge bg-{{ $m['color'] }} bg-opacity-15 text-{{ $m['color'] }}"
                                  style="font-size:.68rem;">{{ $m['badge'] }}</span>
                        @endif
                        <span class="ms-auto overhead-card__check text-{{ $m['color'] }}"
                              style="{{ $selMethod === $value ? '' : 'visibility:hidden;' }}">
                            <i class="bi bi-check-circle-fill"></i>
                        </span>
                    </div>
                    <p class="text-muted mb-1" style="font-size:.82rem;line-height:1.4;">{{ $m['desc'] }}</p>
                    <div class="overhead-card__example d-flex align-items-center gap-1 rounded-2 px-2 py-1 mt-1"
                         style="font-size:.75rem;">
                        <i class="bi bi-lightbulb text-warning flex-shrink-0"></i>
                        <span class="text-muted">{{ $m['example'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Tasa fija — input condicional --}}
<div id="fixedRateRow" class="{{ $selMethod === 'tasa_fija' ? '' : 'd-none' }}">
    <div class="card border-0 bg-success bg-opacity-5 border-start border-success border-3 mb-3">
        <div class="card-body py-3">
            <label class="form-label fw-semibold small d-block mb-1">
                <i class="bi bi-calculator text-success me-1"></i>Tasa fija por unidad producida
            </label>
            <div class="input-group" style="max-width:260px">
                <span class="input-group-text">$</span>
                <input type="number" step="0.0001" min="0" name="overhead_fixed_rate"
                       class="form-control"
                       value="{{ $selRate }}"
                       placeholder="0.0000">
                <span class="input-group-text text-muted">/unidad</span>
            </div>
            <div class="form-text mt-1">
                Monto de overhead asignado automáticamente por cada unidad producida.
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.overhead-card {
    cursor: pointer;
    border: 2px solid #e9ecef;
    background: #fff;
    transition: border-color .18s, background .18s, box-shadow .18s;
    user-select: none;
}
.overhead-card:hover { border-color: #adb5bd; background: #f8f9fa; box-shadow: 0 2px 8px rgba(0,0,0,.06); }

.overhead-card--active.overhead-card--manual      { border-color: #6c757d; background: #f8f9fa; }
.overhead-card--active.overhead-card--por_unidades{ border-color: #0d6efd; background: #f0f6ff; }
.overhead-card--active.overhead-card--por_orden   { border-color: #0dcaf0; background: #f0fafd; }
.overhead-card--active.overhead-card--tasa_fija   { border-color: #198754; background: #f0fff6; }

.overhead-card__icon--secondary { background:#e9ecef;  color:#6c757d; }
.overhead-card__icon--primary   { background:#e8f0fe;  color:#0d6efd; }
.overhead-card__icon--info      { background:#e0f7fa;  color:#0dcaf0; }
.overhead-card__icon--success   { background:#d1f2e1;  color:#198754; }

.overhead-card__example { background: rgba(0,0,0,.03); }
</style>
@endpush

@push('scripts')
<script>
function selectOverheadMethod(value) {
    document.getElementById('overheadMethodInput').value = value;

    document.querySelectorAll('.overhead-card').forEach(card => {
        const m = card.getAttribute('onclick').match(/'([^']+)'/)[1];
        card.classList.toggle('overhead-card--active', m === value);
        const check = card.querySelector('.overhead-card__check');
        if (check) check.style.visibility = m === value ? '' : 'hidden';
    });

    const rateRow = document.getElementById('fixedRateRow');
    if (rateRow) rateRow.classList.toggle('d-none', value !== 'tasa_fija');
}
</script>
@endpush
