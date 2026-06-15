@php
    $user = auth()->user();
    $esAdmin = $user && $user->esAdministrador();
@endphp
<aside class="navbar navbar-vertical navbar-expand-lg iceframe-sidebar" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('dashboard') }}" class="iceframe-brand">
                <img src="{{ asset('images/brand/iceframe-logo-sidebar.png') }}" alt="IceFrame">
            </a>
        </div>

        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">

                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <span class="nav-link-icon"><i class="ti ti-dashboard"></i></span>
                        <span class="nav-link-title">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('productos.index') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('productos.index') }}">
                        <span class="nav-link-icon"><i class="ti ti-packages"></i></span>
                        <span class="nav-link-title">Catálogo de Inventario</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('movimientos.index') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('movimientos.index') }}">
                        <span class="nav-link-icon"><i class="ti ti-history"></i></span>
                        <span class="nav-link-title">Movimientos</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('ventas.resumen') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('ventas.resumen') }}">
                        <span class="nav-link-icon"><i class="ti ti-report-money"></i></span>
                        <span class="nav-link-title">Resumen de ventas</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('productos.create') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('productos.create') }}">
                        <span class="nav-link-icon"><i class="ti ti-plus"></i></span>
                        <span class="nav-link-title">Registrar producto</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('ventas.create') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('ventas.create') }}">
                        <span class="nav-link-icon"><i class="ti ti-shopping-cart"></i></span>
                        <span class="nav-link-title">Registrar venta</span>
                    </a>
                </li>

                @if ($esAdmin)
                    <li class="nav-item {{ request()->routeIs('inventario.hurto.form') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('inventario.hurto.form') }}">
                            <span class="nav-link-icon"><i class="ti ti-alert-octagon"></i></span>
                            <span class="nav-link-title">Registrar hurto</span>
                        </a>
                    </li>
                @endif

                <li class="nav-item {{ request()->routeIs('inventario.reposicion.form') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('inventario.reposicion.form') }}">
                        <span class="nav-link-icon"><i class="ti ti-arrow-up-circle"></i></span>
                        <span class="nav-link-title">Reposición de stock</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</aside>
