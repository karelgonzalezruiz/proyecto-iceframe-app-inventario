<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Tabla: usuarios (id, rol_id, nombre, cedula, email, password, activo, created_at)
 *
 * Modelo de autenticación interno. NO usa la tabla `users` de Laravel.
 * El esquema solo tiene created_at (no updated_at), por eso $timestamps = false.
 * La tabla NO tiene columna remember_token, por eso se desactiva esa función.
 * Las contraseñas semilla ya vienen hasheadas con bcrypt ($2y$) y se validan
 * con Hash::check() / Auth::attempt() de Laravel.
 */
class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    public $timestamps = false;

    protected $fillable = [
        'rol_id', 'nombre', 'cedula', 'email', 'password', 'activo',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        
    }

    public function getRememberTokenName()
    {
        return null;
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'usuario_id');
    }

    // ----- Helpers de rol -----

    public function esAdministrador(): bool
    {
        return optional($this->rol)->nombre === 'Administrador';
    }

    public function esTrabajador(): bool
    {
        return optional($this->rol)->nombre === 'Trabajador';
    }
}