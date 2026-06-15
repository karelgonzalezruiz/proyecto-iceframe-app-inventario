<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CRUD de productos + Catálogo de Inventario interno.
 *
 * "Eliminar" producto = desactivación lógica (activo = false). Nunca se borra
 * físicamente para no romper el historial de ventas/movimientos.
 * La desactivación está restringida a Administrador por el middleware 'admin'
 * aplicado a la ruta destroy (ver routes/web.php).
 */
class ProductoController extends Controller
{
    /**
     * Catálogo de Inventario: listado con búsqueda y filtros.
     */
    public function index(Request $request)
    {
        $this->validarConsultaCatalogo($request);

        $productos = $this->consultaCatalogo($request)
            ->paginate(15)
            ->withQueryString();

        $categorias = Categoria::orderBy('nombre')->get();
        $marcas     = Marca::orderBy('nombre')->get();

        // Lista para el selector buscable de Producto (muestra todos al abrirlo;
        // Tom Select busca por el texto, que es el nombre).
        $listaProductos = Producto::orderBy('nombre')->get(['id', 'nombre']);

        return view('productos.index', compact('productos', 'categorias', 'marcas', 'listaProductos'));
    }

    public function exportarCsv(Request $request): StreamedResponse
    {
        $this->validarConsultaCatalogo($request);

        $productos = $this->consultaCatalogo($request)->get();
        $nombre = 'catalogo-inventario-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($productos) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, [
                'ID', 'Producto', 'Descripción', 'Categoría', 'Marca', 'Proveedor', 'Condición',
                'Stock actual', 'Stock mínimo', 'Precio unitario', 'Valor total', 'Estado',
            ], ';', '"', '');

            foreach ($productos as $producto) {
                fputcsv($salida, [
                    $producto->id,
                    $producto->nombre,
                    $producto->descripcion,
                    optional($producto->categoria)->nombre,
                    optional($producto->marca)->nombre,
                    optional($producto->proveedor)->nombre,
                    $producto->condicion,
                    $producto->stock_actual,
                    $producto->stock_minimo,
                    number_format((float) $producto->precio_unitario, 2, '.', ''),
                    number_format($producto->valor_total, 2, '.', ''),
                    $producto->estado,
                ], ';', '"', '');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportarJson(Request $request): JsonResponse
    {
        $this->validarConsultaCatalogo($request);

        $productos = $this->consultaCatalogo($request)->get();

        $respuesta = response()->json([
            'reporte' => 'Catálogo de inventario',
            'generado_en' => now()->toIso8601String(),
            'total_registros' => $productos->count(),
            'filtros' => $request->only([
                'producto_id', 'categoria_id', 'marca_id', 'estado_stock',
                'incluir_inactivos', 'orden', 'direccion',
            ]),
            'datos' => $productos->map(fn (Producto $producto) => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'categoria' => optional($producto->categoria)->nombre,
                'marca' => optional($producto->marca)->nombre,
                'proveedor' => optional($producto->proveedor)->nombre,
                'condicion' => $producto->condicion,
                'stock_actual' => $producto->stock_actual,
                'stock_minimo' => $producto->stock_minimo,
                'precio_unitario' => (float) $producto->precio_unitario,
                'valor_total' => $producto->valor_total,
                'estado' => $producto->estado,
            ])->values(),
        ], options: JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $this->quizasDescargarJson($request, $respuesta, 'catalogo-inventario');
    }

    /**
     * Si la petición trae ?descargar=1, marca la respuesta JSON como adjunto
     * para que el navegador la descargue como archivo .json en lugar de mostrarla.
     */
    private function quizasDescargarJson(Request $request, JsonResponse $respuesta, string $base): JsonResponse
    {
        if ($request->boolean('descargar')) {
            $nombre = $base . '-' . now()->format('Y-m-d-His') . '.json';
            $respuesta->headers->set('Content-Disposition', 'attachment; filename="' . $nombre . '"');
        }

        return $respuesta;
    }

    public function create()
    {
        return view('productos.create', $this->datosFormulario());
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['activo'] = true;

        $producto = Producto::create($data);

        return redirect()
            ->route('productos.show', $producto)
            ->with('success', 'Producto registrado correctamente.');
    }

    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'marca', 'proveedor']);

        $movimientos = $producto->movimientos()
            ->with('usuario')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return view('productos.show', compact('producto', 'movimientos'));
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', array_merge(
            ['producto' => $producto],
            $this->datosFormulario()
        ));
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $this->validar($request);

        $producto->update($data);

        return redirect()
            ->route('productos.show', $producto)
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * "Eliminar" = desactivación lógica. Solo Administrador (middleware en la ruta).
     */
    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => false]);

        return redirect()
            ->route('productos.index')
            ->with('success', "Producto «{$producto->nombre}» desactivado (activo = false).");
    }

    /**
     * Reactivar un producto desactivado (utilidad administrativa).
     * Solo Administrador (middleware en la ruta).
     */
    public function reactivar(Producto $producto)
    {
        $producto->update(['activo' => true]);

        return redirect()
            ->back()
            ->with('success', "Producto «{$producto->nombre}» reactivado.");
    }

    /**
     * Borrado FÍSICO permanente. Solo Administrador (middleware en la ruta).
     *
     * Si el producto tiene ventas registradas se aborta: un producto vendido
     * solo puede desactivarse para no romper el historial. Si no tiene ventas,
     * se borran primero sus movimientos de inventario (reposiciones/ajustes/
     * hurtos) y luego el producto, todo dentro de una transacción.
     */
    public function eliminar(Producto $producto)
    {
        if ($producto->tieneVentas()) {
            return redirect()
                ->route('productos.index')
                ->withErrors([
                    'eliminar' => "No se puede eliminar «{$producto->nombre}» porque tiene ventas registradas. Use la opción «Desactivar» en su lugar.",
                ]);
        }

        $nombre = $producto->nombre;

        DB::transaction(function () use ($producto) {
            $producto->movimientos()->delete();
            $producto->delete();
        });

        return redirect()
            ->route('productos.index')
            ->with('success', "Producto «{$nombre}» eliminado permanentemente.");
    }

    // ----- Catálogos al vuelo (JSON, usados por los modales «+ Nueva») -----

    public function storeMarca(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
        ]);

        $marca = Marca::create(['nombre' => trim($data['nombre'])]);

        return response()->json(['id' => $marca->id, 'nombre' => $marca->nombre], 201);
    }

    public function storeCategoria(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:120'],
            'descripcion' => ['required', 'string', 'max:255'],
        ]);

        $categoria = Categoria::create([
            'nombre'      => trim($data['nombre']),
            'descripcion' => trim($data['descripcion']),
        ]);

        return response()->json(['id' => $categoria->id, 'nombre' => $categoria->nombre], 201);
    }

    public function storeProveedor(Request $request)
    {
        $data = $request->validate([
            'nombre'   => ['required', 'string', 'max:120'],
            'telefono' => ['required', 'string', 'max:30'],
            'email'    => ['required', 'email', 'max:120'],
        ]);

        $proveedor = Proveedor::create([
            'nombre'   => trim($data['nombre']),
            'telefono' => trim($data['telefono']),
            'email'    => trim($data['email']),
        ]);

        return response()->json(['id' => $proveedor->id, 'nombre' => $proveedor->nombre], 201);
    }

    // ----- Helpers -----

    private function datosFormulario(): array
    {
        return [
            'categorias'  => Categoria::orderBy('nombre')->get(),
            'marcas'      => Marca::orderBy('nombre')->get(),
            'proveedores' => Proveedor::orderBy('nombre')->get(),
            'condiciones' => Producto::CONDICIONES,
        ];
    }

    private function consultaCatalogo(Request $request)
    {
        $query = Producto::with(['categoria', 'marca', 'proveedor']);
        $incluirInactivos = $request->boolean('incluir_inactivos')
            && optional($request->user())->esAdministrador();

        if (! $incluirInactivos) {
            $query->where('activo', true);
        }

        if ($request->filled('producto_id')) {
            $query->where('id', (int) $request->input('producto_id'));
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', (int) $request->input('categoria_id'));
        }

        if ($request->filled('marca_id')) {
            $query->where('marca_id', (int) $request->input('marca_id'));
        }

        match ($request->input('estado_stock')) {
            'bajo' => $query->whereColumn('stock_actual', '<=', 'stock_minimo')
                ->where('stock_actual', '>', 0),
            'agotado' => $query->where('stock_actual', '<=', 0),
            'disponible' => $query->whereColumn('stock_actual', '>', 'stock_minimo'),
            default => null,
        };

        $direccion = $request->input('direccion', 'asc');

        match ($request->input('orden', 'nombre')) {
            'id' => $query->orderBy('productos.id', $direccion),
            'marca' => $query->orderBy(
                Marca::select('nombre')->whereColumn('marcas.id', 'productos.marca_id'),
                $direccion
            ),
            'categoria' => $query->orderBy(
                Categoria::select('nombre')->whereColumn('categorias.id', 'productos.categoria_id'),
                $direccion
            ),
            'stock' => $query->orderBy('stock_actual', $direccion),
            'precio' => $query->orderBy('precio_unitario', $direccion),
            'valor' => $query->orderByRaw(
                'stock_actual * precio_unitario ' . ($direccion === 'desc' ? 'DESC' : 'ASC')
            ),
            default => $query->orderBy('productos.nombre', $direccion),
        };

        $query->orderBy('productos.id');

        return $query;
    }

    private function validarConsultaCatalogo(Request $request): void
    {
        $request->validate([
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'marca_id' => ['nullable', 'integer', 'exists:marcas,id'],
            'estado_stock' => ['nullable', 'in:disponible,bajo,agotado'],
            'incluir_inactivos' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'in:id,nombre,marca,categoria,stock,precio,valor'],
            'direccion' => ['nullable', 'in:asc,desc'],
        ]);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'categoria_id'    => ['required', 'integer', 'exists:categorias,id'],
            'marca_id'        => ['required', 'integer', 'exists:marcas,id'],
            'proveedor_id'    => ['required', 'integer', 'exists:proveedores,id'],
            'nombre'          => ['required', 'string', 'max:200'],
            'descripcion'     => ['required', 'string', 'max:2000'],
            'condicion'       => ['required', 'in:' . implode(',', Producto::CONDICIONES)],
            'precio_unitario' => ['required', 'numeric', 'min:0'],
            'stock_actual'    => ['required', 'integer', 'min:0'],
            'stock_minimo'    => ['required', 'integer', 'min:0'],
        ], [
            'categoria_id.required' => 'La categoría es obligatoria.',
            'marca_id.required'     => 'La marca es obligatoria.',
            'proveedor_id.required' => 'El proveedor es obligatorio.',
            'nombre.required'       => 'El nombre es obligatorio.',
            'descripcion.required'  => 'La descripción es obligatoria.',
            'condicion.in'          => 'La condición seleccionada no es válida.',
            'precio_unitario.min'   => 'El precio unitario no puede ser negativo.',
            'stock_actual.min'      => 'El stock actual no puede ser negativo.',
            'stock_minimo.min'      => 'El stock mínimo no puede ser negativo.',
        ]);
    }
}
