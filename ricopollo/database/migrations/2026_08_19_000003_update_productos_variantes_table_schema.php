<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Actualizar tabla productos
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'tipo')) {
                $table->enum('tipo', ['simple', 'variantes'])->default('simple')->after('descripcion');
            }
            if (!Schema::hasColumn('productos', 'dia_promo')) {
                $table->enum('dia_promo', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'])->nullable()->after('precio_promo');
            }
            if (!Schema::hasColumn('productos', 'stock')) {
                $table->integer('stock')->nullable()->after('dia_promo');
            }
            if (!Schema::hasColumn('productos', 'activo')) {
                $table->tinyInteger('activo')->default(1)->after('stock');
            }
            if (!Schema::hasColumn('productos', 'disponible')) {
                $table->tinyInteger('disponible')->default(1)->after('activo');
            }
            if (!Schema::hasColumn('productos', 'disponible_desde')) {
                $table->dateTime('disponible_desde')->nullable()->after('disponible');
            }
        });

        // 2. Actualizar tabla producto_variantes
        Schema::table('producto_variantes', function (Blueprint $table) {
            if (!Schema::hasColumn('producto_variantes', 'cantidad')) {
                $table->decimal('cantidad', 10, 2)->nullable()->after('nombre_variante');
            }
            if (!Schema::hasColumn('producto_variantes', 'unidad')) {
                $table->string('unidad', 20)->nullable()->after('cantidad');
            }
            if (!Schema::hasColumn('producto_variantes', 'precio_promo')) {
                $table->decimal('precio_promo', 10, 2)->nullable()->after('precio');
            }
            if (!Schema::hasColumn('producto_variantes', 'dia_promo')) {
                $table->enum('dia_promo', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'])->nullable()->after('precio_promo');
            }
            if (!Schema::hasColumn('producto_variantes', 'stock')) {
                $table->integer('stock')->nullable()->after('dia_promo');
            }
            if (!Schema::hasColumn('producto_variantes', 'disponible')) {
                $table->tinyInteger('disponible')->default(1)->after('activo');
            }
            if (!Schema::hasColumn('producto_variantes', 'disponible_desde')) {
                $table->dateTime('disponible_desde')->nullable()->after('disponible');
            }
            if (!Schema::hasColumn('producto_variantes', 'orden_mostrado')) {
                $table->integer('orden_mostrado')->default(0)->after('disponible_desde');
            }
        });

        // 3. Actualizar tabla pedido_items si existe
        if (Schema::hasTable('pedido_items')) {
            Schema::table('pedido_items', function (Blueprint $table) {
                if (!Schema::hasColumn('pedido_items', 'varianteID')) {
                    $table->integer('varianteID')->unsigned()->nullable()->after('productoID');
                }
                if (!Schema::hasColumn('pedido_items', 'nombre_variante')) {
                    $table->string('nombre_variante', 150)->nullable()->after('varianteID');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo
    }
};
