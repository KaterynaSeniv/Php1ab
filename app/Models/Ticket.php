<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket): void {
            if (! $ticket->qr_token) {
                do {
                    $ticket->qr_token = Str::random(64);
                } while (static::where('qr_token', $ticket->qr_token)->exists());
            }
        });
    }

    protected $fillable = [
        'qr_token',
        'user_id',
        'train_route_id',
        'passenger_full_name',
        'car_number',
        'seat_number',
        'price',
        'status',
        'boarding_status',
        'checked_at',
        'checked_by',
    ];

    protected $casts = [
        'car_number' => 'integer',
        'seat_number' => 'integer',
        'price' => 'float',
        'checked_at' => 'datetime',
    ];

    public function trainRoute(): BelongsTo
    {
        return $this->belongsTo(TrainRoute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->withTimestamps();
    }
}
