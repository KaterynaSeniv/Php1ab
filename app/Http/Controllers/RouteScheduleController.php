<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class RouteScheduleController extends Controller
{
    public function index(): JsonResponse
    {
        $routes = [
            ['train_number' => '749К', 'route' => 'Київ — Івано-Франківськ', 'free_seats' => 18],
            ['train_number' => '043Л', 'route' => 'Івано-Франківськ — Київ', 'free_seats' => 12],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $routes,
        ], 200);
    }
}
