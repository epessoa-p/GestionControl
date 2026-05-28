@extends('layouts.base')

@section('content')
@php
    $currentCompany = auth()->user()->getCurrentCompany();
    $activeCompanies = auth()->user()->activeCompanies()->get();
@endphp

<div class="app-shell d-flex">
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <button class="btn btn-link p-0 me-2 d-lg-none text-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebarMobile" aria-controls="appSidebarMobile">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="brand-icon"><i class="bi bi-grid-1x2-fill"></i></div>
            <div>
                <div class="brand-title">MATERIAL ADMIN PRO</div>
                <small class="text-muted">Sistema CRM</small>
            </div>
        </div>

        <div class="sidebar-section-title">Interface</div>
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-house"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('trackings.*') ? 'active' : '' }}" href="{{ route('trackings.index') }}">
                    <i class="bi bi-clipboard-check"></i> Seguimientos
                </a>
            </li>
        </ul>

        <div class="sidebar-section-title mt-4">CRM</div>
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('crm.clients.*') ? 'active' : '' }}" href="{{ route('crm.clients.index') }}">
                    <i class="bi bi-people"></i> Clientes
                </a>
            </li>
        </ul>

        <div class="sidebar-section-title mt-4">Operaciones</div>
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('entries.*') ? 'active' : '' }}" href="{{ route('entries.index') }}">
                    <i class="bi bi-box-arrow-in-down"></i> Entradas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('departures.*') ? 'active' : '' }}" href="{{ route('departures.index') }}">
                    <i class="bi bi-box-arrow-up"></i> Salidas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
                    <i class="bi bi-cart3"></i> Ventas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('transfers.*') ? 'active' : '' }}" href="{{ route('transfers.index') }}">
                    <i class="bi bi-arrow-left-right"></i> Traspasos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                    <i class="bi bi-clipboard2-data"></i> Órdenes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('productions.*') ? 'active' : '' }}" href="{{ route('productions.index') }}">
                    <i class="bi bi-gear-wide-connected"></i> Produccion
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('recipes.*') ? 'active' : '' }}" href="{{ route('recipes.index') }}">
                    <i class="bi bi-journal-text"></i> Recetas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('overhead-periods.*') ? 'active' : '' }}" href="{{ route('overhead-periods.index') }}">
                    <i class="bi bi-calendar2-week"></i> Gastos período
                </a>
            </li>
        </ul>

        <div class="sidebar-section-title mt-4">Finanzas</div>
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('cash-registers.*') || request()->routeIs('cash-sessions.*') ? 'active' : '' }}" href="{{ route('cash-registers.index') }}">
                    <i class="bi bi-cash-stack"></i> Cajas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('petty-cash.*') ? 'active' : '' }}" href="{{ route('petty-cash.index') }}">
                    <i class="bi bi-wallet2"></i> Caja Chica
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('commissions.*') ? 'active' : '' }}" href="{{ route('commissions.index') }}">
                    <i class="bi bi-percent"></i> Comisiones
                </a>
            </li>
        </ul>

        <div class="sidebar-section-title mt-4">Comercial</div>
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('promoters.*') ? 'active' : '' }}" href="{{ route('promoters.index') }}">
                    <i class="bi bi-megaphone"></i> Promotores
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> Reportes
                </a>
            </li>
        </ul>

        <div class="sidebar-section-title mt-4">Administracion</div>
        <ul class="nav flex-column gap-1">
            @if(auth()->user()->is_super_admin)
                <li class="nav-item">
                    <a class="nav-link app-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                        <i class="bi bi-building"></i> Empresas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link app-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                        <i class="bi bi-shield-lock"></i> Roles
                    </a>
                </li>
            @endif
            @if(auth()->user()->hasPermissionInCompany('users.view', $currentCompany))
                <li class="nav-item">
                    <a class="nav-link app-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="bi bi-person-gear"></i> Usuarios
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('branches.*') ? 'active' : '' }}" href="{{ route('branches.index') }}">
                    <i class="bi bi-diagram-2"></i> Sucursales
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <i class="bi bi-box-seam"></i> Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('measurement-units.*') ? 'active' : '' }}" href="{{ route('measurement-units.index') }}">
                    <i class="bi bi-rulers"></i> Unidades de medida
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}" href="{{ route('warehouses.index') }}">
                    <i class="bi bi-building-add"></i> Almacenes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('machinery.*') ? 'active' : '' }}" href="{{ route('machinery.index') }}">
                    <i class="bi bi-tools"></i> Maquinaria
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('cargos.*') ? 'active' : '' }}" href="{{ route('cargos.index') }}">
                    <i class="bi bi-briefcase"></i> Cargos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('personal.*') ? 'active' : '' }}" href="{{ route('personal.index') }}">
                    <i class="bi bi-person-vcard"></i> Personal
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link app-link {{ request()->routeIs('document-templates.*') ? 'active' : '' }}" href="{{ route('document-templates.index') }}">
                    <i class="bi bi-file-earmark-ruled"></i> Plantillas
                </a>
            </li>
        </ul>
    </aside>

    <main class="app-main">
        <nav class="navbar navbar-expand-lg app-topbar mb-4">
            <div class="container-fluid px-0">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-icon d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebarMobile" aria-controls="appSidebarMobile">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="topbar-label">Overview</span>
                    <span class="topbar-separator">|</span>
                    <span class="text-muted small">{{ auth()->user()->is_super_admin ? 'Modo Global' : ($currentCompany?->name ?? 'Sin empresa activa') }}</span>

                    @if(!auth()->user()->is_super_admin && $activeCompanies->count() > 1)
                        <div class="dropdown">
                            <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-buildings"></i> Empresa
                            </button>
                            <ul class="dropdown-menu shadow border-0">
                                @foreach($activeCompanies as $company)
                                    <li>
                                        <form action="{{ route('set-company', $company->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item d-flex justify-content-between align-items-center">
                                                <span>{{ $company->name }}</span>
                                                @if($currentCompany && $currentCompany->id === $company->id)
                                                    <i class="bi bi-check-lg text-success"></i>
                                                @endif
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                @php
                    $navCashRegister = null;
                    $navCashSession  = null;
                    if (!auth()->user()->is_super_admin && $currentCompany) {
                        $navPersonal = \App\Models\Personal::where('user_id', auth()->id())
                            ->where('company_id', $currentCompany->id)
                            ->first();
                        if ($navPersonal) {
                            $navCashRegister = \App\Models\CashRegister::where('assigned_personal_id', $navPersonal->id)
                                ->where('company_id', $currentCompany->id)
                                ->where('active', true)
                                ->first();
                            if ($navCashRegister) {
                                $navCashSession = $navCashRegister->activeSession();
                                $navCashSession?->load('cashRegister.branch');
                            }
                        }
                    }
                @endphp

                <div class="d-flex align-items-center gap-2">

                    {{-- Botón de caja --}}
                    @if($navCashRegister)
                        @if($navCashSession)
                            @php
                                $navOpenMinutes = $navCashSession->opened_at?->diffInMinutes(now());
                                $navDuration = $navOpenMinutes >= 60
                                    ? floor($navOpenMinutes / 60) . 'h ' . ($navOpenMinutes % 60) . 'm'
                                    : $navOpenMinutes . 'm';
                            @endphp
                            <div class="dropdown">
                                <button class="btn btn-cash-open dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-cash-stack btn-cash-icon"></i>
                                    <span class="btn-cash-dot btn-cash-dot--open btn-cash-pulse"></span>
                                    <span class="btn-cash-text">
                                        <span class="btn-cash-sublabel">Caja abierta</span>
                                        <span class="btn-cash-name">{{ $navCashSession->cashRegister?->branch?->name ?? $navCashRegister->name }}</span>
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0" style="min-width:240px;">
                                    {{-- Info card --}}
                                    <li>
                                        <div class="cash-dd-info px-3 pt-3 pb-2">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <div class="cash-dd-dot-wrap">
                                                    <span class="btn-cash-dot btn-cash-dot--open btn-cash-pulse"></span>
                                                </div>
                                                <span class="fw-semibold text-dark small">{{ $navCashRegister->name }}</span>
                                            </div>
                                            <div class="d-flex flex-column gap-1">
                                                <div class="cash-dd-row">
                                                    <i class="bi bi-clock text-muted"></i>
                                                    <span>Abierta {{ $navCashSession->opened_at?->format('H:i') }} · <strong>{{ $navDuration }}</strong></span>
                                                </div>
                                                @if($navCashSession->personal)
                                                <div class="cash-dd-row">
                                                    <i class="bi bi-person text-muted"></i>
                                                    <span>{{ $navCashSession->personal->full_name }}</span>
                                                </div>
                                                @endif
                                                @if($navCashSession->cashRegister?->branch)
                                                <div class="cash-dd-row">
                                                    <i class="bi bi-geo-alt text-muted"></i>
                                                    <span>{{ $navCashSession->cashRegister->branch->name }}</span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                    <li><hr class="dropdown-divider my-0"></li>
                                    <li>
                                        <a class="dropdown-item cash-dd-action py-2 px-3" href="{{ route('cash-sessions.show', $navCashSession) }}">
                                            <span class="cash-dd-action-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-list-ul"></i></span>
                                            <span>Ver movimientos</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item cash-dd-action py-2 px-3 mb-1" href="{{ route('cash-sessions.show', $navCashSession) }}#cerrar">
                                            <span class="cash-dd-action-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-lock"></i></span>
                                            <span class="text-danger">Cerrar caja</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        @else
                            <button type="button" class="btn btn-cash-closed" data-bs-toggle="modal" data-bs-target="#navOpenCashModal">
                                <span class="btn-cash-dot btn-cash-dot--closed"></span> Abrir caja
                            </button>
                        @endif
                    @endif

                    <div class="dropdown">
                        <button class="btn btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><span class="dropdown-item-text text-muted">{{ auth()->user()->name }}</span></li>
                            <li><span class="dropdown-item-text text-muted small">{{ auth()->user()->email }}</span></li>
                        </ul>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button class="btn btn-logout" type="submit">
                            <i class="bi bi-box-arrow-right"></i> Cerrar sesion
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        @if(isset($breadcrumbs))
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    @foreach($breadcrumbs as $label => $url)
                        @if($loop->last)
                            <li class="breadcrumb-item active">{{ $label }}</li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ $url }}" class="text-decoration-none">{{ $label }}</a></li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif

        @if($message = session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($message = session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('page')
    </main>
</div>

{{-- ── Modal: Apertura de caja ──────────────────────────────────────────────── --}}
@if(isset($navCashRegister) && $navCashRegister && !$navCashSession)
<div class="modal fade" id="navOpenCashModal" tabindex="-1" aria-labelledby="navOpenCashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content border-0 shadow-lg overflow-hidden">

            {{-- Header con fondo oscuro --}}
            <div class="modal-cash-header d-flex align-items-center gap-3 px-4 py-4">
                <div class="modal-cash-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-white">Apertura de caja</h5>
                    <small class="text-white-50">{{ $navCashRegister->name }}
                        @if($navCashRegister->branch) · {{ $navCashRegister->branch->name }} @endif
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('cash-registers.open-session', $navCashRegister) }}" method="POST">
                @csrf
                <div class="modal-body px-4 pt-4 pb-3">

                    {{-- Personal asignado --}}
                    @if($navCashRegister->assignedPersonal)
                        <div class="modal-cash-personal d-flex align-items-center gap-3 mb-4">
                            <div class="modal-cash-avatar">
                                {{ strtoupper(substr($navCashRegister->assignedPersonal->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">{{ $navCashRegister->assignedPersonal->full_name }}</div>
                                <small class="text-muted">Cajero asignado</small>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success ms-auto">
                                <i class="bi bi-person-check me-1"></i>Verificado
                            </span>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Personal <span class="text-danger">*</span></label>
                            <select name="personal_id" class="form-select" required>
                                <option value="">Seleccionar personal...</option>
                                @foreach(\App\Models\Personal::where('company_id', $currentCompany?->id)->where('active', true)->orderBy('full_name')->get() as $p)
                                    <option value="{{ $p->id }}">{{ $p->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Monto de apertura --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Monto de apertura</label>
                        <div class="modal-cash-amount-wrap">
                            <span class="modal-cash-currency">$</span>
                            <input type="number" name="opening_amount" id="navOpenAmount"
                                   class="modal-cash-amount-input"
                                   value="0" min="0" step="0.01"
                                   placeholder="0.00" required
                                   autocomplete="off">
                        </div>
                        <small class="text-muted">Dinero físico con el que inicia el turno.</small>
                    </div>

                    {{-- Notas opcionales --}}
                    <div class="mb-1">
                        <label class="form-label fw-semibold text-dark d-flex align-items-center gap-2">
                            Notas <span class="badge bg-light text-muted fw-normal">Opcional</span>
                        </label>
                        <textarea name="opening_notes" class="form-control border-0 bg-light" rows="2"
                                  placeholder="Observaciones del turno..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex gap-2">
                    <button type="button" class="btn btn-light border flex-fill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cash-submit flex-fill">
                        <i class="bi bi-unlock me-1"></i> Abrir caja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div class="offcanvas offcanvas-start" tabindex="-1" id="appSidebarMobile" aria-labelledby="appSidebarMobileLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="appSidebarMobileLabel">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="p-3">
            <div class="sidebar-section-title">Interface</div>
            <ul class="nav flex-column gap-1">
                <li><a class="nav-link app-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Overview</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('trackings.*') ? 'active' : '' }}" href="{{ route('trackings.index') }}">Seguimientos</a></li>
            </ul>
            <div class="sidebar-section-title mt-3">CRM</div>
            <ul class="nav flex-column gap-1">
                <li><a class="nav-link app-link {{ request()->routeIs('crm.clients.*') ? 'active' : '' }}" href="{{ route('crm.clients.index') }}">Clientes</a></li>
            </ul>
            <div class="sidebar-section-title mt-3">Operaciones</div>
            <ul class="nav flex-column gap-1">
                <li><a class="nav-link app-link {{ request()->routeIs('entries.*') ? 'active' : '' }}" href="{{ route('entries.index') }}">Entradas</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('departures.*') ? 'active' : '' }}" href="{{ route('departures.index') }}">Salidas</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">Ventas</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('transfers.*') ? 'active' : '' }}" href="{{ route('transfers.index') }}">Traspasos</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">Órdenes</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('productions.*') ? 'active' : '' }}" href="{{ route('productions.index') }}">Produccion</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('recipes.*') ? 'active' : '' }}" href="{{ route('recipes.index') }}">Recetas</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('overhead-periods.*') ? 'active' : '' }}" href="{{ route('overhead-periods.index') }}">Gastos período</a></li>
            </ul>
            <div class="sidebar-section-title mt-3">Finanzas</div>
            <ul class="nav flex-column gap-1">
                <li><a class="nav-link app-link {{ request()->routeIs('cash-registers.*') || request()->routeIs('cash-sessions.*') ? 'active' : '' }}" href="{{ route('cash-registers.index') }}">Cajas</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('petty-cash.*') ? 'active' : '' }}" href="{{ route('petty-cash.index') }}">Caja Chica</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('commissions.*') ? 'active' : '' }}" href="{{ route('commissions.index') }}">Comisiones</a></li>
            </ul>
            <div class="sidebar-section-title mt-3">Comercial</div>
            <ul class="nav flex-column gap-1">
                <li><a class="nav-link app-link {{ request()->routeIs('promoters.*') ? 'active' : '' }}" href="{{ route('promoters.index') }}">Promotores</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">Reportes</a></li>
            </ul>
            <div class="sidebar-section-title mt-3">Administracion</div>
            <ul class="nav flex-column gap-1">
                @if(auth()->user()->is_super_admin)
                    <li><a class="nav-link app-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">Empresas</a></li>
                    <li><a class="nav-link app-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">Roles</a></li>
                @endif
                @if(auth()->user()->hasPermissionInCompany('users.view', $currentCompany))
                    <li><a class="nav-link app-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Usuarios</a></li>
                @endif
                <li><a class="nav-link app-link {{ request()->routeIs('branches.*') ? 'active' : '' }}" href="{{ route('branches.index') }}">Sucursales</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Productos</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('measurement-units.*') ? 'active' : '' }}" href="{{ route('measurement-units.index') }}">Unidades de medida</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}" href="{{ route('warehouses.index') }}">Almacenes</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('machinery.*') ? 'active' : '' }}" href="{{ route('machinery.index') }}">Maquinaria</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('cargos.*') ? 'active' : '' }}" href="{{ route('cargos.index') }}">Cargos</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('personal.*') ? 'active' : '' }}" href="{{ route('personal.index') }}">Personal</a></li>
                <li><a class="nav-link app-link {{ request()->routeIs('document-templates.*') ? 'active' : '' }}" href="{{ route('document-templates.index') }}">Plantillas</a></li>
            </ul>
        </nav>
    </div>
</div>

@push('styles')
<style>
    .app-shell {
        min-height: 100vh;
        background: #f2f2f2;
    }

    .app-sidebar {
        width: 250px;
        background: #f8f8f8;
        border-right: 1px solid #dfdfdf;
        padding: 12px 10px;
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        padding: 10px 8px 14px;
        border-bottom: 1px solid #e0e0e0;
    }

    .brand-icon {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: grid;
        place-items: center;
        color: #4b4b4b;
        background: #ececec;
        font-size: 0.9rem;
    }

    .brand-title {
        font-weight: 700;
        font-size: 0.68rem;
        letter-spacing: 0.14em;
        line-height: 1.1;
        color: #333;
    }

    .sidebar-section-title {
        color: #828282;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: none;
        letter-spacing: 0;
        padding: 2px 10px 6px;
    }

    .app-link {
        border-radius: 6px;
        padding: 8px 10px;
        color: #464646;
        border: 1px solid transparent;
        font-size: 0.88rem;
        transition: all 0.2s ease;
    }

    .app-link:hover {
        background: #ebebeb;
        color: #202020;
    }

    .app-link.active {
        background: #ffffff;
        border-color: #d7d7d7;
        color: #111;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    }

    .app-main {
        flex: 1;
        padding: 14px 18px 24px;
    }

    .app-topbar {
        background: #1e1e1e;
        border: 0;
        border-radius: 0;
        padding: 8px 14px;
        margin-left: -18px;
        margin-right: -18px;
        margin-top: -14px;
    }

    .topbar-label {
        color: #f2f2f2;
        font-size: 0.82rem;
        font-weight: 500;
    }

    .topbar-separator {
        color: #8f8f8f;
        font-size: 0.8rem;
    }

    .btn-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid #3a3a3a;
        background: #232323;
        color: #e8e8e8;
        display: grid;
        place-items: center;
        padding: 0;
    }

    .btn-icon:hover {
        background: #2d2d2d;
        color: #fff;
    }

    .btn-logout {
        border: 1px solid #474747;
        background: #2a2a2a;
        color: #f0f0f0;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 0.82rem;
    }

    .btn-logout:hover {
        background: #363636;
        color: #fff;
    }

    /* ── Botón de caja en topbar ─────────────────────────── */
    .btn-cash-open,
    .btn-cash-closed {
        border-radius: 8px;
        padding: 5px 11px;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        text-decoration: none;
    }

    .btn-cash-open {
        background: #1a3a1a;
        border: 1px solid #2d6a2d;
        color: #6fcf6f;
    }
    .btn-cash-open:hover,
    .btn-cash-open:focus {
        background: #1f4a1f;
        color: #88df88;
    }
    .btn-cash-open::after { border-color: #6fcf6f; }

    .btn-cash-icon {
        font-size: 0.95rem;
        opacity: 0.9;
    }

    .btn-cash-text {
        display: flex;
        flex-direction: column;
        line-height: 1.15;
        text-align: left;
    }
    .btn-cash-sublabel {
        font-size: 0.65rem;
        opacity: 0.7;
        font-weight: 500;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .btn-cash-name {
        font-size: 0.82rem;
        font-weight: 600;
    }

    .btn-cash-closed {
        background: #3a1a10;
        border: 1px solid #7a3520;
        color: #f4845f;
    }
    .btn-cash-closed:hover {
        background: #4a2010;
        color: #f9a07a;
    }

    .btn-cash-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .btn-cash-dot--open   { background: #4cbb4c; box-shadow: 0 0 5px #4cbb4c88; }
    .btn-cash-dot--closed { background: #e06030; box-shadow: 0 0 5px #e0603088; }

    @keyframes cashPulse {
        0%   { box-shadow: 0 0 0 0 rgba(76,187,76,0.55); }
        70%  { box-shadow: 0 0 0 5px rgba(76,187,76,0); }
        100% { box-shadow: 0 0 0 0 rgba(76,187,76,0); }
    }
    .btn-cash-pulse { animation: cashPulse 2s infinite; }

    /* ── Dropdown de caja abierta ────────────────────────── */
    .cash-dd-info {
        background: #f8fafc;
        border-radius: 8px 8px 0 0;
    }
    .cash-dd-dot-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
    }
    .cash-dd-row {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: #555;
        padding: 1px 0;
    }
    .cash-dd-row i { font-size: 0.75rem; width: 14px; }
    .cash-dd-action {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.85rem;
    }
    .cash-dd-action-icon {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        display: grid;
        place-items: center;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    /* ── Modal apertura de caja ──────────────────────────── */
    .modal-cash-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
        min-height: 88px;
    }

    .modal-cash-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        display: grid;
        place-items: center;
        font-size: 1.35rem;
        color: #fff;
        flex-shrink: 0;
    }

    .modal-cash-personal {
        background: #f8fafc;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 14px 16px;
    }

    .modal-cash-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        font-weight: 700;
        font-size: 1rem;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }

    .modal-cash-amount-wrap {
        display: flex;
        align-items: center;
        border: 2px solid #e0e7ef;
        border-radius: 12px;
        overflow: hidden;
        transition: border-color 0.2s;
        background: #fff;
        margin-bottom: 6px;
    }
    .modal-cash-amount-wrap:focus-within {
        border-color: #0f3460;
        box-shadow: 0 0 0 3px rgba(15,52,96,0.08);
    }

    .modal-cash-currency {
        padding: 0 14px;
        font-size: 1.1rem;
        font-weight: 600;
        color: #6c757d;
        background: #f8fafc;
        border-right: 2px solid #e0e7ef;
        height: 52px;
        display: grid;
        place-items: center;
    }

    .modal-cash-amount-input {
        border: 0;
        outline: none;
        padding: 0 16px;
        font-size: 1.55rem;
        font-weight: 700;
        color: #1a1a2e;
        width: 100%;
        height: 52px;
        background: transparent;
    }
    .modal-cash-amount-input::-webkit-outer-spin-button,
    .modal-cash-amount-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .modal-cash-amount-input[type=number] { -moz-appearance: textfield; }

    .btn-cash-submit {
        background: linear-gradient(135deg, #1a1a2e, #0f3460);
        color: #fff;
        border: 0;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
    }
    .btn-cash-submit:hover {
        background: linear-gradient(135deg, #0f3460, #1a1a2e);
        color: #fff;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var openModal = document.getElementById('navOpenCashModal');
        if (openModal) {
            openModal.addEventListener('shown.bs.modal', function () {
                var amountInput = document.getElementById('navOpenAmount');
                if (amountInput) { amountInput.focus(); amountInput.select(); }
            });
        }
    });
</script>
<style>

    @media (max-width: 991.98px) {
        .app-sidebar {
            display: none;
        }

        .app-main {
            padding: 16px;
        }

        .app-topbar {
            margin-left: -16px;
            margin-right: -16px;
            margin-top: -16px;
        }
    }
</style>
@endpush
@endsection
