<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TrainRouteFactory extends Factory
{
    public function definition(): array
    {
        $cities = ['Київ-Пасажирський', 'Івано-Франківськ', 'Львів', 'Одеса-Головна', 'Харків-Пасажирський', 'Дніпро-Головний'];
        $departure = fake()->randomElement($cities);
        $arrival = fake()->randomElement(array_diff($cities, [$departure]));

        return [
            'train_number' => fake()->unique()->numerify('###').fake()->randomElement(['К', 'Л', 'Ш', 'Д']),
            'departure_station' => $departure,
            'arrival_station' => $arrival,
            'departure_time' => fake()->dateTimeBetween('+1 day', '+1 month'),
        ];
    }
}
