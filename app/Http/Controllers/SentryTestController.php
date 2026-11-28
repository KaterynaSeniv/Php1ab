<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use RuntimeException;

class SentryTestController extends Controller
{
    public function __invoke(): never
    {
        abort_unless(app()->isLocal(), 404);

        throw new RuntimeException('Тестовий виняток для перевірки інтеграції Sentry (ЛР №9)');
    }
}
