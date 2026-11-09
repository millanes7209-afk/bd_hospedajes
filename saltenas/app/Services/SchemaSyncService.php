<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SchemaSyncService
{
    protected static bool $checked = false;

    /**
     * Asegura en tiempo de ejecución que cierres_diarios y boveda_movimientos
     * tengan las columnas dinero_efectivo, dinero_qr, estado y origen en producción.
     */
    public static function ensureUpdatedColumns(): void
    {
        if (self::$checked) {
            return;
        }
        self::$checked = true;

        if (Schema::hasTable('boveda_movimientos')) {
            self::addColumnSafe('boveda_movimientos', 'dinero_efectivo', function (Blueprint $table) {
                $table->decimal('dinero_efectivo', 10, 2)->default(0);
            });
            self::addColumnSafe('boveda_movimientos', 'dinero_qr', function (Blueprint $table) {
                $table->decimal('dinero_qr', 10, 2)->default(0);
            });
        }

        if (Schema::hasTable('cierres_diarios')) {
            self::addColumnSafe('cierres_diarios', 'dinero_efectivo', function (Blueprint $table) {
                $table->decimal('dinero_efectivo', 10, 2)->default(0);
            });
            self::addColumnSafe('cierres_diarios', 'dinero_qr', function (Blueprint $table) {
                $table->decimal('dinero_qr', 10, 2)->default(0);
            });
            self::addColumnSafe('cierres_diarios', 'estado', function (Blueprint $table) {
                $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('aprobado');
            });
            self::addColumnSafe('cierres_diarios', 'origen', function (Blueprint $table) {
                $table->enum('origen', ['manual', 'remoto'])->default('manual');
            });
        }

        if (Schema::hasTable('carritos')) {
            self::addColumnSafe('carritos', 'subdominio', function (Blueprint $table) {
                $table->string('subdominio')->nullable();
            });
        }
    }

    protected static function addColumnSafe(string $tableName, string $columnName, \Closure $definition): void
    {
        try {
            if (!Schema::hasColumn($tableName, $columnName)) {
                Schema::table($tableName, $definition);
            }
        } catch (\Throwable $e) {
            // Ignorar si ya fue agregada por una petición concurrente o migración previa
        }
    }
}
