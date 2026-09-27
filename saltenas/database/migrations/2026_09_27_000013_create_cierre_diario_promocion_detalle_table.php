<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cierre_diario_promocion_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cierre_diario_detalle_id')->constrained('cierre_diario_detalle')->onDelete('cascade');
            $table->foreignId('promocion_id')->constrained('promociones')->onDelete('cascade');
            $table->integer('paquetes_vendidos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierre_diario_promocion_detalle');
    }
};
