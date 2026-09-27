<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cierre_diario_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cierre_diario_id')->constrained('cierres_diarios')->onDelete('cascade');
            $table->foreignId('variante_id')->constrained('variantes_saltena')->onDelete('cascade');
            $table->integer('cantidad_entregada');
            $table->integer('cantidad_vendida_normal');
            $table->integer('cantidad_sobrante');
            $table->decimal('precio_unitario_aplicado', 10, 2);
            $table->boolean('inconsistente')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierre_diario_detalle');
    }
};
