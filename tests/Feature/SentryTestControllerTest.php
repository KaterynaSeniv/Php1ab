<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SentryTestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_health_endpoint_returns_ok_status(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    public function test_sentry_test_route_does_not_throw_outside_the_local_environment(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/sentry/test')
            ->assertNotFound();
    }
}
