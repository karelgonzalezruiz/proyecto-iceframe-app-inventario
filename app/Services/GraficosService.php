<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Construye los datasets de los 4 gráficos del Dashboard.
 *
 * Todas las consultas son de SOLO LECTURA y no alteran el esquema.
 */
class GraficosService
{
    /**
     * Gráfico 1 — Ventas por día (últimos 14 días, solo completadas).
     * Rellena con 0 los días sin ventas para que el área sea continua.
     *
     * @return array{labels: string[], valores: float[]}
     */
    public function ventasPorDia(int $dias = 14): array
    {
        $desde = Carbon::today()->subDays($dias - 1);

        $filas = Venta::completadas()
            ->where('fecha', '>=', $desde)
            ->select(DB::raw('DATE(fecha) AS dia'), DB::raw('SUM(total) AS total'))
            ->groupBy(DB::raw('DATE(fecha)'))
            ->pluck('total', 'dia');

        $labels = [];
        $valores = [];

        for ($i = 0; $i < $dias; $i++) {
            $fecha = $desde->copy()->addDays($i);
            $clave = $fecha->toDateString();
            $labels[] = $fecha->format('d/m');
            $valores[] = (float) ($filas[$clave] ?? 0);
        }

        return ['labels' => $labels, 'valores' => $valores];
    }

    /**
     * Gráfico 2 — Top 5 productos más vendidos (unidades), solo ventas completadas.
     *
     * @return array{labels: string[], valores: int[]}
     */
    public function topProductos(int $limite = 5): array
    {
        $filas = DB::table('detalle_venta as d')
            ->join('ventas as v', 'v.id', '=', 'd.venta_id')
            ->join('productos as p', 'p.id', '=', 'd.producto_id')
            ->where('v.estado', 'Completada')
            ->select('p.nombre', DB::raw('SUM(d.cantidad) AS unidades'))
            ->groupBy('p.nombre')
            ->orderByDesc('unidades')
            ->limit($limite)
            ->get();

        return [
            'labels'  => $filas->pluck('nombre')->all(),
            'valores' => $filas->pluck('unidades')->map(fn ($v) => (int) $v)->all(),
        ];
    }

    /**
     * Gráfico 3 — Movimientos por tipo (conteo). Orden fijo Venta/Reposicion/Hurto/Ajuste.
     *
     * @return array{labels: string[], valores: int[]}
     */
    public function movimientosPorTipo(): array
    {
        $conteos = MovimientoInventario::select('tipo', DB::raw('COUNT(*) AS total'))
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $labels = MovimientoInventario::TIPOS;
        $valores = array_map(fn ($tipo) => (int) ($conteos[$tipo] ?? 0), $labels);

        return ['labels' => $labels, 'valores' => $valores];
    }

    /**
     * Gráfico 4 — Valor de inventario por categoría (solo productos activos).
     *
     * @return array{labels: string[], valores: float[]}
     */
    public function valorPorCategoria(): array
    {
        $filas = Producto::query()
            ->join('categorias as c', 'c.id', '=', 'productos.categoria_id')
            ->where('productos.activo', true)
            ->select('c.nombre', DB::raw('SUM(productos.precio_unitario * productos.stock_actual) AS valor'))
            ->groupBy('c.nombre')
            ->orderByDesc('valor')
            ->get();

        return [
            'labels'  => $filas->pluck('nombre')->all(),
            'valores' => $filas->pluck('valor')->map(fn ($v) => (float) $v)->all(),
        ];
    }

}
