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

        if (Schema::hasTable('insumos')) {
            self::addColumnSafe('insumos', 'stock_actual', function (Blueprint $table) {
                $table->decimal('stock_actual', 10, 2)->default(0);
            });
            self::addColumnSafe('insumos', 'stock_minimo', function (Blueprint $table) {
                $table->decimal('stock_minimo', 10, 2)->default(0);
            });
        }

        if (Schema::hasTable('compras')) {
            self::addColumnSafe('compras', 'metodo_pago', function (Blueprint $table) {
                $table->string('metodo_pago', 20)->default('efectivo');
            });
            self::addColumnSafe('compras', 'proveedor', function (Blueprint $table) {
                $table->string('proveedor', 100)->nullable();
            });
            self::addColumnSafe('compras', 'comprobante_nro', function (Blueprint $table) {
                $table->string('comprobante_nro', 50)->nullable();
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

        try {
            if (!Schema::hasTable('variantes_saltena')) {
                Schema::create('variantes_saltena', function (Blueprint $table) {
                    $table->id();
                    $table->string('nombre');
                    $table->decimal('precio_venta', 10, 2)->default(0);
                    $table->boolean('activo')->default(true);
                    $table->timestamps();
                });
            } else {
                self::addColumnSafe('variantes_saltena', 'activo', function (Blueprint $table) {
                    $table->boolean('activo')->default(true);
                });
                self::addColumnSafe('variantes_saltena', 'precio_venta', function (Blueprint $table) {
                    $table->decimal('precio_venta', 10, 2)->default(0);
                });
            }
        } catch (\Throwable $e) {
            // Ignorar
        }

        try {
            if (!Schema::hasTable('variante_receta')) {
                Schema::create('variante_receta', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('variante_id')->constrained('variantes_saltena')->onDelete('cascade');
                    $table->enum('tipo_componente', ['insumo', 'preparacion']);
                    $table->foreignId('insumo_id')->nullable()->constrained('insumos')->onDelete('cascade');
                    $table->foreignId('preparacion_id')->nullable()->constrained('preparaciones')->onDelete('cascade');
                    $table->decimal('cantidad_usada', 10, 4)->default(0);
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Ignorar
        }

        try {
            if (!Schema::hasTable('carrito_turnos_vivos')) {
                Schema::create('carrito_turnos_vivos', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('carrito_id')->unique()->constrained('carritos')->onDelete('cascade');
                    $table->unsignedBigInteger('turno_remoto_id')->nullable();
                    $table->string('estado', 20)->default('cerrado');
                    $table->boolean('stock_aceptado')->default(false);
                    $table->date('fecha')->nullable();
                    $table->timestamp('abierto_at')->nullable();
                    $table->timestamp('cerrado_at')->nullable();
                    $table->integer('total_enviadas')->default(0);
                    $table->integer('total_vendidas')->default(0);
                    $table->integer('total_restantes')->default(0);
                    $table->decimal('dinero_total', 10, 2)->default(0);
                    $table->decimal('dinero_efectivo', 10, 2)->default(0);
                    $table->decimal('dinero_qr', 10, 2)->default(0);
                    $table->longText('desglose_variedades')->nullable();
                    $table->timestamp('ultima_actualizacion_pos')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Ignorar si ya existe por petición concurrente
        }

        try {
            if (!Schema::hasTable('sessions')) {
                Schema::create('sessions', function (Blueprint $table) {
                    $table->string('id')->primary();
                    $table->foreignId('user_id')->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
            }
        } catch (\Throwable $e) {
            // Ignorar si ya existe por petición concurrente
        }

        try {
            if (!Schema::hasTable('cache')) {
                Schema::create('cache', function (Blueprint $table) {
                    $table->string('key')->primary();
                    $table->mediumText('value');
                    $table->integer('expiration')->index();
                });
            }
            if (!Schema::hasTable('cache_locks')) {
                Schema::create('cache_locks', function (Blueprint $table) {
                    $table->string('key')->primary();
                    $table->string('owner');
                    $table->integer('expiration')->index();
                });
            }
        } catch (\Throwable $e) {
            // Ignorar si ya existe por petición concurrente
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
