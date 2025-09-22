<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@vendedor.com',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        User::create([
            'name' => 'Visualizador',
            'email' => 'visualizador@visualizador.com',
            'password' => Hash::make('password'),
            'role' => 'visualizador',
        ]);
    }
}
