<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas web - IceFrame Inventory
|--------------------------------------------------------------------------
| Login interno simple contra la tabla `usuarios`. Todo el panel requiere
| autenticación. Las acciones exclusivas de Administrador usan el middleware
| 'admin' (alias de EnsureAdmin, registrado en bootstrap/app.php).
*/

// ----- Autenticación -----
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Raíz -> dashboard (o login si no hay sesión).
Route::get('/', fn () => redirect()->route('dashboard'));

// ----- Panel protegido -----
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/reportes', function () {
        $url = config('app.reportes_url');

        abort_unless($url, 503, 'El sistema de reportes no está configurado.');

        return redirect()->away($url);
    })->name('reportes');

    // Catálogo de Inventario + CRUD de productos.
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
    Route::get('/productos/csv', [ProductoController::class, 'exportarCsv'])->name('productos.csv');
    Route::get('/productos/json', [ProductoController::class, 'exportarJson'])->name('productos.json');
    Route::get('/productos/create', [ProductoController::class, 'create'])->name('productos.create');
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
    Route::get('/productos/{producto}', [ProductoController::class, 'show'])->name('productos.show');
    Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit'])->name('productos.edit');
    Route::match(['put', 'patch'], '/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');

    // Compra simple ("Registrar compra").
    Route::get('/ventas/create', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/resumen', [VentaController::class, 'resumen'])->name('ventas.resumen');
    Route::get('/ventas/resumen/csv', [VentaController::class, 'exportarResumenCsv'])->name('ventas.resumen.csv');
    Route::get('/ventas/resumen/json', [VentaController::class, 'exportarResumenJson'])->name('ventas.resumen.json');

    // Historial de movimientos.
    Route::get('/movimientos', [MovimientoController::class, 'index'])->name('movimientos.index');
    Route::get('/movimientos/csv', [MovimientoController::class, 'exportarCsv'])->name('movimientos.csv');
    Route::get('/movimientos/json', [MovimientoController::class, 'exportarJson'])->name('movimientos.json');

    // Catálogos al vuelo (JSON, usados por los modales «+ Nueva» del formulario).
    Route::post('/catalogos/marca', [ProductoController::class, 'storeMarca'])->name('catalogos.marca');
    Route::post('/catalogos/categoria', [ProductoController::class, 'storeCategoria'])->name('catalogos.categoria');
    Route::post('/catalogos/proveedor', [ProductoController::class, 'storeProveedor'])->name('catalogos.proveedor');

    // Reposición de stock (Administrador y Trabajador).
    Route::get('/inventario/reposicion', [InventarioController::class, 'reposicionForm'])->name('inventario.reposicion.form');
    Route::post('/inventario/reposicion', [InventarioController::class, 'reposicion'])->name('inventario.reposicion');

    // ----- Solo Administrador -----
    Route::middleware('admin')->group(function () {
        // Desactivación lógica (eliminar = activo = false).
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
        Route::patch('/productos/{producto}/reactivar', [ProductoController::class, 'reactivar'])->name('productos.reactivar');

        // Borrado físico permanente (solo si el producto no tiene ventas).
        Route::delete('/productos/{producto}/eliminar', [ProductoController::class, 'eliminar'])->name('productos.eliminar');

        // Registrar hurto.
        Route::get('/inventario/hurto', [InventarioController::class, 'hurtoForm'])->name('inventario.hurto.form');
        Route::post('/inventario/hurto', [InventarioController::class, 'hurto'])->name('inventario.hurto');
    });
});
