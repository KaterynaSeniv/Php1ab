<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\TrainRoute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_tickets_index_pages_render_correctly(): void
    {
        $route = TrainRoute::factory()->create(['train_number' => '043К']);
        Ticket::factory()->create([
            'train_route_id' => $route->id,
            'passenger_full_name' => 'Сенів Катерина Дмитрівна',
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Електронна система продажу залізничних квитків');

        $this->get('/tickets')
            ->assertStatus(200)
            ->assertSee('Сенів Катерина Дмитрівна')
            ->assertSee('043К')
            ->assertSee('Оформити новий квиток');
    }

    public function test_create_ticket_form_validates_invalid_input(): void
    {
        $response = $this->from('/tickets/create')->post('/tickets', [
            'train_route_id' => '',
            'passenger_full_name' => '',
            'car_number' => 99,
            'seat_number' => 0,
        ]);

        $response->assertRedirect('/tickets/create');
        $response->assertSessionHasErrors([
            'train_route_id',
            'passenger_full_name',
            'car_number',
            'seat_number',
        ]);
    }

    public function test_user_can_create_and_update_ticket_via_web_forms(): void
    {
        $route = TrainRoute::factory()->create();
        $service1 = Service::factory()->create();
        $service2 = Service::factory()->create();

        $createResponse = $this->post('/tickets', [
            'train_route_id' => $route->id,
            'passenger_full_name' => 'Тестовий Пасажир Blade',
            'car_number' => 3,
            'seat_number' => 12,
            'services' => [$service1->id, $service2->id],
        ]);

        $createResponse->assertSessionHasNoErrors();
        $createResponse->assertSessionHas('success');

        $ticket = Ticket::where('passenger_full_name', 'Тестовий Пасажир Blade')->firstOrFail();
        $this->assertEquals('booked', $ticket->status);

        $updateResponse = $this->put("/tickets/{$ticket->id}", [
            'train_route_id' => $route->id,
            'passenger_full_name' => 'Оновлений Пасажир Blade',
            'car_number' => 4,
            'seat_number' => 15,
            'status' => 'booked',
            'services' => [$service1->id],
        ]);

        $updateResponse->assertRedirect('/tickets');
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'passenger_full_name' => 'Оновлений Пасажир Blade',
            'car_number' => 4,
        ]);
    }

    public function test_cannot_book_an_already_taken_seat(): void
    {
        $route = TrainRoute::factory()->create([
            'car_count' => 4,
            'seats_per_car' => 36,
        ]);

        Ticket::factory()->create([
            'train_route_id' => $route->id,
            'car_number' => 2,
            'seat_number' => 8,
        ]);

        $response = $this->from('/tickets/create')->post('/tickets', [
            'train_route_id' => $route->id,
            'passenger_full_name' => 'Інший Пасажир',
            'car_number' => 2,
            'seat_number' => 8,
        ]);

        $response->assertRedirect('/tickets/create');
        $response->assertSessionHasErrors('seat_number');
        $this->assertEquals(1, Ticket::where('train_route_id', $route->id)->count());
    }
}
