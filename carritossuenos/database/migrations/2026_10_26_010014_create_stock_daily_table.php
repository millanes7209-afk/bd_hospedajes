<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_diario', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->integer('cantidad_enviada')->default(0);
            $table->boolean('aceptado')->default(false);
            $table->unsignedBigInteger('despacho_central_id')->nullable();
            $table->timestamps();

            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_diario');
    }
};