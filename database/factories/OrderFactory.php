<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),
            'status' => fake()->randomElement(OrderStatus::cases()),
            'total' => 0, // será recalculado após criar os itens
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            OrderItem::factory()
                ->count(fake()->numberBetween(1, 5))
                ->for($order)
                ->create();

            $order->recalculateTotal();
        });
    }
}
