<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: categorias (id, nombre, descripcion)
 */
class Categoria extends Model
{
    protected $table = 'categorias';
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
