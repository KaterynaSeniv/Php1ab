<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'train_number',
        'departure_station',
        'arrival_station',
        'departure_time',
        'base_price',
        'car_count',
        'seats_per_car',
        'car_type',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'base_price' => 'float',
        'car_count' => 'integer',
        'seats_per_car' => 'integer',
    ];

    public function formattedDeparture(): string
    {
        return $this->departure_time?->format('d.m.Y H:i') ?? '—';
    }

    public function datetimeLocalValue(): string
    {
        return $this->departure_time?->format('Y-m-d\TH:i') ?? '';
    }

    public function isSeatTaken(int $carNumber, int $seatNumber, ?int $exceptTicketId = null): bool
    {
        $query = $this->tickets()
            ->where('car_number', $carNumber)
            ->where('seat_number', $seatNumber);

        if ($exceptTicketId !== null) {
            $query->where('id', '!=', $exceptTicketId);
        }

        return $query->exists();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function conductors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conductor_route_assignments', 'train_route_id', 'conductor_id')
            ->withPivot(['shift_start', 'shift_end', 'cars_scope', 'instructions'])
            ->withTimestamps();
    }
}
