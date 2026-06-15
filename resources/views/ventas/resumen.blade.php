@extends('layouts.app')
@section('title', 'Resumen de ventas · IceFrame')
@section('content')
<div class="page-header mb-3">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle">Ingresos</div>
            <h2 class="page-title">Resumen de ventas</h2>
        </div>
        <div class="col-auto btn-list">
            <a href="{{ route('ventas.resumen.json', array_merge(request()->query(), ['descargar' => 1])) }}" class="btn btn-outline-secondary">
                <i class="ti ti-download me-1"></i> Descargar JSON
            </a>
            <a href="{{ route('ventas.resumen.csv', request()->query()) }}" class="btn btn-outline-primary">
                <i class="ti ti-file-download me-1"></i> Descargar CSV
            </a>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('ventas.resumen') }}" class="d-flex flex-wrap flex-sm-nowrap gap-2 align-items-end">
            <div class="flex-fill" style="min-width: 200px;">
                <label class="form-label">Periodo</label>
                <select name="periodo" class="form-select">
                    <option value="dia" @selected($periodo === 'dia')>Día</option>
                    <option value="semana" @selected($periodo === 'semana')>Semana</option>
                    <option value="mes" @selected($periodo === 'mes')>Mes</option>
                    <option value="anio" @selected($periodo === 'anio')>Año</option>
                    <option value="total" @selected($periodo === 'total')>Total</option>
                </select>
            </div>
            <div class="flex-fill" style="min-width: 200px;">
                <label class="form-label">Ordenar por fecha</label>
                <select name="direccion" class="form-select">
                    <option value="desc" @selected(($direccion ?? 'desc') === 'desc')>Más recientes primero</option>
                    <option value="asc" @selected(($direccion ?? 'desc') === 'asc')>Más antiguas primero</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-calendar-stats me-1"></i> Aplicar periodo
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row row-cards mb-3">
    <div class="col-lg-6">
        <div class="card iceframe-sales-total h-100">
            <div class="card-body">
                <span class="iceframe-sales-total-icon">
                    <i class="ti ti-cash-banknote"></i>
                </span>
                <div class="iceframe-sales-total-copy">
                    <div class="iceframe-sales-total-label">{{ $etiquetaPeriodo }}</div>
                    <div class="iceframe-sales-total-value">${{ number_format($totalPeriodo, 2) }}</div>
                    <div class="iceframe-sales-total-meta">
                        {{ $ventas->total() }} {{ $ventas->total() === 1 ? 'venta completada' : 'ventas completadas' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card iceframe-payment-card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-credit-card text-primary me-2"></i>Por método de pago</h3>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-sm">
                    <thead><tr><th>Método</th><th class="text-end">Ventas</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        @forelse ($porMetodo as $fila)
                            <tr>
                                <td>
                                    <span class="iceframe-payment-method">
                                        <i class="ti {{ $fila->metodo_pago === 'Efectivo' ? 'ti-cash' : 'ti-credit-card' }}"></i>
                                        {{ $fila->metodo_pago }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">{{ $fila->cantidad }}</td>
                                <td class="text-end fw-bold">${{ number_format($fila->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-secondary">Sin ventas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Ventas completadas</h3></div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th>Método</th><th>Estado</th><th class="text-end">Total</th></tr></thead>
            <tbody>
                @forelse ($ventas as $venta)
                    <tr>
                        <td class="text-secondary">{{ $venta->id }}</td>
                        <td>{{ optional($venta->fecha)->format('d/m/Y H:i') }}</td>
                        <td>{{ optional($venta->cliente)->nombre ?? optional($venta->cliente)->cedula }}</td>
                        <td>{{ optional($venta->usuario)->nombre }}</td>
                        <td>{{ $venta->metodo_pago }}</td>
                        <td><span class="badge bg-green text-white">{{ $venta->estado }}</span></td>
                        <td class="text-end fw-bold">{{ number_format($venta->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-secondary py-4">Aún no se han registrado ventas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">
            Mostrando {{ $ventas->count() }} de {{ $ventas->total() }} ventas
        </p>
        <div class="ms-auto">{{ $ventas->links() }}</div>
    </div>
</div>
@endsection
