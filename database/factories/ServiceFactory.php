<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $services = [
            'Постільна білизна',
            'Сніданок у вагоні',
            'Обід у вагоні',
            'Чай та кава',
            'Страхування подорожі',
            'Транспортування багажу',
            'Інтернет Wi-Fi',
            'Кондиціонер',
            'Періодичні видання',
            'Напої (вода, сік)',
        ];

        $name = fake()->unique()->randomElement($services);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'price' => fake()->randomFloat(2, 20, 250),
        ];
    }
}
