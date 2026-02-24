<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopOrderReview>
 */
class WorkshopOrderReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comment'     => fake()->text(),
            'reviewed_by' => 'client',
            'star_rating' => fake()->randomFloat(0, 1, 5),
            'client_id'   => fake()->numberBetween(2,30),
            'order_id'    => fake()->numberBetween(1, 30),
            'work_shop_id'=> fake()->numberBetween(1, 10),
        ];
    }
}
