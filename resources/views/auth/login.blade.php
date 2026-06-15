<!DOCTYPE html>
<html lang="es" class="iceframe-login-page">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · IceFrame Inventory</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/iceframe-icon.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/iceframe.css') }}">
</head>
<body>
<div class="iceframe-login-wrap">
    <div class="card iceframe-login-card">
        <div class="card-body p-4 p-md-5">
            <div class="iceframe-login-brand mb-4">
                <img src="{{ asset('images/brand/iceframe-logo.png') }}" alt="IceFrame">
            </div>
            <p class="text-center text-secondary mb-4">Sistema interno de inventario</p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control" placeholder="admin@iceframe.com" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control"
                           placeholder="Tu contraseña" required>
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn btn-iceframe w-100">
                        <i class="ti ti-login me-1"></i> Entrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
