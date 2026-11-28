<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TrainRoute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TrainRouteService
{
    public function getPaginatedList(): LengthAwarePaginator
    {
        return TrainRoute::query()
            ->withCount('tickets')
            ->orderBy('train_number')
            ->paginate(10);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRoute(array $data): TrainRoute
    {
        return DB::transaction(fn (): TrainRoute => TrainRoute::create($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateRoute(TrainRoute $route, array $data): TrainRoute
    {
        return DB::transaction(function () use ($route, $data): TrainRoute {
            $route->update($data);

            return $route;
        });
    }

    public function deleteRoute(TrainRoute $route): void
    {
        DB::transaction(function () use ($route): void {
            $route->delete();
        });
    }
}
