<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('carrito_turnos_vivos');
    }
};
