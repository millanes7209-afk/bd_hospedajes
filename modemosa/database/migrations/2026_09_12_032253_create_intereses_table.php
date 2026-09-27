<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('intereses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('publicacion_id')->constrained('publicaciones')->cascadeOnDelete();
            $table->timestamps(); // created_at = momento del click en "Contactar"
            // Un usuario solo puede registrar 1 interés por publicación
            $table->unique(['user_id', 'publicacion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intereses');
    }
};
