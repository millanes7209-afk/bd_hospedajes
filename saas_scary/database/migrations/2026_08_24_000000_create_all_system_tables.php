<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Tabla Categorias
        if (!Schema::hasTable('categorias')) {
            Schema::create('categorias', function (Blueprint $table) {
                $table->id('categoriaID');
                $table->string('nombre', 100);
                $table->string('slug', 100)->nullable();
                $table->boolean('activo')->default(1);
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->timestamp('fecha_modificacion')->nullable();
            });
        }

        // 2. Tabla Productos
        if (!Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table) {
                $table->id('productoID');
                $table->unsignedBigInteger('categoriaID')->nullable();
                $table->string('nombre', 150);
                $table->text('descripcion')->nullable();
                $table->decimal('precio', 10, 2)->default(0.00);
                $table->string('imagen', 255)->nullable();
                $table->boolean('activo')->default(1);
                $table->timestamp('fecha_creacion')->useCurrent();
            });
        }

        // 3. Tabla Productos Variantes
        if (!Schema::hasTable('productos_variantes')) {
            Schema::create('productos_variantes', function (Blueprint $table) {
                $table->id('varianteID');
                $table->unsignedBigInteger('productoID');
                $table->string('nombre', 100);
                $table->decimal('precio', 10, 2)->default(0.00);
                $table->boolean('disponible')->default(1);
                $table->timestamp('fecha_creacion')->useCurrent();
            });
        }

        // 4. Tabla Pedidos
        if (!Schema::hasTable('pedidos')) {
            Schema::create('pedidos', function (Blueprint $table) {
                $table->id('pedidoID');
                $table->string('numero_pedido', 50)->nullable();
                $table->string('cliente_nombre', 150)->nullable();
                $table->string('cliente_telefono', 30)->nullable();
                $table->string('tipo_pedido', 50)->nullable();
                $table->string('numero_mesa', 50)->nullable();
                $table->string('direccion_entrega', 255)->nullable();
                $table->string('nota', 255)->nullable();
                $table->decimal('monto_total', 10, 2)->default(0.00);
                $table->string('estado', 50)->default('pendiente');
                $table->string('estado_pago', 50)->default('pendiente');
                $table->string('metodo_pago', 50)->default('ninguno');
                $table->decimal('latitud', 10, 8)->nullable();
                $table->decimal('longitud', 11, 8)->nullable();
                $table->timestamp('aceptado_en')->nullable();
                $table->timestamp('fecha_creacion')->useCurrent();
            });
        }

        // 5. Tabla Pedido Items
        if (!Schema::hasTable('pedido_items')) {
            Schema::create('pedido_items', function (Blueprint $table) {
                $table->id('pedidoItemID');
                $table->unsignedBigInteger('pedidoID');
                $table->unsignedBigInteger('productoID')->nullable();
                $table->unsignedBigInteger('varianteID')->nullable();
                $table->string('nombre_variante', 150)->nullable();
                $table->integer('cantidad')->default(1);
                $table->decimal('precio_unitario', 10, 2)->default(0.00);
                $table->decimal('precio_total', 10, 2)->default(0.00);
            });
        }

        // 6. Registros de Pedidos (Log audit)
        if (!Schema::hasTable('registros_pedidos')) {
            Schema::create('registros_pedidos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pedidoID');
                $table->string('evento', 100);
                $table->string('detalles', 255)->nullable();
                $table->timestamp('fecha_creacion')->useCurrent();
            });
        }

        // 7. Mesas
        if (!Schema::hasTable('mesas')) {
            Schema::create('mesas', function (Blueprint $table) {
                $table->id('mesaID');
                $table->string('nombre', 50);
                $table->enum('estado', ['libre', 'ocupada'])->default('libre');
                $table->timestamp('fecha_creacion')->useCurrent();
            });
        }

        // 8. Ventas
        if (!Schema::hasTable('ventas')) {
            Schema::create('ventas', function (Blueprint $table) {
                $table->id('ventaID');
                $table->enum('origen', ['local', 'whatsapp'])->default('local');
                $table->enum('tipo_venta', ['mesa', 'llevar', 'delivery'])->default('llevar');
                $table->unsignedBigInteger('mesaID')->nullable();
                $table->string('cliente_nombre', 150)->nullable();
                $table->string('cliente_telefono', 30)->nullable();
                $table->string('direccion_entrega', 255)->nullable();
                $table->string('nota', 255)->nullable();
                $table->enum('estado', ['abierta', 'pendiente', 'en_preparacion', 'lista', 'cerrada'])->default('abierta');
                $table->decimal('monto_total', 10, 2)->default(0.00);
                $table->unsignedBigInteger('usuario_apertura_id')->default(1);
                $table->unsignedBigInteger('usuario_cierre_id')->nullable();
                $table->timestamp('fecha_apertura')->useCurrent();
                $table->timestamp('fecha_cierre')->nullable();
            });
        }

        // 9. Venta Items
        if (!Schema::hasTable('venta_items')) {
            Schema::create('venta_items', function (Blueprint $table) {
                $table->id('ventaItemID');
                $table->unsignedBigInteger('ventaID');
                $table->unsignedBigInteger('productoID')->nullable();
                $table->unsignedBigInteger('varianteID')->nullable();
                $table->string('nombre_producto', 150)->default('');
                $table->string('nombre_variante', 100)->nullable();
                $table->integer('cantidad')->default(1);
                $table->decimal('precio_unitario', 10, 2)->default(0.00);
                $table->decimal('precio_total', 10, 2)->default(0.00);
                $table->string('nota', 255)->nullable();
                $table->timestamp('fecha_creacion')->useCurrent();
            });
        }

        // 10. Pagos
        if (!Schema::hasTable('pagos')) {
            Schema::create('pagos', function (Blueprint $table) {
                $table->id('pagoID');
                $table->unsignedBigInteger('ventaID');
                $table->enum('metodo_pago', ['qr', 'efectivo']);
                $table->decimal('monto', 10, 2)->default(0.00);
                $table->timestamp('fecha_creacion')->useCurrent();
            });
        }

        // 11. Tenants
        if (!Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150);
                $table->string('subdominio', 50)->unique();
                $table->string('rubro', 50)->nullable();
                $table->string('db_host', 100)->nullable();
                $table->string('db_nombre', 100)->nullable();
                $table->string('db_usuario', 100)->nullable();
                $table->string('db_password', 100)->nullable();
                $table->string('logo', 255)->nullable();
                $table->string('primary_color', 20)->nullable();
                $table->string('accent_color', 20)->nullable();
                $table->string('_estado', 1)->default('A');
                $table->string('eslogan', 255)->nullable();
                $table->string('footer_texto', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
    }
};
