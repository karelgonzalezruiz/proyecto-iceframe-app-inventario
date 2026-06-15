@extends('layouts.app')
@section('title', $producto->nombre . ' · IceFrame')
@section('content')
<div class="page-header mb-3">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle">Producto #{{ $producto->id }}</div>
            <h2 class="page-title">{{ $producto->nombre }}</h2>
        </div>
        <div class="col-auto btn-list">
            <a href="{{ route('productos.edit', $producto) }}" class="btn btn-outline-primary"><i class="ti ti-edit me-1"></i> Editar</a>
            <a href="{{ route('productos.index') }}" class="btn btn-link">Volver al catálogo</a>
        </div>
    </div>
</div>

<div class="row row-cards">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Detalles</h3></div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-5">Categoría</dt><dd class="col-7">{{ optional($producto->categoria)->nombre }}</dd>
                    <dt class="col-5">Marca</dt><dd class="col-7">{{ optional($producto->marca)->nombre }}</dd>
                    <dt class="col-5">Proveedor</dt><dd class="col-7">{{ optional($producto->proveedor)->nombre }}</dd>
                    <dt class="col-5">Condición</dt><dd class="col-7">{{ $producto->condicion }}</dd>
                    <dt class="col-5">Precio unitario</dt><dd class="col-7">{{ number_format($producto->precio_unitario, 2) }}</dd>
                    <dt class="col-5">Stock actual</dt><dd class="col-7">{{ $producto->stock_actual }}</dd>
                    <dt class="col-5">Stock mínimo</dt><dd class="col-7">{{ $producto->stock_minimo }}</dd>
                    <dt class="col-5">Valor total</dt><dd class="col-7">{{ number_format($producto->valor_total, 2) }}</dd>
                    <dt class="col-5">Estado</dt><dd class="col-7">{{ $producto->estado }}</dd>
                </dl>
                @if ($producto->descripcion)
                    <p class="text-secondary mb-0">{{ $producto->descripcion }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Movimientos recientes</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Fecha</th><th>Tipo</th><th class="text-end">Cantidad</th><th>Usuario</th></tr></thead>
                    <tbody>
                        @forelse ($movimientos as $mov)
                            <tr>
                                <td class="text-secondary">{{ optional($mov->fecha)->format('d/m/Y H:i') }}</td>
                                <td>{{ $mov->tipo }}</td>
                                <td class="text-end">{{ $mov->cantidad }}</td>
                                <td>{{ optional($mov->usuario)->nombre ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-3">Sin movimientos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
