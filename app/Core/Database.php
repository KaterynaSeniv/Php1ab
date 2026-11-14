<?php

declare(strict_types=1);

namespace App\Core;

use LogicException;

class Database
{
    private static ?self $instance = null;

    private function __construct()
    {
        echo "Підключення до БД створено<br>\n";
    }

    private function __clone()
    {
        throw new LogicException('Клонування екземпляра Singleton заборонено.');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}