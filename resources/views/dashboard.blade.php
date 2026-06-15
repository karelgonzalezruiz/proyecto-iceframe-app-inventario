@extends('layouts.app')
@section('title', 'Dashboard · IceFrame')

@section('content')
<div class="page-header d-print-none mb-3">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle">Panel</div>
            <h2 class="page-title">Dashboard</h2>
        </div>
        <div class="col-auto d-flex align-items-center gap-3">
            <span class="text-secondary d-none d-md-inline">
                <i class="ti ti-calendar-event me-1"></i>{{ now()->translatedFormat('l, d \d\e F Y') }}
            </span>
            <a href="{{ route('reportes') }}" class="btn btn-primary" target="_blank" rel="noopener">
                <i class="ti ti-report-analytics me-1"></i> Ver reportes
            </a>
        </div>
    </div>
</div>

{{-- FILA 1: 4 tarjetas stat (gradiente + tendencia + animación) --}}
<div class="row row-cards mb-3 iceframe-kpi-row">
    {{-- Productos activos --}}
    <div class="col-sm-6 col-lg-3">
        <div class="card iceframe-stat iceframe-fade h-100">
            <div class="card-body d-flex align-items-center">
                <span class="iceframe-badge if-badge-sky me-3"><i class="ti ti-packages"></i></span>
                <div class="flex-fill">
                    <div class="iceframe-stat-label mb-1">Productos activos</div>
                    <div class="iceframe-stat-value" data-count="{{ $totalProductosActivos }}">0</div>
                    <div class="small text-secondary mt-1">
                        <i class="ti ti-circle-off text-danger"></i> {{ $productosAgotados }} agotados
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bajo stock (con barra de salud) --}}
    <div class="col-sm-6 col-lg-3">
        <div class="card iceframe-stat iceframe-fade h-100" style="animation-delay:.08s">
            <div class="card-body d-flex align-items-center">
                <span class="iceframe-badge if-badge-amber me-3"><i class="ti ti-alert-triangle"></i></span>
                <div class="flex-fill">
                    <div class="iceframe-stat-label mb-1">Bajo stock</div>
                    <div class="iceframe-stat-value" data-count="{{ $productosBajoStock }}">0</div>
                    <div class="iceframe-progress mt-2" title="Salud del stock"><span style="width:0" data-width="{{ $saludStock }}%"></span></div>
                    <div class="small text-secondary mt-1">{{ $saludStock }}% del stock sin alertas</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Valor del inventario --}}
    <div class="col-sm-6 col-lg-3">
        <div class="card iceframe-stat iceframe-fade h-100" style="animation-delay:.16s">
            <div class="card-body d-flex align-items-center">
                <span class="iceframe-badge if-badge-violet me-3"><i class="ti ti-cash"></i></span>
                <div class="flex-fill">
                    <div class="iceframe-stat-label mb-1">Valor del inventario</div>
                    <div class="iceframe-stat-value" data-count="{{ $valorInventario }}" data-decimals="2" data-prefix="$">$0.00</div>
                    <div class="small text-secondary mt-1">Solo productos activos</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total vendido (con tendencia vs ayer) --}}
    <div class="col-sm-6 col-lg-3">
        <div class="card iceframe-stat iceframe-fade h-100" style="animation-delay:.24s">
            <div class="card-body d-flex align-items-center">
                <span class="iceframe-badge if-badge-green me-3"><i class="ti ti-report-money"></i></span>
                <div class="flex-fill">
                    <div class="d-flex align-items-center">
                        <div class="iceframe-stat-label mb-1">Vendido hoy</div>
                        @php $dir = $tendenciaVentas > 0 ? 'up' : ($tendenciaVentas < 0 ? 'down' : 'flat'); @endphp
                        <span class="iceframe-trend {{ $dir }} ms-auto">
                            <i class="ti ti-trending-{{ $dir === 'up' ? 'up' : ($dir === 'down' ? 'down' : 'flat') }}"></i>
                            {{ $tendenciaVentas > 0 ? '+' : '' }}{{ $tendenciaVentas }}%
                        </span>
                    </div>
                    <div class="iceframe-stat-value" data-count="{{ $ventasDelDia }}" data-decimals="2" data-prefix="$">$0.00</div>
                    <div class="small text-secondary mt-1">{{ $ventasHoyCount }} venta(s) · Total histórico ${{ number_format($totalVendido, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- FILA 2: Ventas por día + Top 5 productos --}}
<div class="row row-cards mb-3">
    <div class="col-lg-6">
        <div class="card iceframe-fade h-100" style="animation-delay:.08s">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-chart-area-line text-primary me-2"></i>Ventas de los últimos 14 días</h3>
            </div>
            <div class="card-body"><div id="chart-ventas" style="min-height:320px"></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card iceframe-fade h-100" style="animation-delay:.16s">
            <div class="card-header"><h3 class="card-title"><i class="ti ti-trophy text-primary me-2"></i>Top 5 productos más vendidos</h3></div>
            <div class="card-body"><div id="chart-top" style="min-height:320px"></div></div>
        </div>
    </div>
</div>

{{-- FILA 3: Movimientos por tipo + Valor por categoría --}}
<div class="row row-cards mb-3">
    <div class="col-lg-6">
        <div class="card iceframe-fade h-100" style="animation-delay:.08s">
            <div class="card-header"><h3 class="card-title"><i class="ti ti-chart-donut text-primary me-2"></i>Movimientos por tipo</h3></div>
            <div class="card-body"><div id="chart-movimientos" style="min-height:320px"></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card iceframe-fade h-100" style="animation-delay:.16s">
            <div class="card-header"><h3 class="card-title"><i class="ti ti-chart-bar text-primary me-2"></i>Valor de inventario por categoría</h3></div>
            <div class="card-body"><div id="chart-categorias" style="min-height:320px"></div></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.IceframeCharts = {
        ventas: @json($gVentas),
        top: @json($gTop),
        movimientos: @json($gMovimientos),
        categorias: @json($gCategorias)
    };
</script>
@endpush
