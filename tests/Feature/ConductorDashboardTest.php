<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TrainRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConductorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_cannot_open_conductor_dashboard(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger']);

        $this->actingAs($passenger)
            ->get('/conductor/dashboard')
            ->assertForbidden();
    }

    public function test_conductor_sees_only_assigned_route_and_passengers(): void
    {
        $conductor = User::factory()->create(['role' => 'conductor']);
        $passenger = User::factory()->create(['role' => 'passenger']);
        $assignedRoute = TrainRoute::factory()->create();
        $otherRoute = TrainRoute::factory()->create();

        $conductor->conductorRoutes()->attach($assignedRoute->id, [
            'shift_start' => now()->subHour(),
            'shift_end' => now()->addHours(8),
            'cars_scope' => 'Вагони 1–3',
            'instructions' => 'Перевірити документи.',
        ]);

        Ticket::factory()->create([
            'user_id' => $passenger->id,
            'train_route_id' => $assignedRoute->id,
            'passenger_full_name' => 'Призначений Пасажир',
        ]);
        Ticket::factory()->create([
            'user_id' => $passenger->id,
            'train_route_id' => $otherRoute->id,
            'passenger_full_name' => 'Інший Пасажир',
        ]);

        $this->actingAs($conductor)
            ->get('/conductor/dashboard')
            ->assertOk()
            ->assertSee($assignedRoute->train_number)
            ->assertDontSee($otherRoute->train_number);

        $this->actingAs($conductor)
            ->get("/conductor/routes/{$assignedRoute->id}")
            ->assertOk()
            ->assertSee('Призначений Пасажир')
            ->assertDontSee('Інший Пасажир');
    }

    public function test_conductor_cannot_open_unassigned_route(): void
    {
        $conductor = User::factory()->create(['role' => 'conductor']);
        $route = TrainRoute::factory()->create();

        $this->actingAs($conductor)
            ->get("/conductor/routes/{$route->id}")
            ->assertForbidden();
    }

    public function test_conductor_can_check_in_passenger(): void
    {
        $conductor = User::factory()->create(['role' => 'conductor']);
        $passenger = User::factory()->create(['role' => 'passenger']);
        $route = TrainRoute::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $passenger->id,
            'train_route_id' => $route->id,
        ]);

        $conductor->conductorRoutes()->attach($route->id, [
            'shift_start' => now()->subHour(),
            'shift_end' => now()->addHours(8),
            'cars_scope' => 'Усі вагони',
        ]);

        $this->actingAs($conductor)
            ->put("/conductor/tickets/{$ticket->id}/check-in", [
                'boarding_status' => 'checked',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'boarding_status' => 'checked',
            'checked_by' => $conductor->id,
        ]);
    }

    public function test_ticket_creation_generates_unique_qr_token(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger']);
        $route = TrainRoute::factory()->create();
        $first = Ticket::factory()->create([
            'user_id' => $passenger->id,
            'train_route_id' => $route->id,
        ]);
        $second = Ticket::factory()->create([
            'user_id' => $passenger->id,
            'train_route_id' => $route->id,
        ]);

        $this->assertNotEmpty($first->qr_token);
        $this->assertNotEmpty($second->qr_token);
        $this->assertNotSame($first->qr_token, $second->qr_token);
    }

    public function test_conductor_can_confirm_paid_ticket_by_qr_code(): void
    {
        $conductor = User::factory()->create(['role' => 'conductor']);
        $passenger = User::factory()->create(['role' => 'passenger']);
        $route = TrainRoute::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $passenger->id,
            'train_route_id' => $route->id,
            'status' => 'paid',
        ]);

        $conductor->conductorRoutes()->attach($route->id, [
            'shift_start' => now()->subHour(),
            'shift_end' => now()->addHours(8),
            'cars_scope' => 'Усі вагони',
        ]);

        $this->actingAs($conductor)
            ->get("/conductor/scan/{$ticket->qr_token}")
            ->assertOk()
            ->assertSee($ticket->passenger_full_name);

        $this->actingAs($conductor)
            ->post("/conductor/scan/{$ticket->qr_token}/confirm")
            ->assertRedirect("/conductor/scan/{$ticket->qr_token}");

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'boarding_status' => 'checked',
            'checked_by' => $conductor->id,
        ]);
    }

    public function test_conductor_cannot_confirm_qr_ticket_for_unassigned_route(): void
    {
        $conductor = User::factory()->create(['role' => 'conductor']);
        $passenger = User::factory()->create(['role' => 'passenger']);
        $route = TrainRoute::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $passenger->id,
            'train_route_id' => $route->id,
            'status' => 'paid',
        ]);

        $this->actingAs($conductor)
            ->get("/conductor/scan/{$ticket->qr_token}")
            ->assertForbidden();
    }
}
