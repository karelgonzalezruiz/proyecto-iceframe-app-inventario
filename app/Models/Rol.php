<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabla: roles (id, nombre)
 * Sin timestamps en el esquema.
 */
class Rol extends Model
{
    protected $table = 'roles';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }
}
