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
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carrito_id');
            $table->unsignedBigInteger('variante_id'); // tipo de salteña (referencia a variantes_saltena)
            $table->integer('cantidad');
            $table->boolean('approved')->default(false);
            $table->timestamps();

            $table->foreign('carrito_id')
                  ->references('id')
                  ->on('carritos')
                  ->onDelete('cascade');

            $table->foreign('variante_id')
                  ->references('id')
                  ->on('variantes_saltena')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despachos');
    }
};
?>