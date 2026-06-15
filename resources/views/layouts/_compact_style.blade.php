{{-- Estilo compacto reutilizable. Envolver el contenido de la vista en <div class="view-compact"> ... </div>.
     Las reglas están scopeadas bajo .view-compact para no afectar el resto de la app. --}}
@once
@push('styles')
<style>
    .view-compact { font-size: .82rem; }

    .view-compact h1 { font-size: 1.3rem; }
    .view-compact h2 { font-size: 1.15rem; }
    .view-compact h3, .view-compact h4 { font-size: 1rem; }
    .view-compact h5 { font-size: .92rem; }
    .view-compact h6 { font-size: .8rem; }
    .view-compact .lead { font-size: .95rem; }

    .view-compact .card { border-radius: 9px; }
    .view-compact .card-body { padding: .85rem 1rem; }
    .view-compact .card-header { padding: .6rem 1rem; }

    .view-compact .table { font-size: .8rem; margin-bottom: 0; }
    .view-compact .table > :not(caption) > * > * { padding: .42rem .55rem; }
    .view-compact .table th { font-weight: 600; }

    .view-compact .btn { padding: .28rem .6rem; font-size: .78rem; border-radius: 7px; }
    .view-compact .btn-lg { padding: .45rem .9rem; font-size: .9rem; }
    .view-compact .btn-sm { padding: .18rem .45rem; font-size: .72rem; }

    .view-compact .form-control,
    .view-compact .form-select { font-size: .8rem; padding: .32rem .6rem; border-radius: 7px; }
    .view-compact .form-control-sm,
    .view-compact .form-select-sm { font-size: .74rem; padding: .2rem .45rem; }
    .view-compact .form-label { font-size: .76rem; margin-bottom: .2rem; font-weight: 600; }
    .view-compact .input-group-text { font-size: .8rem; padding: .32rem .55rem; }

    .view-compact .badge { font-size: .68rem; font-weight: 600; }

    .view-compact .kpi-card { padding: 14px; border-radius: 10px; }
    .view-compact .kpi-value { font-size: 1.35rem; }
    .view-compact .kpi-label { font-size: .72rem; }
    .view-compact .kpi-trend { font-size: .68rem; }
    .view-compact .kpi-icon { width: 38px; height: 38px; font-size: 1rem; border-radius: 10px; }

    .view-compact .page-head h1 { margin-bottom: .15rem; }
    .view-compact .text-muted.small,
    .view-compact small { font-size: .72rem; }
</style>
@endpush
@endonce
