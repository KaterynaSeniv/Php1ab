<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\LoggerInterface;
use App\Models\AbstractEntity;
use Exception;

class EntityService
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function registerEntity(AbstractEntity $entity): void
    {
        $this->logger->log('Оброблено сутність: ' . $entity->getDetails());
    }

    public function calculateTicketTotal(float $basePrice, int $quantity, float $serviceFee = 25.0): float
    {
        $total = ($basePrice * $quantity) + $serviceFee;
        $this->logger->log("Розраховано вартість квитків ({$quantity} шт.): {$total} грн");

        return $total;
    }

    public function applyDiscount(float $price, float $discount): float
    {
        if ($discount < 0 || $discount > 100) {
            $this->logger->log("Помилка розрахунку знижки: {$discount}%");
            throw new Exception('Неприпустиме значення знижки!');
        }

        $finalPrice = $price - ($price * ($discount / 100));
        $this->logger->log("Застосовано знижку {$discount}%. Нова ціна: {$finalPrice} грн");

        return $finalPrice;
    }
}