<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TrainRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_search_filters_routes_by_route_and_date(): void
    {
        TrainRoute::factory()->create([
            'train_number' => '043К',
            'departure_station' => 'Київ-Пасажирський',
            'arrival_station' => 'Львів',
            'departure_time' => now()->addDays(2)->setTime(10, 30),
        ]);
        TrainRoute::factory()->create([
            'train_number' => '081К',
            'departure_station' => 'Одеса',
            'arrival_station' => 'Харків',
            'departure_time' => now()->addDays(2)->setTime(14, 15),
        ]);

        $response = $this->get('/search?from='.urlencode('Київ').'&to='.urlencode('Львів').'&date='.now()->addDays(2)->format('Y-m-d'));

        $response->assertOk()
            ->assertSee('043К')
            ->assertDontSee('081К');
    }

    public function test_home_search_rejects_invalid_date(): void
    {
        $response = $this->get('/search?date=not-a-date');

        $response->assertSessionHasErrors('date');
    }

    public function test_routes_index_page_displays_entities(): void
    {
        TrainRoute::factory()->create([
            'train_number' => '043К',
            'departure_station' => 'Київ-Пасажирський',
            'arrival_station' => 'Івано-Франківськ',
            'base_price' => 350.00,
            'car_count' => 10,
            'seats_per_car' => 54,
            'car_type' => 'platskart',
        ]);

        $response = $this->get('/routes');

        $response->assertStatus(200)
            ->assertSee('043К')
            ->assertSee('Київ-Пасажирський')
            ->assertSee('Івано-Франківськ')
            ->assertDontSee('Додати рейс');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Переглянути послуги')
            ->assertSee('Додаткові послуги')
            ->assertDontSee('Залізничні квитки')
            ->assertDontSee('Рейси поїздів (1:М)')
            ->assertDontSee('Додаткові послуги (М:М)');
    }

    public function test_create_route_form_validates_invalid_input(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->from('/routes/create')->post('/routes', [
            'train_number' => '',
            'departure_station' => '',
            'arrival_station' => '',
            'departure_time' => '',
            'base_price' => '',
            'car_count' => '',
            'seats_per_car' => '',
            'car_type' => '',
        ]);

        $response->assertRedirect('/routes/create');
        $response->assertSessionHasErrors([
            'train_number',
            'departure_station',
            'arrival_station',
            'departure_time',
            'base_price',
            'car_count',
            'seats_per_car',
            'car_type',
        ]);
    }

    public function test_user_can_create_and_update_route_via_web_forms(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $createResponse = $this->actingAs($admin)->post('/routes', [
            'train_number' => '081К',
            'departure_station' => 'Львів',
            'arrival_station' => 'Одеса',
            'departure_time' => now()->addDay()->format('Y-m-d\TH:i'),
            'base_price' => 350.00,
            'car_count' => 10,
            'seats_per_car' => 54,
            'car_type' => 'platskart',
        ]);

        $createResponse->assertRedirect('/routes');
        $createResponse->assertSessionHas('success');

        $this->assertDatabaseHas('train_routes', [
            'train_number' => '081К',
            'departure_station' => 'Львів',
            'arrival_station' => 'Одеса',
        ]);

        $route = TrainRoute::where('train_number', '081К')->firstOrFail();

        $updateResponse = $this->actingAs($admin)->put("/routes/{$route->id}", [
            'train_number' => '081К',
            'departure_station' => 'Львів',
            'arrival_station' => 'Харків',
            'departure_time' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'base_price' => 400.00,
            'car_count' => 12,
            'seats_per_car' => 50,
            'car_type' => 'coupe',
        ]);

        $updateResponse->assertRedirect('/routes');
        $this->assertDatabaseHas('train_routes', [
            'id' => $route->id,
            'arrival_station' => 'Харків',
        ]);
    }
}
