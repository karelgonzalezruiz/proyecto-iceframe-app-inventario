<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Venta simple ("Registrar venta" en la UI; en BD se usa ventas/detalle_venta).
 *
 * Toda la operación es atómica: cliente + venta + detalle_venta + descuento de
 * stock + movimiento_inventario tipo Venta. Si algo falla, se revierte todo.
 *
 * NO implementa caja, arqueo, contabilidad, factura ni pasarela de pago.
 */
class VentaController extends Controller
{
    public function create(Request $request)
    {
        $productos = Producto::activos()
            ->where('stock_actual', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('ventas.create', [
            'productos'  => $productos,
            'metodos'    => Venta::METODOS_PAGO,
            'productoSel' => $request->integer('producto'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cedula'          => ['required', 'digits:10'],
            'nombre'          => ['required', 'string', 'max:120'],
            'producto_id'     => ['required', 'integer', 'exists:productos,id'],
            'cantidad'        => ['required', 'integer', 'min:1'],
            'metodo_pago'     => ['required', 'in:' . implode(',', Venta::METODOS_PAGO)],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ], [
            'cedula.required'      => 'La cédula del cliente es obligatoria.',
            'cedula.digits'        => 'La cédula debe tener exactamente 10 dígitos numéricos.',
            'nombre.required'      => 'El nombre del cliente es obligatorio.',
            'producto_id.required' => 'Debe seleccionar un producto.',
            'cantidad.min'         => 'La cantidad debe ser mayor que cero.',
            'metodo_pago.in'       => 'El método de pago no es válido.',
        ]);

        // Anti-doble-clic (backend): si llega una segunda petición con la misma
        // idempotency_key en una ventana corta, se rechaza para no crear dos
        // ventas. Cache::add() es atómico: solo el primer request "gana" la key.
        if (! empty($data['idempotency_key'])) {
            $lockKey = 'venta:idem:' . $data['idempotency_key'];
            if (! Cache::add($lockKey, true, now()->addSeconds(30))) {
                return back()->withInput()->withErrors([
                    'venta' => 'Esta venta ya se está procesando. Espere un momento.',
                ]);
            }
        }

        $usuarioId = $request->user()->id;

        try {
            $venta = DB::transaction(function () use ($data, $usuarioId) {
                // 1-4. Buscar o crear cliente por cédula y nombre obligatorio.
                $cliente = Cliente::firstOrCreate(
                    ['cedula' => trim($data['cedula'])],
                    ['nombre' => trim($data['nombre'])]
                );

                // Completar registros antiguos que todavía no tengan nombre.
                if (! empty($data['nombre']) && empty($cliente->nombre)) {
                    $cliente->update(['nombre' => $data['nombre']]);
                }

                // 5. Bloquear el producto activo para evitar condiciones de carrera.
                $producto = Producto::where('id', $data['producto_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $producto || ! $producto->activo) {
                    throw new \RuntimeException('El producto no está disponible para la venta.');
                }

                // 9. Validar stock disponible dentro de la transacción.
                if ($data['cantidad'] > $producto->stock_actual) {
                    throw new \RuntimeException(
                        "Stock insuficiente. Disponible: {$producto->stock_actual}."
                    );
                }

                // 10. Calcular subtotal y total (precio histórico del producto).
                $precio   = (float) $producto->precio_unitario;
                $subtotal = $precio * (int) $data['cantidad'];
                $total    = $subtotal; // compra de un solo producto

                // 11. Crear venta Completada.
                $venta = Venta::create([
                    'cliente_id'  => $cliente->id,
                    'usuario_id'  => $usuarioId,
                    'fecha'       => now(),
                    'metodo_pago' => $data['metodo_pago'],
                    'estado'      => 'Completada',
                    'total'       => $total,
                ]);

                // 12. Crear detalle_venta.
                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $producto->id,
                    'cantidad'        => $data['cantidad'],
                    'precio_unitario' => $precio,
                    'subtotal'        => $subtotal,
                ]);

                // 13. Descontar stock.
                $producto->decrement('stock_actual', $data['cantidad']);

                // 14. Movimiento de inventario tipo Venta (cantidad siempre positiva).
                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'usuario_id'  => $usuarioId,
                    'venta_id'    => $venta->id,
                    'tipo'        => 'Venta',
                    'cantidad'    => $data['cantidad'],
                    'observacion' => 'Venta registrada desde Laravel',
                    'fecha'       => now(),
                ]);

                return $venta;
            });
        } catch (\RuntimeException $e) {
            // La venta no se creó: liberar la key para permitir reintentar.
            if (! empty($data['idempotency_key'])) {
                Cache::forget('venta:idem:' . $data['idempotency_key']);
            }
            return back()->withInput()->withErrors(['venta' => $e->getMessage()]);
        }

        // 15. Mostrar el valor de la transacción.
        return redirect()
            ->route('ventas.create')
            ->with('success', 'Venta registrada. Venta #' . $venta->id
                . ' — Total: ' . number_format((float) $venta->total, 2));
    }

    /**
     * Resumen de ingresos por ventas. NO es caja ni contabilidad.
     */
    public function resumen(Request $request)
    {
        $periodo   = $this->periodoSeleccionado($request);
        $direccion = $this->direccionSeleccionada($request);

        $totalPeriodo = (float) $this->aplicarPeriodo(Venta::completadas(), $periodo)
            ->sum('total');

        $porMetodo = $this->aplicarPeriodo(Venta::completadas(), $periodo)
            ->select('metodo_pago', DB::raw('SUM(total) AS total'), DB::raw('COUNT(*) AS cantidad'))
            ->groupBy('metodo_pago')
            ->get();

        $ventas = $this->consultaVentasPeriodo($periodo, $direccion)
            ->paginate(15)
            ->withQueryString();

        return view('ventas.resumen', [
            'periodo'         => $periodo,
            'direccion'       => $direccion,
            'etiquetaPeriodo' => $this->etiquetaPeriodo($periodo),
            'totalPeriodo'    => $totalPeriodo,
            'porMetodo'       => $porMetodo,
            'ventas'          => $ventas,
        ]);
    }

    public function exportarResumenCsv(Request $request): StreamedResponse
    {
        $periodo   = $this->periodoSeleccionado($request);
        $direccion = $this->direccionSeleccionada($request);

        $ventas = $this->consultaVentasPeriodo($periodo, $direccion)
            ->with('detalles.producto')
            ->get();
        $nombre = 'resumen-ventas-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($ventas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, [
                'Venta', 'Fecha', 'Cédula', 'Cliente', 'Vendedor', 'Método',
                'Producto', 'Cantidad', 'Precio unitario', 'Subtotal', 'Total',
            ], ';', '"', '');

            foreach ($ventas as $venta) {
                foreach ($venta->detalles as $detalle) {
                    fputcsv($salida, [
                        $venta->id,
                        optional($venta->fecha)->format('Y-m-d H:i:s'),
                        optional($venta->cliente)->cedula,
                        optional($venta->cliente)->nombre,
                        optional($venta->usuario)->nombre,
                        $venta->metodo_pago,
                        optional($detalle->producto)->nombre,
                        $detalle->cantidad,
                        number_format((float) $detalle->precio_unitario, 2, '.', ''),
                        number_format((float) $detalle->subtotal, 2, '.', ''),
                        number_format((float) $venta->total, 2, '.', ''),
                    ], ';', '"', '');
                }
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportarResumenJson(Request $request): JsonResponse
    {
        $periodo   = $this->periodoSeleccionado($request);
        $direccion = $this->direccionSeleccionada($request);

        $ventas = $this->consultaVentasPeriodo($periodo, $direccion)
            ->with('detalles.producto')
            ->get();

        $respuesta = response()->json([
            'reporte' => 'Resumen de ventas completadas',
            'generado_en' => now()->toIso8601String(),
            'periodo' => $periodo,
            'orden_fecha' => $direccion,
            'total_ventas' => $ventas->count(),
            'total_vendido' => (float) $ventas->sum('total'),
            'datos' => $ventas->map(fn (Venta $venta) => [
                'id' => $venta->id,
                'fecha' => optional($venta->fecha)->toIso8601String(),
                'cliente' => [
                    'cedula' => optional($venta->cliente)->cedula,
                    'nombre' => optional($venta->cliente)->nombre,
                ],
                'vendedor' => optional($venta->usuario)->nombre,
                'metodo_pago' => $venta->metodo_pago,
                'estado' => $venta->estado,
                'total' => (float) $venta->total,
                'productos' => $venta->detalles->map(fn (DetalleVenta $detalle) => [
                    'nombre' => optional($detalle->producto)->nombre,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => (float) $detalle->precio_unitario,
                    'subtotal' => (float) $detalle->subtotal,
                ])->values(),
            ])->values(),
        ], options: JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($request->boolean('descargar')) {
            $nombre = 'resumen-ventas-' . now()->format('Y-m-d-His') . '.json';
            $respuesta->headers->set('Content-Disposition', 'attachment; filename="' . $nombre . '"');
        }

        return $respuesta;
    }

    /**
     * Periodos válidos del resumen: día, semana, mes, año o total.
     */
    private const PERIODOS = ['dia', 'semana', 'mes', 'anio', 'total'];

    private function periodoSeleccionado(Request $request): string
    {
        $request->validate([
            'periodo' => ['nullable', 'in:' . implode(',', self::PERIODOS)],
        ]);

        return $request->input('periodo', 'dia');
    }

    /**
     * Dirección de orden por fecha: 'desc' (más recientes primero, por defecto)
     * o 'asc' (más antiguas primero).
     */
    private function direccionSeleccionada(Request $request): string
    {
        $request->validate([
            'direccion' => ['nullable', 'in:asc,desc'],
        ]);

        return $request->input('direccion', 'desc');
    }

    /**
     * Acota una consulta de ventas al periodo seleccionado (sin ordenarla).
     * 'total' no aplica ningún filtro de fecha.
     */
    private function aplicarPeriodo($query, string $periodo)
    {
        return match ($periodo) {
            'dia'    => $query->whereDate('fecha', Carbon::today()),
            'semana' => $query->whereBetween('fecha', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
            'mes'    => $query->whereBetween('fecha', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]),
            'anio'   => $query->whereBetween('fecha', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]),
            default  => $query, // total
        };
    }

    /**
     * Consulta de la tabla de ventas del periodo, ordenada por fecha en la
     * dirección indicada ('desc' = más recientes primero, 'asc' = más antiguas).
     */
    private function consultaVentasPeriodo(string $periodo, string $direccion = 'desc')
    {
        $direccion = $direccion === 'asc' ? 'asc' : 'desc';

        return $this->aplicarPeriodo(
            Venta::with(['cliente', 'usuario'])->completadas(),
            $periodo
        )
            ->orderBy('fecha', $direccion)
            ->orderBy('ventas.id', $direccion);
    }

    private function etiquetaPeriodo(string $periodo): string
    {
        return match ($periodo) {
            'dia'    => 'Ventas del día',
            'semana' => 'Ventas de la semana',
            'mes'    => 'Ventas del mes',
            'anio'   => 'Ventas del año',
            default  => 'Total vendido',
        };
    }
}
