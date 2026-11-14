<?php

declare(strict_types=1);

namespace App\Tasks;

class Product
{
    public function __construct(
        public readonly int $id,
        public string $name,
        public float $price
    ) {}
}