<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicacion_id')->constrained('publicaciones')->cascadeOnDelete();
            $table->foreignId('vendedor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('comprador_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps(); // created_at = cuando el vendedor marcó "Vendida"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
