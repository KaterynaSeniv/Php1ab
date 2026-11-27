<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_index_page_displays_entities(): void
    {
        Service::factory()->create([
            'name' => 'Постільна білизна',
            'price' => 150.00,
        ]);

        $response = $this->get('/services');

        $response->assertStatus(200)
            ->assertSee('Постільна білизна')
            ->assertSee('150.00')
            ->assertDontSee('Додати послугу');
    }

    public function test_create_service_form_validates_invalid_input(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->from('/services/create')->post('/services', [
            'name' => '',
            'price' => 'invalid',
        ]);

        $response->assertRedirect('/services/create');
        $response->assertSessionHasErrors([
            'name',
            'price',
        ]);
    }

    public function test_user_can_create_and_update_service_via_web_forms(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $createResponse = $this->actingAs($admin)->post('/services', [
            'name' => 'Страхування подорожі',
            'price' => 200.50,
        ]);

        $createResponse->assertRedirect('/services');
        $createResponse->assertSessionHas('success');

        $this->assertDatabaseHas('services', [
            'name' => 'Страхування подорожі',
            'price' => 200.50,
        ]);

        $service = Service::where('name', 'Страхування подорожі')->firstOrFail();

        $updateResponse = $this->actingAs($admin)->put("/services/{$service->id}", [
            'name' => 'Страхування подорожі (Преміум)',
            'price' => 350.00,
        ]);

        $updateResponse->assertRedirect('/services');
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Страхування подорожі (Преміум)',
            'price' => 350.00,
        ]);
    }
}
