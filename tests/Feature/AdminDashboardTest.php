<?php

namespace Tests\Feature;

use App\Models\TrainRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_cannot_open_admin_dashboard(): void
    {
        $passenger = User::factory()->create(['role' => 'passenger']);

        $this->actingAs($passenger)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_can_view_dashboard_statistics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        TrainRoute::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Адміністративна панель')
            ->assertSee('Користувачі та ролі')
            ->assertSee('Призначення провідників');
    }

    public function test_admin_can_change_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'passenger']);

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/role", [
                'role' => 'conductor',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'conductor',
        ]);
    }

    public function test_admin_can_assign_conductor_to_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $conductor = User::factory()->create(['role' => 'conductor']);
        $route = TrainRoute::factory()->create();

        $this->actingAs($admin)
            ->post("/admin/routes/{$route->id}/conductors", [
                'conductor_id' => $conductor->id,
                'shift_start' => now()->subHour()->format('Y-m-d H:i:s'),
                'shift_end' => now()->addHours(8)->format('Y-m-d H:i:s'),
                'cars_scope' => 'Вагони 1–4',
                'instructions' => 'Перевірити посадку.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('conductor_route_assignments', [
            'conductor_id' => $conductor->id,
            'train_route_id' => $route->id,
            'cars_scope' => 'Вагони 1–4',
        ]);
    }

    public function test_admin_cannot_remove_own_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from('/admin/dashboard')
            ->put("/admin/users/{$admin->id}/role", [
                'role' => 'passenger',
            ])
            ->assertRedirect('/admin/dashboard')
            ->assertSessionHasErrors('role');
    }
}
