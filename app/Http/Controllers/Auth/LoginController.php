<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Login interno simple contra la tabla `usuarios` (no `users`).
 * Sin Breeze ni starter kits. Valida con Hash::check y solo deja
 * entrar a usuarios activos.
 */
class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'El correo no tiene un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $usuario = Usuario::with('rol')->where('email', $credentials['email'])->first();

        // Mensaje genérico para no revelar si el correo existe.
        $error = ['email' => 'Las credenciales no son válidas.'];

        if (! $usuario || ! Hash::check($credentials['password'], $usuario->password)) {
            return back()->withErrors($error)->onlyInput('email');
        }

        if (! $usuario->activo) {
            return back()->withErrors([
                'email' => 'El usuario está desactivado. Contacte al administrador.',
            ])->onlyInput('email');
        }

        Auth::login($usuario, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
