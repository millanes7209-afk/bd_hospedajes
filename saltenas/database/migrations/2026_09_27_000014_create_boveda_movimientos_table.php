<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boveda_movimientos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 10, 2);
            $table->decimal('dinero_efectivo', 10, 2)->default(0);
            $table->decimal('dinero_qr', 10, 2)->default(0);
            $table->date('fecha');
            $table->foreignId('cierre_diario_id')->nullable()->constrained('cierres_diarios')->onDelete('cascade');
            $table->foreignId('compra_id')->nullable()->constrained('compras')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boveda_movimientos');
    }
};
