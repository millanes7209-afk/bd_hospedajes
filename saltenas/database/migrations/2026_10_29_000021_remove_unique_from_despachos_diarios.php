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
        Schema::table('despachos_diarios', function (Blueprint $table) {
            $table->dropUnique('despachos_diarios_carrito_id_fecha_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('despachos_diarios', function (Blueprint $table) {
            $table->unique(['carrito_id', 'fecha']);
        });
    }
};
