<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Services\GraficosService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(GraficosService $graficos)
    {
        $totalProductosActivos = Producto::activos()->count();

        $productosBajoStock = Producto::activos()
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->count();

        // Valor total del inventario (solo productos activos).
        $valorInventario = (float) Producto::activos()
            ->select(DB::raw('COALESCE(SUM(precio_unitario * stock_actual), 0) AS valor'))
            ->value('valor');

        $totalVendido = (float) Venta::completadas()->sum('total');

        $ventasDelDia = (float) Venta::completadas()
            ->whereDate('fecha', Carbon::today())
            ->sum('total');

        // Métricas extra para dar "vida" a las tarjetas (tendencias y conteos).
        $ventasAyer = (float) Venta::completadas()
            ->whereDate('fecha', Carbon::yesterday())
            ->sum('total');

        // Variación % de las ventas de hoy respecto a ayer.
        $tendenciaVentas = $ventasAyer > 0
            ? round((($ventasDelDia - $ventasAyer) / $ventasAyer) * 100)
            : ($ventasDelDia > 0 ? 100 : 0);

        $ventasHoyCount = Venta::completadas()
            ->whereDate('fecha', Carbon::today())
            ->count();

        $productosAgotados = Producto::activos()
            ->where('stock_actual', '<=', 0)
            ->count();

        // % de salud del stock (productos sin alerta sobre el total activo).
        $saludStock = $totalProductosActivos > 0
            ? round((($totalProductosActivos - $productosBajoStock) / $totalProductosActivos) * 100)
            : 100;

        // Datasets para los cuatro gráficos del dashboard.
        $gVentas      = $graficos->ventasPorDia();
        $gTop         = $graficos->topProductos();
        $gMovimientos = $graficos->movimientosPorTipo();
        $gCategorias  = $graficos->valorPorCategoria();

        return view('dashboard', compact(
            'totalProductosActivos',
            'productosBajoStock',
            'valorInventario',
            'totalVendido',
            'ventasDelDia',
            'ventasAyer',
            'tendenciaVentas',
            'ventasHoyCount',
            'productosAgotados',
            'saludStock',
            'gVentas',
            'gTop',
            'gMovimientos',
            'gCategorias'
        ));
    }
}
