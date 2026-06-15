@extends('layouts.app')
@section('title', 'Movimientos · IceFrame')

@php
    $mapTipo = ['Venta' => 'bg-azure', 'Reposicion' => 'bg-green', 'Hurto' => 'bg-red', 'Ajuste' => 'bg-yellow'];
@endphp

@section('content')
<div class="page-header mb-3">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle">Inventario interno</div>
            <h2 class="page-title">Historial de Movimientos</h2>
        </div>
        <div class="col-auto btn-list">
            <a href="{{ route('movimientos.json', array_merge(request()->query(), ['descargar' => 1])) }}" class="btn btn-outline-secondary">
                <i class="ti ti-download me-1"></i> Descargar JSON
            </a>
            <a href="{{ route('movimientos.csv', request()->query()) }}" class="btn btn-outline-primary">
                <i class="ti ti-file-download me-1"></i> Descargar CSV
            </a>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('movimientos.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select select-buscable">
                    <option value="">Todos</option>
                    @foreach ($tipos as $t)
                        <option value="{{ $t }}" @selected(request('tipo') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}" class="form-control">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filtrar</button>
                <a href="{{ route('movimientos.index') }}" class="btn btn-link">Limpiar</a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Fecha</th><th>Producto</th><th>Tipo</th>
                    <th class="text-end">Cantidad</th><th>Usuario</th><th>Venta #</th><th>Observación</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movimientos as $mov)
                    <tr>
                        <td class="text-secondary">{{ optional($mov->fecha)->format('d/m/Y H:i') }}</td>
                        <td>{{ optional($mov->producto)->nombre ?? '—' }}</td>
                        <td><span class="badge {{ $mapTipo[$mov->tipo] ?? 'bg-secondary' }} text-white">{{ $mov->tipo }}</span></td>
                        <td class="text-end">{{ $mov->cantidad }}</td>
                        <td>{{ optional($mov->usuario)->nombre ?? '—' }}</td>
                        <td class="text-secondary">{{ $mov->venta_id ? '#' . $mov->venta_id : '—' }}</td>
                        <td class="text-secondary">{{ $mov->observacion }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-secondary py-4">No hay movimientos para esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">Mostrando {{ $movimientos->count() }} de {{ $movimientos->total() }} movimientos</p>
        <div class="ms-auto">{{ $movimientos->links() }}</div>
    </div>
</div>
@endsection
