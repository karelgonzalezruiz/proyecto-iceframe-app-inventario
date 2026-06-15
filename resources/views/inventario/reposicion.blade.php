@extends('layouts.app')
@section('title', 'Reposición de stock · IceFrame')
@section('content')
<div class="page-header mb-3">
    <div class="page-pretitle">Inventario</div>
    <h2 class="page-title">Reposición de stock</h2>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('inventario.reposicion') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label required">Producto</label>
                    <select name="producto_id" class="form-select select-buscable"
                            data-placeholder="Seleccione un producto activo…" required>
                        <option value="">Seleccione un producto activo…</option>
                        @foreach ($productos as $prod)
                            <option value="{{ $prod->id }}" @selected(old('producto_id') == $prod->id)>
                                {{ $prod->nombre }} (stock: {{ $prod->stock_actual }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Cantidad a reponer</label>
                    <input type="number" name="cantidad" min="1" class="form-control" value="{{ old('cantidad', 1) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label required">Observación</label>
                    <input type="text" name="observacion" class="form-control" maxlength="255"
                           value="{{ old('observacion') }}" placeholder="Ej: llegada de pedido del proveedor" required>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-iceframe"><i class="ti ti-arrow-up-circle me-1"></i> Registrar reposición</button>
                <a href="{{ route('dashboard') }}" class="btn btn-link">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
