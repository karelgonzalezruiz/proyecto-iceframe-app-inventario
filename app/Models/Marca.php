<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: marcas (id, nombre)
 */
class Marca extends Model
{
    protected $table = 'marcas';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'marca_id');
    }
}
