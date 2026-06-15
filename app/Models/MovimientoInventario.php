<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: movimientos_inventario
 * (id, producto_id, usuario_id, venta_id, tipo, cantidad, observacion, fecha)
 *
 * tipo CHECK IN ('Venta','Reposicion','Hurto','Ajuste')
 * venta_id es nullable (solo aplica a movimientos tipo Venta).
 * cantidad CHECK > 0 (siempre positiva; el tipo indica si suma o resta stock).
 */
class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';
    public $timestamps = false;

    protected $fillable = [
        'producto_id', 'usuario_id', 'venta_id', 'tipo', 'cantidad', 'observacion', 'fecha',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'fecha'    => 'datetime',
    ];

    public const TIPOS = ['Venta', 'Reposicion', 'Hurto', 'Ajuste'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
