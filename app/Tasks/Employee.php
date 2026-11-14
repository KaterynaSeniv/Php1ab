<?php

declare(strict_types=1);

namespace App\Tasks;

abstract class Employee
{
    public function __construct(
        protected string $name,
        protected float $baseRate
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function calculateSalary(): float;
}