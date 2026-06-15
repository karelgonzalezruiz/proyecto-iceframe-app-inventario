@extends('layouts.app')
@section('title', 'Registrar venta · IceFrame')
@section('content')
<div class="page-header mb-3">
    <div class="page-pretitle">Transacción simple</div>
    <h2 class="page-title">Registrar venta</h2>
</div>

<div class="row row-cards">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('ventas.store') }}" id="form-compra" data-once>
                    @csrf
                    {{-- Anti-doble-clic (backend): clave única generada al cargar la página. --}}
                    <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Cédula del cliente</label>
                            <input type="text" name="cedula" class="form-control"
                                   pattern="\d{10}" maxlength="10" inputmode="numeric"
                                   title="La cédula debe tener exactamente 10 dígitos numéricos."
                                   value="{{ old('cedula') }}" placeholder="Ej: 0102030405" required>
                            <small class="form-hint">Ingrese únicamente 10 dígitos numéricos.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Nombre del cliente</label>
                            <input type="text" name="nombre" class="form-control" maxlength="120"
                                   value="{{ old('nombre') }}" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label required">Producto</label>
                            <select name="producto_id" id="producto-select" class="form-select select-buscable"
                                    data-placeholder="Seleccione un producto activo…" required>
                                <option value="">Seleccione un producto activo…</option>
                                @foreach ($productos as $prod)
                                    <option value="{{ $prod->id }}"
                                        data-precio="{{ $prod->precio_unitario }}"
                                        data-stock="{{ $prod->stock_actual }}"
                                        @selected(old('producto_id', $productoSel ?? '') == $prod->id)>
                                        {{ $prod->nombre }} (stock: {{ $prod->stock_actual }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Cantidad</label>
                            <input type="number" name="cantidad" id="cantidad-input" class="form-control"
                                   min="1" value="{{ old('cantidad', 1) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Método de pago</label>
                            <select name="metodo_pago" class="form-select" required>
                                @foreach ($metodos as $met)
                                    <option value="{{ $met }}" @selected(old('metodo_pago') === $met)>{{ $met }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-iceframe"><i class="ti ti-cash-register me-1"></i> Registrar venta</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-link">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Resumen de la transacción</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6">Precio unitario</dt><dd class="col-6 text-end" id="r-precio">0.00</dd>
                    <dt class="col-6">Cantidad</dt><dd class="col-6 text-end" id="r-cantidad">0</dd>
                    <dt class="col-6">Stock disponible</dt><dd class="col-6 text-end" id="r-stock">0</dd>
                    <dt class="col-6 fs-3">Total</dt><dd class="col-6 text-end fs-3 fw-bold" id="r-total">0.00</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const sel = document.getElementById('producto-select');
    const cant = document.getElementById('cantidad-input');
    const rPrecio = document.getElementById('r-precio');
    const rCant = document.getElementById('r-cantidad');
    const rStock = document.getElementById('r-stock');
    const rTotal = document.getElementById('r-total');

    function recalcular() {
        const opt = sel.options[sel.selectedIndex];
        const precio = parseFloat(opt?.dataset.precio || 0);
        const stock = parseInt(opt?.dataset.stock || 0);
        const cantidad = parseInt(cant.value || 0);
        rPrecio.textContent = precio.toFixed(2);
        rCant.textContent = isNaN(cantidad) ? 0 : cantidad;
        rStock.textContent = isNaN(stock) ? 0 : stock;
        const total = (precio * (cantidad || 0));
        rTotal.textContent = total.toFixed(2);
        if (!isNaN(stock) && stock > 0) cant.max = stock;
    }
    sel.addEventListener('change', recalcular);
    cant.addEventListener('input', recalcular);
    recalcular();
})();
</script>
@endpush
