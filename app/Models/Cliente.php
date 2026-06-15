<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: clientes (id, cedula, nombre, created_at)
 */
class Cliente extends Model
{
    protected $table = 'clientes';
    public $timestamps = false;

    protected $fillable = ['cedula', 'nombre'];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }
}
