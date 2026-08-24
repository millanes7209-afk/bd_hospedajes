<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('subdominio')->unique();
            $table->string('rubro')->default('GENERAL');
            $table->string('db_host')->default('127.0.0.1');
            $table->string('db_nombre');
            $table->string('db_usuario')->default('root');
            $table->string('db_password')->nullable();
            $table->string('logo')->nullable();
            $table->string('primary_color')->default('#FFE66D');
            $table->string('accent_color')->default('#E23E1A');
            $table->string('dark_bg_color')->default('#09090c');
            $table->string('dark_card_color')->default('#15151e');
            $table->string('light_bg_color')->default('#eceef1');
            $table->string('light_card_color')->default('#ffffff');
            $table->char('_estado', 1)->default('A');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
