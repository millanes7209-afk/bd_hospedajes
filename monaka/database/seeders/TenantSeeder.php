<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed Super Admin (Developer) in central database saas_control
        \App\Models\SuperAdmin::updateOrCreate(
            ['email' => 'micklanessz@gmail.com'],
            [
                'nombre' => 'DESARROLLADOR SCARY',
                'password' => \Illuminate\Support\Facades\Hash::make('SCARYmovie1.'),
                'rol' => 'SUPER_ADMIN',
                '_estado' => 'A',
            ]
        );

        // 1. Create Rico Pollo tenant entry in central saas_control DB
        $tenant = Tenant::updateOrCreate(
            ['subdominio' => 'ricopollo'],
            [
                'nombre' => 'Rico Pollo',
                'rubro' => 'RESTAURANTE',
                'db_host' => '127.0.0.1',
                'db_nombre' => 'rico_pollo',
                'db_usuario' => 'root',
                'db_password' => '',
                'logo' => 'assets/ricopollo.svg',
                'primary_color' => '#FFE66D',
                'accent_color' => '#E23E1A',
                'dark_bg_color' => '#09090c',
                'dark_card_color' => '#15151e',
                'light_bg_color' => '#eceef1',
                'light_card_color' => '#ffffff',
                '_estado' => 'A',
            ]
        );

        // 2. Switch to tenant DB rico_pollo
        config([
            'database.connections.tenant' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'database' => 'rico_pollo',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ]
        ]);

        DB::purge('tenant');

        // Create users table in bd_ricopollo if it doesn't exist
        DB::connection('tenant')->statement("
            CREATE TABLE IF NOT EXISTS `users` (
              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `email` varchar(255) NOT NULL UNIQUE,
              `password` varchar(255) NOT NULL,
              `rol` varchar(50) DEFAULT 'ADMINISTRADOR',
              `remember_token` varchar(100) DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Insert or update test admin user for Rico Pollo
        $userExists = DB::connection('tenant')->table('users')->where('email', 'admin@ricopollo.com')->first();
        if (!$userExists) {
            DB::connection('tenant')->table('users')->insert([
                'name' => 'ADMINISTRADOR RICO POLLO',
                'email' => 'admin@ricopollo.com',
                'password' => Hash::make('12345678'),
                'rol' => 'ADMINISTRADOR',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
