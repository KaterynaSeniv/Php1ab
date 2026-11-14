<?php

declare(strict_types=1);

namespace App\Models;

class Passenger extends AbstractEntity
{
    public function __construct(
        string $id,
        string $createdAt,
        private string $name,
        private string $email,
        private string $phone
    ) {
        parent::__construct($id, $createdAt);
    }

    public function getDetails(): string
    {
        return "Пасажир #{$this->id} ({$this->name}), Email: {$this->email}, Тел: {$this->phone}, зареєстрований: {$this->createdAt}";
    }
}