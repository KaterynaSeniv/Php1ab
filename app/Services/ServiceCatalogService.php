<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceCatalogService
{
    public function getPaginatedList(): LengthAwarePaginator
    {
        return Service::query()
            ->withCount('tickets')
            ->orderBy('name')
            ->paginate(10);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createService(array $data): Service
    {
        return DB::transaction(fn (): Service => Service::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) ?: 'service-'.time(),
            'price' => (float) $data['price'],
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateService(Service $service, array $data): Service
    {
        return DB::transaction(function () use ($service, $data): Service {
            $service->update([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) ?: $service->slug,
                'price' => (float) $data['price'],
            ]);

            return $service;
        });
    }

    public function deleteService(Service $service): void
    {
        DB::transaction(function () use ($service): void {
            $service->delete();
        });
    }
}
