<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\AbstractEntity;
use App\Models\Passenger;
use App\Models\Ticket;
use InvalidArgumentException;

class EntityFactory
{
    public static function createEntity(string $type, array $data): AbstractEntity
    {
        return match (strtolower($type)) {
            'ticket' => new Ticket(
                id: (string) ($data['id'] ?? '0'),
                createdAt: (string) ($data['createdAt'] ?? date('Y-m-d H:i:s')),
                passengerFullName: (string) ($data['passengerFullName'] ?? 'Невідомий пасажир'),
                carNumber: (int) ($data['carNumber'] ?? 1),
                seatNumber: (int) ($data['seatNumber'] ?? 1),
                price: (float) ($data['price'] ?? 0.0)
            ),
            'passenger' => new Passenger(
                id: (string) ($data['id'] ?? '0'),
                createdAt: (string) ($data['createdAt'] ?? date('Y-m-d H:i:s')),
                name: (string) ($data['name'] ?? 'Гість'),
                email: (string) ($data['email'] ?? 'no-reply@railway.ua'),
                phone: (string) ($data['phone'] ?? '+380000000000')
            ),
            default => throw new InvalidArgumentException("Невідомий тип сутності: {$type}"),
        };
    }
}