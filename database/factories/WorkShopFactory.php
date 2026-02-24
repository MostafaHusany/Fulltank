<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkShop>
 */
class WorkShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'    => fake()->name(),
            'address' => fake()->address(),
            'geo_lat' => 30.104732,
            'geo_lng' => 31.378030,
            'phone_1' => fake()->unique()->e164PhoneNumber(),
            'ar_description' => 'demo desc arabic',
            'en_description' => 'demo desc english',
            'is_main' => 1,
            'orders_capacity' => 3,
            'free_orders_space' => 3,
            'gove_id'    => 1,
            'dist_id'    => 15,
            'work_shop_manager_id' => fake()->numberBetween(32, 51)
        ];
    }
}
