<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\TicketService;
use PHPUnit\Framework\TestCase;

class TicketServiceTest extends TestCase
{
    public function test_calculates_total_ticket_price_with_selected_services(): void
    {
        $service = new TicketService;

        $this->assertSame(450.5, $service->calculateTotalPrice(400.25, [50.25]));
    }

    public function test_ticket_without_additional_services_keeps_the_base_price(): void
    {
        $service = new TicketService;

        $this->assertSame(400.25, $service->calculateTotalPrice(400.25, []));
    }
}
