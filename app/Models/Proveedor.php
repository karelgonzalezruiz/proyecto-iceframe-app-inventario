<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: proveedores (id, nombre, telefono, email, created_at)
 */
class Proveedor extends Model
{
    protected $table = 'proveedores';
    public $timestamps = false;

    protected $fillable = ['nombre', 'telefono', 'email'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }
}
