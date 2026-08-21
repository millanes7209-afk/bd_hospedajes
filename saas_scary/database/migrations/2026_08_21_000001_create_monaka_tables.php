<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Tabla de Mesas
        if (!Schema::hasTable('mesas')) {
            Schema::create('mesas', function (Blueprint $table) {
                $table->id('mesaID');
                $table->string('nombre', 50); // Ej: Mesa 1, Mesa 2
                $table->enum('estado', ['libre', 'ocupada'])->default('libre');
                $table->timestamp('fecha_creacion')->useCurrent();
            });
        }

        // 2. Tabla de Ventas (Unifica Local y WhatsApp)
        if (!Schema::hasTable('ventas')) {
            Schema::create('ventas', function (Blueprint $table) {
                $table->id('ventaID');
                $table->enum('origen', ['local', 'whatsapp']);
                $table->enum('tipo_venta', ['mesa', 'llevar', 'delivery']);
                $table->unsignedBigInteger('mesaID')->nullable();
                $table->string('cliente_nombre', 150)->nullable();
                $table->string('cliente_telefono', 30)->nullable();
                $table->string('direccion_entrega', 255)->nullable();
                $table->string('nota', 255)->nullable();
                $table->enum('estado', ['abierta', 'pendiente', 'en_preparacion', 'lista', 'cerrada'])->default('abierta');
                $table->decimal('monto_total', 10, 2)->default(0.00);
                $table->unsignedBigInteger('usuario_apertura_id');
                $table->unsignedBigInteger('usuario_cierre_id')->nullable();
                $table->timestamp('fecha_apertura')->useCurrent();
                $table->timestamp('fecha_cierre')->nullable();

                $table->foreign('mesaID')->references('mesaID')->on('mesas')->onDelete('set null');
            });
        }

        // 3. Tabla de Ítems de la Venta (Snapshot de nombre y precio)
        if (!Schema::hasTable('venta_items')) {
            Schema::create('venta_items', function (Blueprint $table) {
                $table->id('ventaItemID');
                $table->unsignedBigInteger('ventaID');
                $table->unsignedBigInteger('productoID');
                $table->unsignedBigInteger('varianteID')->nullable();
                $table->string('nombre_producto', 150);
                $table->string('nombre_variante', 100)->nullable();
                $table->integer('cantidad')->default(1);
                $table->decimal('precio_unitario', 10, 2);
                $table->decimal('precio_total', 10, 2);
                $table->string('nota', 255)->nullable();
                $table->timestamp('fecha_creacion')->useCurrent();

                $table->foreign('ventaID')->references('ventaID')->on('ventas')->onDelete('cascade');
            });
        }

        // 4. Tabla de Pagos (Soporta cobro dividido)
        if (!Schema::hasTable('pagos')) {
            Schema::create('pagos', function (Blueprint $table) {
                $table->id('pagoID');
                $table->unsignedBigInteger('ventaID');
                $table->enum('metodo_pago', ['qr', 'efectivo']);
                $table->decimal('monto', 10, 2);
                $table->timestamp('fecha_creacion')->useCurrent();

                $table->foreign('ventaID')->references('ventaID')->on('ventas')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('venta_items');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('mesas');
    }
};
