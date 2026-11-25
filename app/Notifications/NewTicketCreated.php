<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;

class NewTicketCreated extends Notification
{
    use Queueable;

    protected $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'request_number' => $this->ticket->request_number,
            'title' => $this->ticket->title,
            'department' => $this->ticket->department->name,
            'priority' => $this->ticket->priority->name,
            'requester' => $this->ticket->requester->name,
            'message' => "New ticket created: {$this->ticket->title}",
            'url' => route('tickets.show', $this->ticket->id),
        ];
    }
}
