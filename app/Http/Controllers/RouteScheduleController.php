<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TrainRoute;
use Illuminate\Http\JsonResponse;

class RouteScheduleController extends Controller
{
    public function index(): JsonResponse
    {
        $routes = TrainRoute::query()->withCount('tickets')->get();

        return response()->json([
            'status' => 'success',
            'data' => $routes,
        ], 200);
    }
}
