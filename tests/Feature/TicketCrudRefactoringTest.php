<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendTicketCreatedNotificationJob;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TrainRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketCrudRefactoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_and_api_creation_save_ticket_services_and_dispatch_notification_jobs(): void
    {
        $user = User::factory()->create();
        $route = TrainRoute::factory()->create([
            'base_price' => 400.25,
            'car_count' => 3,
            'seats_per_car' => 30,
        ]);
        $service = Service::factory()->create(['price' => 50.25]);
        Queue::fake();

        $this->actingAs($user)
            ->post('/tickets', [
                'train_route_id' => $route->id,
                'passenger_full_name' => 'Тестовий Пасажир',
                'car_number' => 1,
                'seat_number' => 1,
                'services' => [$service->id],
            ])
            ->assertRedirect();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tickets', [
            'train_route_id' => $route->id,
            'passenger_full_name' => 'Тестовий Пасажир',
            'car_number' => 1,
            'seat_number' => 2,
            'services' => [$service->id],
        ])
            ->assertCreated()
            ->assertJsonPath('data.price', 450.5);

        $this->assertDatabaseCount('tickets', 2);
        $this->assertDatabaseCount('service_ticket', 2);
        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'price' => 450.50,
        ]);
        Queue::assertPushed(SendTicketCreatedNotificationJob::class, 2);
    }

    public function test_web_and_api_creation_apply_the_same_required_fields(): void
    {
        $user = User::factory()->create();
        Queue::fake([SendTicketCreatedNotificationJob::class]);

        $this->actingAs($user)
            ->from('/tickets/create')
            ->post('/tickets', [])
            ->assertRedirect('/tickets/create')
            ->assertSessionHasErrors(['train_route_id', 'passenger_full_name', 'car_number', 'seat_number']);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tickets', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['train_route_id', 'passenger_full_name', 'car_number', 'seat_number']);

        $this->assertDatabaseCount('tickets', 0);
        Queue::assertNothingPushed();
    }

    public function test_api_rejects_an_occupied_seat_without_creating_a_ticket_or_dispatching_a_job(): void
    {
        $user = User::factory()->create();
        $route = TrainRoute::factory()->create([
            'car_count' => 3,
            'seats_per_car' => 30,
        ]);
        Ticket::factory()->create([
            'train_route_id' => $route->id,
            'car_number' => 2,
            'seat_number' => 12,
        ]);
        Queue::fake([SendTicketCreatedNotificationJob::class]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tickets', [
            'train_route_id' => $route->id,
            'passenger_full_name' => 'Інший Пасажир',
            'car_number' => 2,
            'seat_number' => 12,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('seat_number');

        $this->assertDatabaseCount('tickets', 1);
        Queue::assertNothingPushed();
    }
}
