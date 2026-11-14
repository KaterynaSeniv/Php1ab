<?php

declare(strict_types=1);

namespace App\Models;

use App\Interfaces\ExportableInterface;
use App\Traits\JsonExportTrait;

class Ticket extends AbstractEntity implements ExportableInterface
{
    use JsonExportTrait;

    public function __construct(
        string $id,
        string $createdAt,
        private string $passengerFullName,
        private int $carNumber,
        private int $seatNumber,
        private float $price
    ) {
        parent::__construct($id, $createdAt);
    }

    public function getDetails(): string
    {
        return "Квиток #{$this->id} (від {$this->createdAt}): Пасажир {$this->passengerFullName}, вагон {$this->carNumber}, місце {$this->seatNumber}, ціна {$this->price} грн";
    }
}