@php $user = auth()->user(); @endphp
<header class="navbar navbar-expand-md d-print-none iceframe-navbar">
    <div class="container-xl">
        <div class="navbar-nav flex-row order-md-last align-items-center ms-auto">

            @if ($user)
                <span class="badge {{ $user->esAdministrador() ? 'bg-azure' : 'bg-cyan' }} text-white me-3">
                    {{ optional($user->rol)->nombre ?? 'Sin rol' }}
                </span>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown">
                        <span class="avatar avatar-sm iceframe-avatar">
                            {{ strtoupper(mb_substr($user->nombre, 0, 1)) }}
                        </span>
                        <div class="d-none d-xl-block ps-2">
                            <div>{{ $user->nombre }}</div>
                            <div class="mt-1 small text-secondary">{{ $user->email }}</div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="ti ti-logout me-2"></i>Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="navbar-nav flex-row d-md-none">
            <a href="{{ route('dashboard') }}" class="navbar-brand iceframe-mobile-brand">
                <img src="{{ asset('images/brand/iceframe-icon.png') }}" alt="IceFrame">
            </a>
        </div>
    </div>
</header>
