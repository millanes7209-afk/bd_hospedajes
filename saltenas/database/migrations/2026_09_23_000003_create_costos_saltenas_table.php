<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('costos_saltenas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_variante', 100); // ej: Salteña de Pollo, Salteña de Carne, Salteña Mixta
            $table->decimal('costo_unidad', 10, 2)->default(0.00);
            $table->decimal('precio_venta', 10, 2)->default(8.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('costos_saltenas');
    }
};
