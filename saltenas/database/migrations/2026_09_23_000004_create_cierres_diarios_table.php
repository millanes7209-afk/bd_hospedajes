<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cierres_diarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->date('fecha'); // Editable, default hoy
            $table->enum('clima', ['frio_lluvia', 'templado_nublado', 'caluroso_soleado'])->default('templado_nublado');
            $table->integer('temp_min')->nullable(); // °C
            $table->integer('temp_max')->nullable(); // °C
            $table->integer('saltenas_vendidas')->default(0);
            $table->integer('saltenas_sobrantes')->default(0);
            $table->decimal('total_efectivo', 10, 2)->default(0.00);
            $table->decimal('total_qr', 10, 2)->default(0.00);
            $table->decimal('total_recaudado', 10, 2)->default(0.00);
            $table->decimal('costo_total_jornada', 10, 2)->default(0.00);
            $table->decimal('ganancia_neta', 10, 2)->default(0.00);
            $table->text('observaciones')->nullable(); // Días festivos, marchas, eventos
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Evitar duplicar el cierre de la misma sucursal en la misma fecha
            $table->unique(['sucursal_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierres_diarios');
    }
};
