<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canViewPassenger = $request->user()
            && in_array($request->user()->role, ['admin', 'conductor'], true);
        $canViewPassenger = $canViewPassenger
            || ($request->user() && $request->user()->id === $this->user_id);

        return [
            'id' => $this->id,
            'owner_id' => $request->user()
                ? $this->when($canViewPassenger, $this->user_id)
                : $this->when(false, $this->user_id),
            'train_route' => $this->whenLoaded('trainRoute', fn () => [
                'id' => $this->trainRoute->id,
                'train_number' => $this->trainRoute->train_number,
                'departure_station' => $this->trainRoute->departure_station,
                'arrival_station' => $this->trainRoute->arrival_station,
                'departure_time' => $this->trainRoute->departure_time?->toISOString(),
                'base_price' => (float) $this->trainRoute->base_price,
            ]),
            'passenger_full_name' => $canViewPassenger
                ? $this->passenger_full_name
                : 'Приховано',
            'car_number' => $this->car_number,
            'seat_number' => $this->seat_number,
            'price' => (float) $this->price,
            'status' => $this->status,
            'services' => $this->whenLoaded('services', fn () => $this->services->map(fn ($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'price' => (float) $service->price,
            ])),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
