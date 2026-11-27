<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\TrainRoute;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $passenger = User::updateOrCreate(
            ['email' => 'passenger@example.com'],
            [
                'name' => 'Тестовий пасажир',
                'password' => Hash::make('password123'),
                'role' => 'passenger',
                'email_verified_at' => now(),
            ],
        );

        $conductor = User::updateOrCreate(
            ['email' => 'conductor@example.com'],
            [
                'name' => 'Тестовий провідник',
                'password' => Hash::make('password123'),
                'role' => 'conductor',
                'email_verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Адміністратор',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        );

        $serviceDefinitions = [
            ['name' => 'Постільна білизна', 'price' => 120],
            ['name' => 'Сніданок у вагоні', 'price' => 180],
            ['name' => 'Інтернет Wi-Fi', 'price' => 90],
            ['name' => 'Страхування подорожі', 'price' => 75],
            ['name' => 'Транспортування багажу', 'price' => 150],
        ];

        $services = collect();
        foreach ($serviceDefinitions as $definition) {
            $services->push(Service::updateOrCreate(
                ['name' => $definition['name']],
                [
                    'slug' => Str::slug($definition['name']),
                    'price' => $definition['price'],
                ],
            ));
        }

        $routeDefinitions = [
            [
                'train_number' => '043К',
                'departure_station' => 'Київ-Пасажирський',
                'arrival_station' => 'Львів',
                'departure_time' => now()->addDays(2)->setTime(10, 30),
                'base_price' => 650,
                'car_count' => 10,
                'seats_per_car' => 54,
                'car_type' => 'platskart',
            ],
            [
                'train_number' => '081К',
                'departure_station' => 'Львів',
                'arrival_station' => 'Одеса',
                'departure_time' => now()->addDays(3)->setTime(18, 15),
                'base_price' => 820,
                'car_count' => 8,
                'seats_per_car' => 36,
                'car_type' => 'coupe',
            ],
            [
                'train_number' => '129П',
                'departure_station' => 'Харків-Пасажирський',
                'arrival_station' => 'Дніпро-Головний',
                'departure_time' => now()->addDay()->setTime(7, 45),
                'base_price' => 390,
                'car_count' => 12,
                'seats_per_car' => 54,
                'car_type' => 'platskart',
            ],
        ];

        foreach ($routeDefinitions as $definition) {
            TrainRoute::updateOrCreate(
                ['train_number' => $definition['train_number']],
                $definition,
            );
        }

        $route043 = TrainRoute::where('train_number', '043К')->firstOrFail();
        $route081 = TrainRoute::where('train_number', '081К')->firstOrFail();

        $conductor->conductorRoutes()->syncWithoutDetaching([
            $route043->id => [
                'shift_start' => $route043->departure_time->copy()->subMinutes(45),
                'shift_end' => $route043->departure_time->copy()->addHours(9),
                'cars_scope' => 'Вагони 1–5',
                'instructions' => 'Перевірити посадку у вагонах 1–5, бригаду та наявність послуг пасажирам.',
            ],
            $route081->id => [
                'shift_start' => $route081->departure_time->copy()->subMinutes(60),
                'shift_end' => $route081->departure_time->copy()->addHours(8),
                'cars_scope' => 'Вагони 1–4',
                'instructions' => 'Контроль купейних вагонів, звірка документів та оплата додаткових послуг.',
            ],
        ]);

        $firstRoute = TrainRoute::where('train_number', '043К')->firstOrFail();

        if (! Ticket::where('user_id', $passenger->id)->where('train_route_id', $firstRoute->id)->exists()) {
            $ticket = Ticket::create([
                'qr_token' => Str::random(64),
                'user_id' => $passenger->id,
                'train_route_id' => $firstRoute->id,
                'passenger_full_name' => 'Тестовий Пасажир',
                'car_number' => 2,
                'seat_number' => 10,
                'price' => 770,
                'status' => 'booked',
            ]);

            $ticket->services()->sync([$services->first()->id, $services->skip(2)->first()->id]);
        }
    }
}
