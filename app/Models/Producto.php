<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: productos
 * (id, categoria_id, marca_id, proveedor_id, nombre, descripcion,
 *  condicion, precio_unitario, stock_actual, stock_minimo, activo, created_at)
 *
 * condicion CHECK IN ('Nuevo','Reacondicionado','Usado','Repuesto','Accesorio')
 * Eliminación lógica: activo = false (nunca borrado físico).
 */
class Producto extends Model
{
    protected $table = 'productos';
    public $timestamps = false;

    protected $fillable = [
        'categoria_id', 'marca_id', 'proveedor_id', 'nombre', 'descripcion',
        'condicion', 'precio_unitario', 'stock_actual', 'stock_minimo', 'activo',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'stock_actual'    => 'integer',
        'stock_minimo'    => 'integer',
        'activo'          => 'boolean',
    ];

    /** Valores válidos del CHECK de la columna condicion. */
    public const CONDICIONES = ['Nuevo', 'Reacondicionado', 'Usado', 'Repuesto', 'Accesorio'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    // ----- Scopes -----

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeBajoStock($query)
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo')
                     ->where('stock_actual', '>', 0);
    }

    public function scopeAgotados($query)
    {
        return $query->where('stock_actual', '<=', 0);
    }

    // ----- Reglas de negocio -----

    /**
     * ¿El producto ha sido vendido alguna vez? Devuelve true si existe al menos
     * un detalle_venta que lo referencia. Se usa para bloquear el borrado físico:
     * un producto con ventas solo puede desactivarse (activo = false), nunca
     * eliminarse, para no romper el historial de ventas.
     */
    public function tieneVentas(): bool
    {
        return $this->detalles()->exists();
    }

    // ----- Accesores de presentación -----

    public function getValorTotalAttribute(): float
    {
        return (float) $this->precio_unitario * (int) $this->stock_actual;
    }

    /**
     * Estado visual: Desactivado, Agotado, Bajo stock, Disponible.
     */
    public function getEstadoAttribute(): string
    {
        if (! $this->activo) {
            return 'Desactivado';
        }
        if ($this->stock_actual <= 0) {
            return 'Agotado';
        }
        if ($this->stock_actual <= $this->stock_minimo) {
            return 'Bajo stock';
        }
        return 'Disponible';
    }
}
