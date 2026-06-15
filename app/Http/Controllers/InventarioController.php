<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Reposición de stock (suma) y registro de Hurto (resta, solo Administrador).
 * Ambas operaciones son atómicas y registran movimientos_inventario.
 */
class InventarioController extends Controller
{
    // ---------- Reposición ----------

    public function reposicionForm()
    {
        $productos = Producto::activos()->orderBy('nombre')->get();
        return view('inventario.reposicion', compact('productos'));
    }

    public function reposicion(Request $request)
    {
        $data = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'cantidad'    => ['required', 'integer', 'min:1'],
            'observacion' => ['required', 'string', 'max:255'],
        ], [
            'cantidad.min' => 'La cantidad debe ser mayor que cero.',
            'observacion.required' => 'La observación es obligatoria.',
        ]);

        $usuarioId = $request->user()->id;

        try {
            DB::transaction(function () use ($data, $usuarioId) {
                $producto = Producto::where('id', $data['producto_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $producto || ! $producto->activo) {
                    throw new \RuntimeException('El producto no está disponible.');
                }

                $producto->increment('stock_actual', $data['cantidad']);

                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'usuario_id'  => $usuarioId,
                    'venta_id'    => null,
                    'tipo'        => 'Reposicion',
                    'cantidad'    => $data['cantidad'],
                    'observacion' => trim($data['observacion']),
                    'fecha'       => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['reposicion' => $e->getMessage()]);
        }

        return redirect()
            ->route('inventario.reposicion.form')
            ->with('success', 'Reposición registrada correctamente.');
    }

    // ---------- Hurto (solo Administrador, restringido por middleware en la ruta) ----------

    public function hurtoForm(Request $request)
    {
        $productos = Producto::activos()
            ->where('stock_actual', '>', 0)
            ->orderBy('nombre')
            ->get();
        $productoSel = $request->query('producto');

        return view('inventario.hurto', compact('productos', 'productoSel'));
    }

    public function hurto(Request $request)
    {
        $data = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'cantidad'    => ['required', 'integer', 'min:1'],
            'observacion' => ['required', 'string', 'max:255'],
        ], [
            'cantidad.min' => 'La cantidad debe ser mayor que cero.',
            'observacion.required' => 'La observación es obligatoria.',
        ]);

        $usuarioId = $request->user()->id;

        try {
            DB::transaction(function () use ($data, $usuarioId) {
                $producto = Producto::where('id', $data['producto_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $producto || ! $producto->activo) {
                    throw new \RuntimeException('El producto no está disponible.');
                }

                if ($data['cantidad'] > $producto->stock_actual) {
                    throw new \RuntimeException(
                        "No se puede restar más del stock disponible ({$producto->stock_actual})."
                    );
                }

                $producto->decrement('stock_actual', $data['cantidad']);

                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'usuario_id'  => $usuarioId,
                    'venta_id'    => null,
                    'tipo'        => 'Hurto',
                    'cantidad'    => $data['cantidad'],
                    'observacion' => trim($data['observacion']),
                    'fecha'       => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['hurto' => $e->getMessage()]);
        }

        return redirect()
            ->route('inventario.hurto.form')
            ->with('success', 'Hurto registrado y stock actualizado.');
    }
}
