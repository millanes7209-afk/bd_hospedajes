<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('variante_receta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variante_id')->constrained('variantes_saltena')->onDelete('cascade');
            $table->enum('tipo_componente', ['insumo', 'preparacion']);
            $table->foreignId('insumo_id')->nullable()->constrained('insumos')->onDelete('cascade');
            $table->foreignId('preparacion_id')->nullable()->constrained('preparaciones')->onDelete('cascade');
            $table->decimal('cantidad_usada', 10, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variante_receta');
    }
};
