@extends('layouts.app')
@section('title', 'Registrar hurto · IceFrame')
@section('content')
<div class="page-header mb-3">
    <div class="page-pretitle">Inventario · Solo administrador</div>
    <h2 class="page-title">Registrar hurto</h2>
</div>
<div class="card border-danger">
    <div class="card-body">
        <p class="text-secondary">
            Registra una pérdida o hurto. Resta del stock y queda como movimiento tipo <strong>Hurto</strong>.
        </p>
        <form method="POST" action="{{ route('inventario.hurto') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label required">Producto</label>
                    <select name="producto_id" class="form-select select-buscable"
                            data-placeholder="Seleccione un producto con stock…" required>
                        <option value="">Seleccione un producto con stock…</option>
                        @foreach ($productos as $prod)
                            <option value="{{ $prod->id }}" @selected(old('producto_id', $productoSel ?? '') == $prod->id)>
                                {{ $prod->nombre }} (stock: {{ $prod->stock_actual }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Cantidad hurtada</label>
                    <input type="number" name="cantidad" min="1" class="form-control" value="{{ old('cantidad', 1) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label required">Observación</label>
                    <input type="text" name="observacion" class="form-control" maxlength="255"
                           value="{{ old('observacion') }}" placeholder="Ej: faltante detectado en conteo físico" required>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-danger"><i class="ti ti-alert-octagon me-1"></i> Registrar hurto</button>
                <a href="{{ route('dashboard') }}" class="btn btn-link">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
