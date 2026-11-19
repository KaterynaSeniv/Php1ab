<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'train_route_id',
        'passenger_full_name',
        'car_number',
        'seat_number',
        'price',
        'status',
    ];

    protected $casts = [
        'car_number' => 'integer',
        'seat_number' => 'integer',
        'price' => 'float',
    ];

    public function trainRoute(): BelongsTo
    {
        return $this->belongsTo(TrainRoute::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->withTimestamps();
    }
}
