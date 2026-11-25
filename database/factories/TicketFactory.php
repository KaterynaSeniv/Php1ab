<?php

namespace Database\Factories;

use App\Models\TrainRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'train_route_id' => TrainRoute::factory(),
            'passenger_full_name' => fake()->lastName().' '.fake()->firstName(),
            'car_number' => fake()->numberBetween(1, 5),
            'seat_number' => fake()->numberBetween(1, 36),
            'price' => fake()->randomFloat(2, 250, 1500),
            'status' => fake()->randomElement(['booked', 'paid']),
        ];
    }
}
