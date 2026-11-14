<?php

declare(strict_types=1);

namespace App\Tasks;

class CompanyDirector extends DepartmentHead
{
    public function __construct(
        string $name,
        float $baseRate,
        int $hoursWorked = 160,
        float $shopBonus = 5000.0,
        float $departmentAllowance = 8000.0,
        protected float $executiveMultiplier = 1.5
    ) {
        parent::__construct($name, $baseRate, $hoursWorked, $shopBonus, $departmentAllowance);
    }

    public function calculateSalary(): float
    {
        return parent::calculateSalary() * $this->executiveMultiplier;
    }
}