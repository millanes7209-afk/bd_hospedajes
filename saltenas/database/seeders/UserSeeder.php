<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'millanes7209@gmail.com'],
            [
                'name' => 'Administrador',
                'email' => 'millanes7209@gmail.com',
                'password' => Hash::make('SCARYmovie'),
            ]
        );
    }
}
