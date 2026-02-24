<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopOrder>
 */
class WorkshopOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total_price'               => 2000,
            'type'                      => 'immediately',
            'status'                    => 'finished',
            'work_shop_id'              => fake()->numberBetween(1,20),
            'client_id'                 => fake()->numberBetween(2,30),
            'car_id'                    => fake()->numberBetween(1,30),
            'total_astimated_time'      => fake()->numberBetween(1,9),
            'order_number'              => fake()->numberBetween(1, 30),
            'total_astimated_time_type' => 'hour',
        ];
    }
}
