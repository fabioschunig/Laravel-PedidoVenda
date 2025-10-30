<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Usuários de teste (admin, vendedor, visualizador)
        $this->call([
            UserSeeder::class,
        ]);

        // Dados base
        Customer::factory(20)->create();
        Product::factory(15)->create();

        // Pedidos, vinculados a usuários e clientes já existentes
        Order::factory(30)
            ->state(fn() => [
                'user_id' => User::inRandomOrder()->first()->id,
                'customer_id' => Customer::inRandomOrder()->first()->id,
            ])
            ->create();
    }
}
