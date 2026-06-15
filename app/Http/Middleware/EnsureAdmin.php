<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe rutas exclusivas del Administrador (desactivar/reactivar producto,
 * registrar hurto). Los Trabajadores reciben 403.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! $usuario->esAdministrador()) {
            abort(403, 'Acción permitida solo para administradores.');
        }

        return $next($request);
    }
}
