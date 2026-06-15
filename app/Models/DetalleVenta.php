<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: detalle_venta (id, venta_id, producto_id, cantidad, precio_unitario, subtotal)
 * precio_unitario es el precio histórico al momento de vender.
 */
class DetalleVenta extends Model
{
    protected $table = 'detalle_venta';
    public $timestamps = false;

    protected $fillable = [
        'venta_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal',
    ];

    protected $casts = [
        'cantidad'        => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
