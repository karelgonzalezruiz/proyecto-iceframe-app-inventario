<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Historial de Movimientos de inventario: listado paginado con filtros por
 * tipo (Venta/Reposicion/Hurto/Ajuste) y rango de fechas. Solo lectura.
 */
class MovimientoController extends Controller
{
    public function index(Request $request)
    {
        $this->validarFiltros($request);

        $movimientos = $this->consultaMovimientos($request)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('movimientos.index', [
            'movimientos' => $movimientos,
            'tipos'       => MovimientoInventario::TIPOS,
        ]);
    }

    public function exportarCsv(Request $request): StreamedResponse
    {
        $this->validarFiltros($request);

        $movimientos = $this->consultaMovimientos($request)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();
        $nombre = 'movimientos-inventario-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($movimientos) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, [
                'ID', 'Fecha', 'Producto', 'Tipo', 'Cantidad', 'Usuario', 'Venta', 'Observación',
            ], ';', '"', '');

            foreach ($movimientos as $movimiento) {
                fputcsv($salida, [
                    $movimiento->id,
                    optional($movimiento->fecha)->format('Y-m-d H:i:s'),
                    optional($movimiento->producto)->nombre,
                    $movimiento->tipo,
                    $movimiento->cantidad,
                    optional($movimiento->usuario)->nombre,
                    $movimiento->venta_id,
                    $movimiento->observacion,
                ], ';', '"', '');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportarJson(Request $request): JsonResponse
    {
        $this->validarFiltros($request);

        $movimientos = $this->consultaMovimientos($request)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        $respuesta = response()->json([
            'reporte' => 'Movimientos de inventario',
            'generado_en' => now()->toIso8601String(),
            'total_registros' => $movimientos->count(),
            'filtros' => $request->only(['tipo', 'desde', 'hasta']),
            'datos' => $movimientos->map(fn (MovimientoInventario $movimiento) => [
                'id' => $movimiento->id,
                'fecha' => optional($movimiento->fecha)->toIso8601String(),
                'producto' => optional($movimiento->producto)->nombre,
                'tipo' => $movimiento->tipo,
                'cantidad' => $movimiento->cantidad,
                'usuario' => optional($movimiento->usuario)->nombre,
                'venta_id' => $movimiento->venta_id,
                'observacion' => $movimiento->observacion,
            ])->values(),
        ], options: JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($request->boolean('descargar')) {
            $nombre = 'movimientos-' . now()->format('Y-m-d-His') . '.json';
            $respuesta->headers->set('Content-Disposition', 'attachment; filename="' . $nombre . '"');
        }

        return $respuesta;
    }

    private function validarFiltros(Request $request): void
    {
        $request->validate([
            'tipo'  => ['nullable', 'in:' . implode(',', MovimientoInventario::TIPOS)],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
        ]);
    }

    private function consultaMovimientos(Request $request)
    {
        $query = MovimientoInventario::with(['producto', 'usuario']);

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha', '>=', $request->input('desde'));
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha', '<=', $request->input('hasta'));
        }

        return $query;
    }
}
