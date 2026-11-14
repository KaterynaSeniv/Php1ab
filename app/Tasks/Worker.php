<?php

declare(strict_types=1);

namespace App\Tasks;

class Worker extends Employee
{
    public function __construct(
        string $name,
        float $baseRate,
        protected int $hoursWorked = 160
    ) {
        parent::__construct($name, $baseRate);
    }

    public function calculateSalary(): float
    {
        return $this->baseRate * $this->hoursWorked;
    }
}