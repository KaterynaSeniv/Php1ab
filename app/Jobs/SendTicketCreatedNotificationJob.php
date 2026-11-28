<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class SendTicketCreatedNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function handle(): void
    {
        $ticket = $this->ticket->loadMissing(['trainRoute', 'user']);

        Mail::raw(
            'Квиток №'.$ticket->id.' на поїзд '.$ticket->trainRoute->train_number
                .' успішно заброньовано. Місце: вагон '.$ticket->car_number
                .', місце '.$ticket->seat_number.'.',
            function (Message $message) use ($ticket): void {
                $message
                    ->to($ticket->user->email)
                    ->subject('Бронювання квитка №'.$ticket->id);
            },
        );
    }
}
