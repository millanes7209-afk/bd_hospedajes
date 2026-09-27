<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // quien reporta
            $table->foreignId('publicacion_id')->constrained('publicaciones')->cascadeOnDelete();
            $table->string('motivo', 255);
            $table->enum('estado', ['pendiente', 'revisado'])->default('pendiente');
            $table->text('nota_admin')->nullable(); // observación del administrador
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
