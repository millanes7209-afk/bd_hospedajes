<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('cierres_diarios')) {
            if (!Schema::hasColumn('cierres_diarios', 'dinero_efectivo')) {
                Schema::table('cierres_diarios', function (Blueprint $table) {
                    $table->decimal('dinero_efectivo', 10, 2)->default(0);
                });
            }
            if (!Schema::hasColumn('cierres_diarios', 'dinero_qr')) {
                Schema::table('cierres_diarios', function (Blueprint $table) {
                    $table->decimal('dinero_qr', 10, 2)->default(0);
                });
            }
            if (!Schema::hasColumn('cierres_diarios', 'estado')) {
                Schema::table('cierres_diarios', function (Blueprint $table) {
                    $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('aprobado');
                });
            }
            if (!Schema::hasColumn('cierres_diarios', 'origen')) {
                Schema::table('cierres_diarios', function (Blueprint $table) {
                    $table->enum('origen', ['manual', 'remoto'])->default('manual');
                });
            }
        }

        if (Schema::hasTable('boveda_movimientos')) {
            if (!Schema::hasColumn('boveda_movimientos', 'dinero_efectivo')) {
                Schema::table('boveda_movimientos', function (Blueprint $table) {
                    $table->decimal('dinero_efectivo', 10, 2)->default(0);
                });
            }
            if (!Schema::hasColumn('boveda_movimientos', 'dinero_qr')) {
                Schema::table('boveda_movimientos', function (Blueprint $table) {
                    $table->decimal('dinero_qr', 10, 2)->default(0);
                });
            }
        }

        if (Schema::hasTable('carritos') && !Schema::hasColumn('carritos', 'subdominio')) {
            Schema::table('carritos', function (Blueprint $table) {
                $table->string('subdominio')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Conservador: no eliminar columnas operativas en rollback
    }
};
