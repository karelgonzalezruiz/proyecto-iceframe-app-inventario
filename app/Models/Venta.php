<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: ventas (id, cliente_id, usuario_id, fecha, metodo_pago, estado, total)
 *
 * metodo_pago CHECK IN ('Efectivo','Tarjeta')
 * estado      CHECK IN ('Completada','Anulada')  DEFAULT 'Completada'
 * La columna de fecha es `fecha` (no created_at/updated_at).
 */
class Venta extends Model
{
    protected $table = 'ventas';
    public $timestamps = false;

    protected $fillable = [
        'cliente_id', 'usuario_id', 'fecha', 'metodo_pago', 'estado', 'total',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'decimal:2',
    ];

    public const METODOS_PAGO = ['Efectivo', 'Tarjeta'];
    public const ESTADOS      = ['Completada', 'Anulada'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'venta_id');
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'Completada');
    }
}
