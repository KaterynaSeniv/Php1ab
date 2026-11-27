<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TrainRoute;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_no_token_is_provided(): void
    {
        $response = $this->deleteJson('/api/v1/tickets/1');

        $response->assertUnauthorized();
    }

    public function test_user_cannot_delete_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $route = TrainRoute::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $owner->id,
            'train_route_id' => $route->id,
        ]);

        Sanctum::actingAs($intruder);

        $this->deleteJson("/api/v1/tickets/{$ticket->id}")
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_when_accessing_create_page(): void
    {
        $this->get('/tickets/create')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_cannot_edit_another_users_ticket_via_web(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $route = TrainRoute::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $owner->id,
            'train_route_id' => $route->id,
        ]);

        $this->actingAs($intruder)
            ->get("/tickets/{$ticket->id}/edit")
            ->assertForbidden();
    }

    public function test_routes_and_services_pages_are_public_but_admin_actions_are_hidden(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger']);

        $this->get('/routes')->assertOk()->assertDontSee('Додати рейс');
        $this->get('/services')->assertOk()->assertDontSee('Додати послугу');

        $this->actingAs($passenger)
            ->get('/routes/create')
            ->assertForbidden();

        $this->actingAs($passenger)
            ->get('/services/create')
            ->assertForbidden();
    }

    public function test_admin_can_manage_routes_and_services(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/routes/create')->assertOk();
        $this->actingAs($admin)->get('/services/create')->assertOk();
    }

    public function test_authenticated_user_can_view_and_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Старе Ім’я',
            'email' => 'old@example.com',
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Старе Ім’я');

        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Нове Ім’я',
                'email' => 'new@example.com',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Нове Ім’я',
            'email' => 'new@example.com',
        ]);
    }

    public function test_public_api_can_read_tickets_but_hides_passenger_name(): void
    {
        $owner = User::factory()->create(['role' => 'passenger']);
        $route = TrainRoute::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $owner->id,
            'train_route_id' => $route->id,
            'passenger_full_name' => 'Приховане ПІБ',
        ]);

        $this->getJson('/api/v1/tickets')
            ->assertOk()
            ->assertJsonFragment(['id' => $ticket->id])
            ->assertJsonFragment(['passenger_full_name' => 'Приховано']);

        $this->getJson("/api/v1/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonFragment(['passenger_full_name' => 'Приховано']);
    }

    public function test_owner_can_see_full_passenger_name_in_api(): void
    {
        $owner = User::factory()->create(['role' => 'passenger']);
        $route = TrainRoute::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $owner->id,
            'train_route_id' => $route->id,
            'passenger_full_name' => 'Повне ПІБ Власника',
        ]);

        Sanctum::actingAs($owner);

        $this->getJson("/api/v1/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonFragment(['passenger_full_name' => 'Повне ПІБ Власника']);
    }

    public function test_database_seeder_creates_roles_routes_services_and_ticket_links(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'passenger@example.com',
            'role' => 'passenger',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'conductor@example.com',
            'role' => 'conductor',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);
        $this->assertDatabaseHas('train_routes', ['train_number' => '043К']);
        $this->assertDatabaseHas('services', ['name' => 'Постільна білизна']);

        $ticket = Ticket::where('passenger_full_name', 'Тестовий Пасажир')->firstOrFail();
        $this->assertNotEmpty($ticket->services);
    }
}
