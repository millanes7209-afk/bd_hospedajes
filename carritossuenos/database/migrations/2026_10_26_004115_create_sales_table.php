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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->unsignedBigInteger('promocion_id')->nullable();
            $table->integer('cantidad')->default(1);
            $table->decimal('monto', 10, 2);
            $table->enum('tipo_pago', ['efectivo', 'qr']);
            $table->timestamp('fecha_hora')->useCurrent();
            $table->timestamps();
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('set null');
            $table->foreign('promocion_id')->references('id')->on('promociones')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};