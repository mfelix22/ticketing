<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestCancellationRequested extends Notification
{
    use Queueable;

    protected $ticket;
    protected $requester;

    public function __construct(Ticket $ticket, User $requester)
    {
        $this->ticket = $ticket;
        $this->requester = $requester;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'request_number' => $this->ticket->request_number,
            'title' => $this->ticket->title,
            'requester' => $this->requester->name,
            'message' => "Cancellation requested for {$this->ticket->request_number} by {$this->requester->name}",
            'category' => 'cancel_request',
            'url' => route('tickets.show', $this->ticket->id),
        ];
    }
}
