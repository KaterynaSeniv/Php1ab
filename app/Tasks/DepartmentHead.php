<?php

declare(strict_types=1);

namespace App\Tasks;

class DepartmentHead extends ShopManager
{
    public function __construct(
        string $name,
        float $baseRate,
        int $hoursWorked = 160,
        float $shopBonus = 5000.0,
        protected float $departmentAllowance = 8000.0
    ) {
        parent::__construct($name, $baseRate, $hoursWorked, $shopBonus);
    }

    public function calculateSalary(): float
    {
        return parent::calculateSalary() + $this->departmentAllowance;
    }
}