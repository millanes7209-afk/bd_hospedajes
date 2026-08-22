<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Middleware\TenantMiddleware;
use App\Http\Middleware\AdminAuthMiddleware;

use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckRole;

Route::middleware([TenantMiddleware::class])->group(function () {
    Route::get('/', function () {
        return redirect()->route('menu');
    });

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/menu', [MenuController::class, 'index'])->name('menu');
    Route::get('/api/menu/disponibilidad', [MenuController::class, 'getDisponibilidad'])->name('api.menu.disponibilidad');

    Route::match(['get', 'post'], '/order', [PedidoController::class, 'showCheckout'])->name('order.checkout');
    Route::post('/order/confirm', [PedidoController::class, 'storeOrder'])->name('order.confirm');
    Route::get('/ticket/{id}', [PedidoController::class, 'showTicket'])->name('ticket.show');
    Route::get('/api/pedidos/{id}/estado', [PedidoController::class, 'getApiEstado'])->name('api.pedidos.estado');
    Route::post('/pedidos/{id}/confirmar-pago', [PedidoController::class, 'confirmarPagoCliente'])->name('pedidos.confirmarPago');

    Route::prefix('admin')->middleware([AdminAuthMiddleware::class])->group(function () {
        Route::get('/productos', [ProductoController::class, 'index'])->name('admin.productos');
        Route::get('/productos/crear', [ProductoController::class, 'create'])->name('admin.productos.create');
        Route::post('/productos/crear', [ProductoController::class, 'store'])->name('admin.productos.store');
        Route::get('/productos/editar/{id}', [ProductoController::class, 'edit'])->name('admin.productos.edit');
        Route::post('/productos/editar/{id}', [ProductoController::class, 'update'])->name('admin.productos.update');
        Route::get('/productos/toggle/{id}', [ProductoController::class, 'toggleDisponible'])->name('admin.productos.toggle');
        Route::get('/productos/variantes/toggle/{producto_id}/{variante_id}', [ProductoController::class, 'toggleVariante'])->name('admin.productos.variantes.toggle');
        Route::get('/productos/eliminar/{id}', [ProductoController::class, 'destroy'])->name('admin.productos.destroy');
        Route::get('/pedidos', [PedidoController::class, 'index'])->name('admin.pedidos');
        Route::get('/api/pedidos', [PedidoController::class, 'getAdminApiPedidos'])->name('api.admin.pedidos');
        Route::post('/pedidos/aceptar/{id}', [PedidoController::class, 'aceptarPedido'])->name('admin.pedidos.aceptar');
        Route::post('/pedidos/rechazar/{id}', [PedidoController::class, 'rechazarPedido'])->name('admin.pedidos.rechazar');
        Route::post('/pedidos/estado/{id}', [PedidoController::class, 'updateEstado'])->name('admin.pedidos.estado');

        // POS Mesas (Cuentas abiertas y cobro dividido)
        Route::get('/mesas', [\App\Http\Controllers\MesaController::class, 'index'])->name('admin.mesas');
        Route::post('/mesas/abrir/{id}', [\App\Http\Controllers\MesaController::class, 'abrirMesa'])->name('admin.mesas.abrir');
        Route::post('/mesas/item/agregar/{id}', [\App\Http\Controllers\MesaController::class, 'agregarItem'])->name('admin.mesas.item.agregar');
        Route::get('/mesas/item/remover/{id}', [\App\Http\Controllers\MesaController::class, 'removerItem'])->name('admin.mesas.item.remover');
        Route::post('/mesas/pago/{id}', [\App\Http\Controllers\MesaController::class, 'registrarPago'])->name('admin.mesas.pago');

        // Reportes y Estadísticas
        Route::get('/reportes', [\App\Http\Controllers\ReporteController::class, 'index'])->name('admin.reportes');

        // Perfil personal para Cajeros y Administradores
        Route::get('/perfil', [UserController::class, 'profile'])->name('admin.perfil');
        Route::post('/perfil/password', [UserController::class, 'updatePassword'])->name('admin.perfil.password');

        // Rutas protegidas solo para ADMINISTRADOR y SUPER_ADMIN
        Route::middleware([CheckRole::class . ':ADMINISTRADOR'])->group(function () {
            Route::get('/usuarios', [UserController::class, 'index'])->name('admin.usuarios');
            Route::get('/usuarios/crear', [UserController::class, 'create'])->name('admin.usuarios.create');
            Route::post('/usuarios/crear', [UserController::class, 'store'])->name('admin.usuarios.store');
            Route::get('/usuarios/editar/{id}', [UserController::class, 'edit'])->name('admin.usuarios.edit');
            Route::post('/usuarios/editar/{id}', [UserController::class, 'update'])->name('admin.usuarios.update');
            Route::get('/usuarios/eliminar/{id}', [UserController::class, 'destroy'])->name('admin.usuarios.destroy');
        });
    });

    // Fallbacks para enlaces antiguos
    Route::get('/admin.php', function () {
        return redirect()->route('admin.productos');
    });
    Route::get('/login.php', function () {
        return redirect()->route('login');
    });
    Route::get('/menu.php', function () {
        return redirect()->route('menu');
    });
    Route::get('/products.php', function () {
        return redirect()->route('admin.productos');
    });
    Route::match(['get', 'post'], '/order.php', function () {
        return redirect()->route('order.checkout');
    });
    Route::get('/ticket.php', function () {
        $id = request()->query('id');
        return $id ? redirect()->route('ticket.show', ['id' => $id]) : redirect()->route('menu');
    });
});
