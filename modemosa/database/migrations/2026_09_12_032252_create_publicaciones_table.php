<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('publicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('talla', 20)->nullable();
            $table->string('marca', 80)->nullable();
            $table->enum('estado_prenda', ['nuevo', 'usado'])->default('usado');
            $table->enum('estado_pub', ['borrador', 'publicada', 'reservada', 'vendida', 'expirada'])->default('borrador');
            // Metadata para fases futuras (capturada ahora, usada después)
            $table->string('color_dominante', 40)->nullable();
            $table->string('temporada', 40)->nullable();
            $table->json('tags_estilo')->nullable(); // ej: ["casual","streetwear"]
            // Comprador confirmado (cuando estado_pub = 'vendida')
            $table->foreignId('comprador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones');
    }
};
