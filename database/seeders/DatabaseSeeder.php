<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\TrainRoute;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::factory()->count(8)->create();

        TrainRoute::factory()
            ->count(5)
            ->has(
                Ticket::factory()
                    ->count(6)
                    ->hasAttached($services->random(2))
            )
            ->create();
    }
}
