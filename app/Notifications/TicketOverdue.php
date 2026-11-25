<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;

class TicketOverdue extends Notification
{
    use Queueable;

    protected $ticket;
    protected $hoursOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, int $hoursOverdue)
    {
        $this->ticket = $ticket;
        $this->hoursOverdue = $hoursOverdue;
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
            'title' => $this->ticket->title,
            'status' => $this->ticket->status->name,
            'priority' => $this->ticket->priority->name,
            'due_date' => $this->ticket->due_date,
            'hours_overdue' => $this->hoursOverdue,
            'message' => "Ticket #{$this->ticket->id} is {$this->hoursOverdue} hours overdue",
            'url' => route('tickets.show', $this->ticket->id),
        ];
    }
}
