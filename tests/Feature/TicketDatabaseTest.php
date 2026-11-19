<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\TrainRoute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_tickets_with_relations(): void
    {
        $route = TrainRoute::factory()->create(['train_number' => '043К']);
        $ticket = Ticket::factory()->create([
            'train_route_id' => $route->id,
            'passenger_full_name' => 'Сенів Катерина Дмитрівна',
        ]);

        $response = $this->getJson('/tickets');

        $response->assertStatus(200)
            ->assertJsonFragment(['passenger_full_name' => 'Сенів Катерина Дмитрівна'])
            ->assertJsonFragment(['train_number' => '043К']);
    }

    public function test_can_create_ticket_with_route_and_services_in_database(): void
    {
        $route = TrainRoute::factory()->create();
        $services = Service::factory()->count(2)->create();

        $payload = [
            'train_route_id' => $route->id,
            'passenger_full_name' => 'Шевченко Тарас Григорович',
            'car_number' => 4,
            'seat_number' => 12,
            'price' => 540.00,
            'services' => $services->pluck('id')->toArray(),
        ];

        $response = $this->withHeaders(['X-Partner-Key' => 'RAILWAY-PARTNER-2026'])
            ->postJson('/tickets', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tickets', [
            'passenger_full_name' => 'Шевченко Тарас Григорович',
            'train_route_id' => $route->id,
        ]);

        $ticketId = $response->json('data.id');
        foreach ($services as $service) {
            $this->assertDatabaseHas('service_ticket', [
                'ticket_id' => $ticketId,
                'service_id' => $service->id,
            ]);
        }
    }
}
