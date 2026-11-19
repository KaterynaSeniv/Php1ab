<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TrainRoute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_without_partner_header_is_blocked_by_middleware(): void
    {
        $response = $this->postJson('/tickets', [
            'passenger_full_name' => 'Іваненко Іван',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'access_denied',
            ]);
    }

    public function test_request_with_valid_header_passes_middleware(): void
    {
        $route = TrainRoute::factory()->create();

        $response = $this->withHeaders([
            'X-Partner-Key' => 'RAILWAY-PARTNER-2026',
        ])->postJson('/tickets', [
            'train_route_id' => $route->id,
            'passenger_full_name' => 'Іваненко Іван Іванович',
            'car_number' => 4,
            'seat_number' => 11,
            'price' => 520.00,
        ]);

        $response->assertStatus(201);
        $response->assertHeader('X-Railway-Security', 'Verified');
    }
}
