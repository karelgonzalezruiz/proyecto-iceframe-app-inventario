@php $p = $producto ?? null; @endphp
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label required">Nombre</label>
        <input type="text" name="nombre" class="form-control" maxlength="200"
               value="{{ old('nombre', $p->nombre ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label required">Condición</label>
        <select name="condicion" class="form-select" required>
            @foreach ($condiciones as $c)
                <option value="{{ $c }}" @selected(old('condicion', $p->condicion ?? '') === $c)>{{ $c }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label required">Categoría</label>
        <div class="iceframe-select-group d-flex gap-2">
            <select name="categoria_id" id="select-categoria" class="form-select select-buscable"
                    data-placeholder="Seleccione una categoría…" required>
                <option value="">Seleccione…</option>
                @foreach ($categorias as $cat)
                    <option value="{{ $cat->id }}" @selected(old('categoria_id', $p->categoria_id ?? '') == $cat->id)>{{ $cat->nombre }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-primary flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modal-categoria" title="Nueva categoría">
                <i class="ti ti-plus"></i>
            </button>
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label required">Marca</label>
        <div class="iceframe-select-group d-flex gap-2">
            <select name="marca_id" id="select-marca" class="form-select select-buscable"
                    data-placeholder="Seleccione una marca…" required>
                <option value="">Seleccione…</option>
                @foreach ($marcas as $m)
                    <option value="{{ $m->id }}" @selected(old('marca_id', $p->marca_id ?? '') == $m->id)>{{ $m->nombre }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-primary flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modal-marca" title="Nueva marca">
                <i class="ti ti-plus"></i>
            </button>
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label required">Proveedor</label>
        <div class="iceframe-select-group d-flex gap-2">
            <select name="proveedor_id" id="select-proveedor" class="form-select select-buscable"
                    data-placeholder="Seleccione un proveedor…" required>
                <option value="">Seleccione…</option>
                @foreach ($proveedores as $pr)
                    <option value="{{ $pr->id }}" @selected(old('proveedor_id', $p->proveedor_id ?? '') == $pr->id)>{{ $pr->nombre }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-primary flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modal-proveedor" title="Nuevo proveedor">
                <i class="ti ti-plus"></i>
            </button>
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label required">Precio unitario</label>
        <input type="number" step="0.01" min="0" name="precio_unitario" class="form-control"
               value="{{ old('precio_unitario', $p->precio_unitario ?? '0.00') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label required">Stock actual</label>
        <input type="number" min="0" name="stock_actual" class="form-control"
               value="{{ old('stock_actual', $p->stock_actual ?? 0) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label required">Stock mínimo</label>
        <input type="number" min="0" name="stock_minimo" class="form-control"
               value="{{ old('stock_minimo', $p->stock_minimo ?? 5) }}" required>
    </div>

    <div class="col-12">
        <label class="form-label required">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3" maxlength="2000" required>{{ old('descripcion', $p->descripcion ?? '') }}</textarea>
    </div>
</div>

{{-- ============ Modales «+ Nueva» (crean catálogos al vuelo vía fetch JSON) ============ --}}

{{-- Categoría --}}
<div class="modal modal-blur fade" id="modal-categoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="iceframe-modal-error alert alert-danger d-none mb-3"></div>
                <div class="mb-3">
                    <label class="form-label required">Nombre</label>
                    <input type="text" class="form-control" data-field="nombre" data-required maxlength="120">
                </div>
                <div class="mb-1">
                    <label class="form-label required">Descripción</label>
                    <input type="text" class="form-control" data-field="descripcion" data-required maxlength="255">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-iceframe iceframe-catalogo-guardar"
                        data-url="{{ route('catalogos.categoria') }}" data-target="#select-categoria">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Marca --}}
<div class="modal modal-blur fade" id="modal-marca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva marca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="iceframe-modal-error alert alert-danger d-none mb-3"></div>
                <div class="mb-1">
                    <label class="form-label required">Nombre</label>
                    <input type="text" class="form-control" data-field="nombre" data-required maxlength="120">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-iceframe iceframe-catalogo-guardar"
                        data-url="{{ route('catalogos.marca') }}" data-target="#select-marca">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Proveedor --}}
<div class="modal modal-blur fade" id="modal-proveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="iceframe-modal-error alert alert-danger d-none mb-3"></div>
                <div class="mb-3">
                    <label class="form-label required">Nombre</label>
                    <input type="text" class="form-control" data-field="nombre" data-required maxlength="120">
                </div>
                <div class="mb-3">
                    <label class="form-label required">Teléfono</label>
                    <input type="text" class="form-control" data-field="telefono" data-required maxlength="30">
                </div>
                <div class="mb-1">
                    <label class="form-label required">Correo electrónico</label>
                    <input type="email" class="form-control" data-field="email" data-required maxlength="120">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-iceframe iceframe-catalogo-guardar"
                        data-url="{{ route('catalogos.proveedor') }}" data-target="#select-proveedor">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>
