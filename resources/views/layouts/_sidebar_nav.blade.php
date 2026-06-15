@php
    $pfx        = $prefix ?? 'nav';
    $crmOpen    = request()->routeIs('crm.*');
    $salesOpen  = request()->routeIs('sales.*')
               || request()->routeIs('pos.*')
               || request()->routeIs('sales-quotations.*')
               || request()->routeIs('sales-returns.*')
               || request()->routeIs('receivables.*');
    $opsOpen    = request()->routeIs('inventory-movements.*')
               || request()->routeIs('orders.*')
               || request()->routeIs('productions.*')
               || request()->routeIs('recipes.*')
               || request()->routeIs('overhead-periods.*');
    $invOpen    = request()->routeIs('products.*')
               || request()->routeIs('warehouses.*')
               || request()->routeIs('transfers.*');
    $finOpen    = request()->routeIs('cash-registers.*')
               || request()->routeIs('cash-sessions.*')
               || request()->routeIs('movimientos.*')
               || request()->routeIs('treasury.*')
               || request()->routeIs('arqueos.*');
    $purchOpen  = request()->routeIs('purchases.*');
    $comercOpen = request()->routeIs('promoters.*')
               || request()->routeIs('reports.*');
    $adminOpen  = request()->routeIs('companies.*')
               || request()->routeIs('roles.*')
               || request()->routeIs('users.*')
               || request()->routeIs('branches.*')
               || request()->routeIs('measurement-units.*')
               || request()->routeIs('machinery.*')
               || request()->routeIs('cargos.*')
               || request()->routeIs('personal.*')
               || request()->routeIs('document-templates.*');
    $currentCompany = $currentCompany ?? auth()->user()->getCurrentCompany();
@endphp

{{-- Interface --}}
<div class="sidebar-section-title mt-2">Interface</div>
<ul class="nav flex-column gap-1 mb-2">
    <li class="nav-item">
        <a class="nav-link app-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           href="{{ route('dashboard') }}">
            <i class="bi bi-house"></i> Overview
        </a>
    </li>
</ul>

{{-- CRM --}}
<div class="sidebar-sec" data-sec="crm">
<button class="sidebar-section-btn {{ $crmOpen ? '' : 'collapsed' }}"
        data-bs-toggle="collapse" data-bs-target="#{{ $pfx }}CRM"
        aria-expanded="{{ $crmOpen ? 'true' : 'false' }}">
    <span><i class="bi bi-people-fill me-1"></i>CRM</span>
    <i class="bi bi-chevron-down sidebar-chevron"></i>
</button>
<div class="collapse {{ $crmOpen ? 'show' : '' }}" id="{{ $pfx }}CRM" data-sidebar-section>
    <ul class="nav flex-column gap-1 mb-1">
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('crm.clients.*') && request('status') !== 'prospecto' ? 'active' : '' }}"
               href="{{ route('crm.clients.index') }}">
                <i class="bi bi-people"></i> Clientes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('crm.clients.*') && request('status') === 'prospecto' ? 'active' : '' }}"
               href="{{ route('crm.clients.index') }}?status=prospecto">
                <i class="bi bi-person-plus"></i> Prospectos
            </a>
        </li>
        <li class="nav-item">
            <button class="nav-link app-link w-100 text-start d-flex justify-content-between align-items-center collapsed"
                    data-bs-toggle="collapse" data-bs-target="#{{ $pfx }}Activ"
                    aria-expanded="false" style="border:none;background:none;cursor:pointer;">
                <span><i class="bi bi-calendar2-check me-1"></i> Actividades</span>
                <i class="bi bi-chevron-down sidebar-chevron" style="font-size:.6rem;"></i>
            </button>
            <div class="collapse" id="{{ $pfx }}Activ">
                <ul class="nav flex-column gap-1 ps-2 mt-1">
                    <li class="nav-item">
                        <a href="#" class="nav-link app-link app-link--sub app-link--soon"
                           tabindex="-1" aria-disabled="true">
                            <i class="bi bi-telephone me-1"></i> Llamadas
                            <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link app-link app-link--sub app-link--soon"
                           tabindex="-1" aria-disabled="true">
                            <i class="bi bi-camera-video me-1"></i> Reuniones
                            <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link app-link app-link--sub app-link--soon"
                           tabindex="-1" aria-disabled="true">
                            <i class="bi bi-check2-square me-1"></i> Tareas
                            <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link app-link app-link--sub app-link--soon"
                           tabindex="-1" aria-disabled="true">
                            <i class="bi bi-calendar3 me-1"></i> Agenda
                            <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</div>
</div>{{-- /sidebar-sec crm --}}

{{-- Ventas --}}
<div class="sidebar-sec" data-sec="sales">
<button class="sidebar-section-btn {{ $salesOpen ? '' : 'collapsed' }}"
        data-bs-toggle="collapse" data-bs-target="#{{ $pfx }}Sales"
        aria-expanded="{{ $salesOpen ? 'true' : 'false' }}">
    <span><i class="bi bi-cash-coin me-1"></i>Ventas</span>
    <i class="bi bi-chevron-down sidebar-chevron"></i>
</button>
<div class="collapse {{ $salesOpen ? 'show' : '' }}" id="{{ $pfx }}Sales" data-sidebar-section>
    <ul class="nav flex-column gap-1 mb-1">
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('sales.dashboard') ? 'active' : '' }}"
               href="{{ route('sales.dashboard') }}">
                <i class="bi bi-graph-up"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('pos.*') ? 'active' : '' }}"
               href="{{ route('pos.index') }}">
                <i class="bi bi-upc-scan"></i> POS
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('sales.index') || request()->routeIs('sales.show') || request()->routeIs('sales.create') ? 'active' : '' }}"
               href="{{ route('sales.index') }}">
                <i class="bi bi-cart3"></i> Ventas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('sales-quotations.*') ? 'active' : '' }}"
               href="{{ route('sales-quotations.index') }}">
                <i class="bi bi-file-earmark-text"></i> Cotizaciones
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link app-link app-link--soon" tabindex="-1" aria-disabled="true">
                <i class="bi bi-bag-check me-1"></i> Pedidos
                <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link app-link app-link--soon" tabindex="-1" aria-disabled="true">
                <i class="bi bi-truck me-1"></i> Entregas
                <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('sales-returns.*') ? 'active' : '' }}"
               href="{{ route('sales-returns.index') }}">
                <i class="bi bi-arrow-return-left"></i> Devoluciones
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link app-link app-link--soon" tabindex="-1" aria-disabled="true">
                <i class="bi bi-percent me-1"></i> Promociones
                <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('receivables.*') ? 'active' : '' }}"
               href="{{ route('receivables.index') }}">
                <i class="bi bi-cash-coin"></i> Cuentas por Cobrar
            </a>
        </li>
    </ul>
</div>
</div>{{-- /sidebar-sec sales --}}

{{-- Compras --}}
<div class="sidebar-sec" data-sec="purch">
<button class="sidebar-section-btn {{ $purchOpen ? '' : 'collapsed' }}"
        data-bs-toggle="collapse" data-bs-target="#{{ $pfx }}Purch"
        aria-expanded="{{ $purchOpen ? 'true' : 'false' }}">
    <span><i class="bi bi-cart-check-fill me-1"></i>Compras</span>
    <i class="bi bi-chevron-down sidebar-chevron"></i>
</button>
<div class="collapse {{ $purchOpen ? 'show' : '' }}" id="{{ $pfx }}Purch" data-sidebar-section>
    <ul class="nav flex-column gap-1 mb-1">
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('purchases.suppliers.*') ? 'active' : '' }}"
               href="{{ route('purchases.suppliers.index') }}">
                <i class="bi bi-truck"></i> Proveedores
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('purchases.requests.*') ? 'active' : '' }}"
               href="{{ route('purchases.requests.index') }}">
                <i class="bi bi-file-earmark-plus"></i> Solicitudes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('purchases.quotations.*') ? 'active' : '' }}"
               href="{{ route('purchases.quotations.index') }}">
                <i class="bi bi-file-earmark-text"></i> Cotizaciones
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('purchases.orders.*') ? 'active' : '' }}"
               href="{{ route('purchases.orders.index') }}">
                <i class="bi bi-bag-check"></i> Órdenes de Compra
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('purchases.receptions.*') ? 'active' : '' }}"
               href="{{ route('purchases.receptions.index') }}">
                <i class="bi bi-box-arrow-in-down-right"></i> Recepción
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('purchases.returns.*') ? 'active' : '' }}"
               href="{{ route('purchases.returns.index') }}">
                <i class="bi bi-arrow-return-left"></i> Devoluciones
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('purchases.payables.*') ? 'active' : '' }}"
               href="{{ route('purchases.payables.index') }}">
                <i class="bi bi-credit-card-2-front"></i> Cuentas por Pagar
            </a>
        </li>
    </ul>
</div>
</div>{{-- /sidebar-sec purch --}}

{{-- Operaciones --}}
<div class="sidebar-sec" data-sec="ops">
<button class="sidebar-section-btn {{ $opsOpen ? '' : 'collapsed' }}"
        data-bs-toggle="collapse" data-bs-target="#{{ $pfx }}Ops"
        aria-expanded="{{ $opsOpen ? 'true' : 'false' }}">
    <span><i class="bi bi-gear me-1"></i>Operaciones</span>
    <i class="bi bi-chevron-down sidebar-chevron"></i>
</button>
<div class="collapse {{ $opsOpen ? 'show' : '' }}" id="{{ $pfx }}Ops" data-sidebar-section>
    <ul class="nav flex-column gap-1 mb-1">
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('inventory-movements.*') ? 'active' : '' }}"
               href="{{ route('inventory-movements.index') }}">
                <i class="bi bi-arrow-down-up"></i> Movimientos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('orders.*') ? 'active' : '' }}"
               href="{{ route('orders.index') }}">
                <i class="bi bi-clipboard2-data"></i> Órdenes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('productions.*') ? 'active' : '' }}"
               href="{{ route('productions.index') }}">
                <i class="bi bi-gear-wide-connected"></i> Producción
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('recipes.*') ? 'active' : '' }}"
               href="{{ route('recipes.index') }}">
                <i class="bi bi-journal-text"></i> Recetas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('overhead-periods.*') ? 'active' : '' }}"
               href="{{ route('overhead-periods.index') }}">
                <i class="bi bi-calendar2-week"></i> Gastos período
            </a>
        </li>
    </ul>
</div>
</div>{{-- /sidebar-sec ops --}}

{{-- Inventario --}}
<div class="sidebar-sec" data-sec="inv">
<button class="sidebar-section-btn {{ $invOpen ? '' : 'collapsed' }}"
        data-bs-toggle="collapse" data-bs-target="#{{ $pfx }}Inv"
        aria-expanded="{{ $invOpen ? 'true' : 'false' }}">
    <span><i class="bi bi-boxes me-1"></i>Inventario</span>
    <i class="bi bi-chevron-down sidebar-chevron"></i>
</button>
<div class="collapse {{ $invOpen ? 'show' : '' }}" id="{{ $pfx }}Inv" data-sidebar-section>
    <ul class="nav flex-column gap-1 mb-1">
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('products.*') && request('tab') === 'MATERIA PRIMA' ? 'active' : '' }}"
               href="{{ route('products.index', ['tab' => 'MATERIA PRIMA']) }}">
                <i class="bi bi-layers"></i> Materias Primas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('products.*') && (request('tab') === 'PRODUCTO FINAL' || !request()->has('tab')) ? 'active' : '' }}"
               href="{{ route('products.index', ['tab' => 'PRODUCTO FINAL']) }}">
                <i class="bi bi-box-seam"></i> Prod. Terminados
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link app-link app-link--soon" tabindex="-1" aria-disabled="true">
                <i class="bi bi-circle-half me-1"></i> Semi Elaborados
                <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link app-link app-link--soon" tabindex="-1" aria-disabled="true">
                <i class="bi bi-cup me-1"></i> Envases
                <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link app-link app-link--soon" tabindex="-1" aria-disabled="true">
                <i class="bi bi-tags me-1"></i> Etiquetas
                <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link app-link app-link--soon" tabindex="-1" aria-disabled="true">
                <i class="bi bi-droplet me-1"></i> Insumos
                <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}"
               href="{{ route('warehouses.index') }}">
                <i class="bi bi-building-add"></i> Almacenes
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link app-link app-link--soon" tabindex="-1" aria-disabled="true">
                <i class="bi bi-bar-chart-steps me-1"></i> Kardex
                <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('transfers.*') ? 'active' : '' }}"
               href="{{ route('transfers.index') }}">
                <i class="bi bi-arrow-left-right"></i> Transferencias
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link app-link app-link--soon" tabindex="-1" aria-disabled="true">
                <i class="bi bi-sliders me-1"></i> Ajustes
                <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link app-link app-link--soon" tabindex="-1" aria-disabled="true">
                <i class="bi bi-clipboard-data me-1"></i> Conteos
                <span class="badge bg-secondary ms-auto badge-soon">Próx.</span>
            </a>
        </li>
    </ul>
</div>
</div>{{-- /sidebar-sec inv --}}

{{-- Finanzas --}}
<div class="sidebar-sec" data-sec="fin">
<button class="sidebar-section-btn {{ $finOpen ? '' : 'collapsed' }}"
        data-bs-toggle="collapse" data-bs-target="#{{ $pfx }}Fin"
        aria-expanded="{{ $finOpen ? 'true' : 'false' }}">
    <span><i class="bi bi-cash me-1"></i>Finanzas</span>
    <i class="bi bi-chevron-down sidebar-chevron"></i>
</button>
<div class="collapse {{ $finOpen ? 'show' : '' }}" id="{{ $pfx }}Fin" data-sidebar-section>
    <ul class="nav flex-column gap-1 mb-1">
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('cash-registers.*') || request()->routeIs('cash-sessions.*') ? 'active' : '' }}"
               href="{{ route('cash-registers.index') }}">
                <i class="bi bi-cash-stack"></i> Cajas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('movimientos.*') ? 'active' : '' }}"
               href="{{ route('movimientos.index') }}">
                <i class="bi bi-arrow-left-right"></i> Movimientos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('treasury.*') ? 'active' : '' }}"
               href="{{ route('treasury.index') }}">
                <i class="bi bi-bank"></i> Tesorería
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('arqueos.*') ? 'active' : '' }}"
               href="{{ route('arqueos.index') }}">
                <i class="bi bi-clipboard-check"></i> Arqueos
            </a>
        </li>
    </ul>
</div>
</div>{{-- /sidebar-sec fin --}}

{{-- Comercial --}}
<div class="sidebar-sec" data-sec="com">
<button class="sidebar-section-btn {{ $comercOpen ? '' : 'collapsed' }}"
        data-bs-toggle="collapse" data-bs-target="#{{ $pfx }}Com"
        aria-expanded="{{ $comercOpen ? 'true' : 'false' }}">
    <span><i class="bi bi-megaphone me-1"></i>Comercial</span>
    <i class="bi bi-chevron-down sidebar-chevron"></i>
</button>
<div class="collapse {{ $comercOpen ? 'show' : '' }}" id="{{ $pfx }}Com" data-sidebar-section>
    <ul class="nav flex-column gap-1 mb-1">
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('promoters.*') ? 'active' : '' }}"
               href="{{ route('promoters.index') }}">
                <i class="bi bi-megaphone"></i> Promotores
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
               href="{{ route('reports.index') }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Reportes
            </a>
        </li>
    </ul>
</div>
</div>{{-- /sidebar-sec com --}}

{{-- Administración --}}
<div class="sidebar-sec" data-sec="admin">
<button class="sidebar-section-btn {{ $adminOpen ? '' : 'collapsed' }}"
        data-bs-toggle="collapse" data-bs-target="#{{ $pfx }}Admin"
        aria-expanded="{{ $adminOpen ? 'true' : 'false' }}">
    <span><i class="bi bi-gear-fill me-1"></i>Administración</span>
    <i class="bi bi-chevron-down sidebar-chevron"></i>
</button>
<div class="collapse {{ $adminOpen ? 'show' : '' }}" id="{{ $pfx }}Admin" data-sidebar-section>
    <ul class="nav flex-column gap-1 mb-1">
        @if(auth()->user()->is_super_admin)
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('companies.*') ? 'active' : '' }}"
                   href="{{ route('companies.index') }}">
                    <i class="bi bi-building"></i> Empresas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                   href="{{ route('roles.index') }}">
                    <i class="bi bi-shield-lock"></i> Roles
                </a>
            </li>
        @endif
        @if(auth()->user()->hasPermissionInCompany('users.view', $currentCompany))
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                   href="{{ route('users.index') }}">
                    <i class="bi bi-person-gear"></i> Usuarios
                </a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('branches.*') ? 'active' : '' }}"
               href="{{ route('branches.index') }}">
                <i class="bi bi-diagram-2"></i> Sucursales
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('measurement-units.*') ? 'active' : '' }}"
               href="{{ route('measurement-units.index') }}">
                <i class="bi bi-rulers"></i> Unidades de medida
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('machinery.*') ? 'active' : '' }}"
               href="{{ route('machinery.index') }}">
                <i class="bi bi-tools"></i> Maquinaria
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('cargos.*') ? 'active' : '' }}"
               href="{{ route('cargos.index') }}">
                <i class="bi bi-briefcase"></i> Cargos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('personal.*') ? 'active' : '' }}"
               href="{{ route('personal.index') }}">
                <i class="bi bi-person-vcard"></i> Personal
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link app-link {{ request()->routeIs('document-templates.*') ? 'active' : '' }}"
               href="{{ route('document-templates.index') }}">
                <i class="bi bi-file-earmark-ruled"></i> Plantillas
            </a>
        </li>
    </ul>
</div>
</div>{{-- /sidebar-sec admin --}}
