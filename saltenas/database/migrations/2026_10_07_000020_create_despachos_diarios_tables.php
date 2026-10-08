<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('despachos_diarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrito_id')->constrained('carritos')->onDelete('cascade');
            $table->date('fecha');
            $table->enum('estado', ['pendiente', 'aceptado', 'cerrado'])->default('pendiente');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['carrito_id', 'fecha']);
        });

        Schema::create('despacho_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('despacho_diario_id')->constrained('despachos_diarios')->onDelete('cascade');
            $table->foreignId('variante_id')->constrained('variantes_saltenas')->onDelete('cascade');
            $table->integer('cantidad_enviada')->default(0);
            $table->integer('cantidad_aceptada')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despacho_detalles');
        Schema::dropIfExists('despachos_diarios');
    }
};
