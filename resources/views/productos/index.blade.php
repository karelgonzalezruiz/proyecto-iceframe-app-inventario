@extends('layouts.app')
@section('title', 'Catálogo de Inventario · IceFrame')

@php
    $esAdmin = auth()->user()->esAdministrador();
    function estadoBadge($estado) {
        return match ($estado) {
            'Disponible'  => 'estado-disponible',
            'Bajo stock'  => 'estado-bajo',
            'Agotado'     => 'estado-agotado',
            'Desactivado' => 'estado-desactivado',
            default       => 'bg-secondary',
        };
    }
@endphp

@section('content')
<div class="page-header mb-3">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle">Inventario interno</div>
            <h2 class="page-title">Catálogo de Inventario</h2>
        </div>
        <div class="col-auto btn-list">
            <a href="{{ route('productos.json', array_merge(request()->query(), ['descargar' => 1])) }}" class="btn btn-outline-secondary">
                <i class="ti ti-download me-1"></i> Descargar JSON
            </a>
            <a href="{{ route('productos.csv', request()->query()) }}" class="btn btn-outline-primary">
                <i class="ti ti-file-download me-1"></i> Descargar CSV
            </a>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('productos.index') }}" class="d-flex flex-wrap flex-lg-nowrap gap-2 align-items-end">
            <div class="flex-fill" style="min-width: 220px;">
                <label class="form-label">Producto</label>
                <select name="producto_id" class="form-select select-buscable" data-placeholder="Todos los productos">
                    <option value="">Todos los productos</option>
                    @foreach ($listaProductos as $prod)
                        <option value="{{ $prod->id }}" @selected(request('producto_id') == $prod->id)>{{ $prod->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <label class="form-label">Categoría</label>
                <select name="categoria_id" class="form-select select-buscable" data-placeholder="Todas">
                    <option value="">Todas</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}" @selected(request('categoria_id') == $cat->id)>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <label class="form-label">Marca</label>
                <select name="marca_id" class="form-select select-buscable" data-placeholder="Todas">
                    <option value="">Todas</option>
                    @foreach ($marcas as $marca)
                        <option value="{{ $marca->id }}" @selected(request('marca_id') == $marca->id)>{{ $marca->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-fill" style="min-width: 140px;">
                <label class="form-label">Estado de stock</label>
                <select name="estado_stock" class="form-select">
                    <option value="">Todos</option>
                    <option value="disponible" @selected(request('estado_stock')=='disponible')>Disponible</option>
                    <option value="bajo" @selected(request('estado_stock')=='bajo')>Bajo stock</option>
                    <option value="agotado" @selected(request('estado_stock')=='agotado')>Agotado</option>
                </select>
            </div>
            <div class="flex-fill" style="min-width: 140px;">
                <label class="form-label">Ordenar por</label>
                <select name="orden" class="form-select">
                    <option value="nombre" @selected(request('orden', 'nombre') === 'nombre')>Nombre</option>
                    <option value="id" @selected(request('orden') === 'id')>ID</option>
                    <option value="marca" @selected(request('orden') === 'marca')>Marca</option>
                    <option value="categoria" @selected(request('orden') === 'categoria')>Categoría</option>
                    <option value="stock" @selected(request('orden') === 'stock')>Stock</option>
                    <option value="precio" @selected(request('orden') === 'precio')>Precio</option>
                    <option value="valor" @selected(request('orden') === 'valor')>Valor total</option>
                </select>
            </div>
            <div class="flex-fill" style="min-width: 130px;">
                <label class="form-label">Dirección</label>
                <select name="direccion" class="form-select">
                    <option value="asc" @selected(request('direccion', 'asc') === 'asc')>Ascendente</option>
                    <option value="desc" @selected(request('direccion') === 'desc')>Descendente</option>
                </select>
            </div>
            @if ($esAdmin)
                <div class="d-flex align-items-center" style="min-width: 150px;">
                    <label class="form-check m-0">
                        <input type="checkbox" name="incluir_inactivos" value="1" class="form-check-input" @checked(request('incluir_inactivos'))>
                        <span class="form-check-label">Incluir desactivados</span>
                    </label>
                </div>
            @endif
            <div>
                <button type="submit" class="btn btn-primary" title="Aplicar filtros y orden">
                    <i class="ti ti-adjustments"></i>
                </button>
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
                    <th>ID</th><th>Nombre</th><th>Categoría</th><th>Marca</th><th>Proveedor</th>
                    <th>Condición</th><th class="text-end">Stock</th><th class="text-end">Mín.</th>
                    <th class="text-end">Precio</th><th class="text-end">Valor total</th>
                    <th>Estado</th><th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productos as $p)
                    <tr>
                        <td class="text-secondary">{{ $p->id }}</td>
                        <td><strong>{{ $p->nombre }}</strong></td>
                        <td>{{ optional($p->categoria)->nombre }}</td>
                        <td>{{ optional($p->marca)->nombre }}</td>
                        <td>{{ optional($p->proveedor)->nombre }}</td>
                        <td>{{ $p->condicion }}</td>
                        <td class="text-end">{{ $p->stock_actual }}</td>
                        <td class="text-end text-secondary">{{ $p->stock_minimo }}</td>
                        <td class="text-end">{{ number_format($p->precio_unitario, 2) }}</td>
                        <td class="text-end">{{ number_format($p->valor_total, 2) }}</td>
                        <td><span class="badge {{ estadoBadge($p->estado) }}">{{ $p->estado }}</span></td>
                        <td class="text-end">
                            <div class="btn-list flex-nowrap justify-content-end">
                                <a href="{{ route('productos.show', $p) }}" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="ti ti-eye"></i></a>
                                <a href="{{ route('productos.edit', $p) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="ti ti-edit"></i></a>
                                @if ($p->activo && $p->stock_actual > 0)
                                    <a href="{{ route('ventas.create', ['producto' => $p->id]) }}" class="btn btn-sm btn-outline-azure" title="Registrar venta"><i class="ti ti-shopping-cart"></i></a>
                                @endif
                                @if ($esAdmin)
                                    @if ($p->activo && $p->stock_actual > 0)
                                        <a href="{{ route('inventario.hurto.form', ['producto' => $p->id]) }}"
                                           class="btn btn-sm btn-outline-danger" title="Reportar hurto">
                                            <i class="ti ti-alert-octagon"></i>
                                        </a>
                                    @endif

                                    @if ($p->activo)
                                        <form method="POST" action="{{ route('productos.destroy', $p) }}" onsubmit="return confirm('¿Desactivar «{{ $p->nombre }}»? Dejará de aparecer en listados pero conservará su historial.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Desactivar"><i class="ti ti-eye-off"></i></button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('productos.reactivar', $p) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-green" title="Reactivar"><i class="ti ti-rotate"></i></button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('productos.eliminar', $p) }}" onsubmit="return confirm('⚠️ ELIMINAR PERMANENTEMENTE «{{ $p->nombre }}».\n\nEsta acción NO se puede deshacer. Si el producto tiene ventas registradas, será rechazada.\n\n¿Continuar?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar permanentemente"><i class="ti ti-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="text-center text-secondary py-4">No se encontraron productos con esos criterios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">Mostrando {{ $productos->count() }} de {{ $productos->total() }} productos</p>
        <div class="ms-auto">{{ $productos->links() }}</div>
    </div>
</div>
@endsection
