<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\LoggerInterface;

class FileLogger implements LoggerInterface
{
    public function __construct(
        private string $filePath = __DIR__ . '/../../app.log'
    ) {}

    public function log(string $message): void
    {
        $entry = '[' . date('Y-m-d H:i:s') . "] [FILE LOG] {$message}" . PHP_EOL;
        file_put_contents($this->filePath, $entry, FILE_APPEND);
        echo "[FILE LOG записано у файл] {$message}<br>\n";
    }
}