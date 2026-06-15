<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IceFrame Inventory')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/iceframe-icon.png') }}">

    {{-- Tipografía Inter (acabado tipo Horizon UI) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">

    {{-- Tabler (Bootstrap 5) desde CDN. No se copia toda la plantilla. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3/dist/tabler-icons.min.css">

    {{-- Tom Select: buscadores con autocompletado sobre cualquier <select>. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">

    {{-- Identidad propia IceFrame --}}
    <link rel="stylesheet" href="{{ asset('css/iceframe.css') }}">
</head>
<body class="iceframe-body">
<div class="page">

    @include('partials.sidebar')

    <div class="page-wrapper">
        @include('partials.navbar')

        <div class="page-body">
            <div class="container-xl">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <div class="d-flex">
                            <i class="ti ti-circle-check me-2"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="cerrar"></a>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <div class="d-flex">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <div>
                                <h4 class="alert-title">Revisa los datos</h4>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="cerrar"></a>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

        @include('partials.footer')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0/dist/js/tabler.min.js"></script>
{{-- ApexCharts: gráficos del dashboard. --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
{{-- Tom Select: autocompletado en los <select class="select-buscable">. --}}
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="{{ asset('js/iceframe.js') }}"></script>
@stack('scripts')
</body>
</html>
