<?php

declare(strict_types=1);

namespace App\Tasks;

class ShopManager extends Worker
{
    public function __construct(
        string $name,
        float $baseRate,
        int $hoursWorked = 160,
        protected float $shopBonus = 5000.0
    ) {
        parent::__construct($name, $baseRate, $hoursWorked);
    }

    public function calculateSalary(): float
    {
        return parent::calculateSalary() + $this->shopBonus;
    }
}