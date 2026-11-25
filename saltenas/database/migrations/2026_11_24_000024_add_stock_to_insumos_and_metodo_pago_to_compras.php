<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('insumos')) {
            Schema::table('insumos', function (Blueprint $table) {
                if (!Schema::hasColumn('insumos', 'stock_actual')) {
                    $table->decimal('stock_actual', 10, 2)->default(0)->after('unidad_medida');
                }
                if (!Schema::hasColumn('insumos', 'stock_minimo')) {
                    $table->decimal('stock_minimo', 10, 2)->default(0)->after('stock_actual');
                }
            });
        }

        if (Schema::hasTable('compras')) {
            Schema::table('compras', function (Blueprint $table) {
                if (!Schema::hasColumn('compras', 'metodo_pago')) {
                    $table->string('metodo_pago', 20)->default('efectivo')->after('monto_total');
                }
                if (!Schema::hasColumn('compras', 'proveedor')) {
                    $table->string('proveedor', 100)->nullable()->after('metodo_pago');
                }
                if (!Schema::hasColumn('compras', 'comprobante_nro')) {
                    $table->string('comprobante_nro', 50)->nullable()->after('proveedor');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('insumos')) {
            Schema::table('insumos', function (Blueprint $table) {
                if (Schema::hasColumn('insumos', 'stock_actual')) {
                    $table->dropColumn('stock_actual');
                }
                if (Schema::hasColumn('insumos', 'stock_minimo')) {
                    $table->dropColumn('stock_minimo');
                }
            });
        }

        if (Schema::hasTable('compras')) {
            Schema::table('compras', function (Blueprint $table) {
                if (Schema::hasColumn('compras', 'metodo_pago')) {
                    $table->dropColumn('metodo_pago');
                }
                if (Schema::hasColumn('compras', 'proveedor')) {
                    $table->dropColumn('proveedor');
                }
                if (Schema::hasColumn('compras', 'comprobante_nro')) {
                    $table->dropColumn('comprobante_nro');
                }
            });
        }
    }
};
