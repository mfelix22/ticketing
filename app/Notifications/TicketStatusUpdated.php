<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdated extends Notification // Removed ShouldQueue for immediate notifications
{
    use Queueable;

    protected $ticket;
    protected $oldStatus;
    protected $newStatus;

    public function __construct(Ticket $ticket, string $oldStatus, string $newStatus)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable)
    {
        return ['database']; // Send to database for bell notifications
    }

    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'request_number' => $this->ticket->request_number,
            'title' => $this->ticket->title,
            'message' => "{$this->ticket->request_number} status: {$this->oldStatus} → {$this->newStatus}",
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'url' => route('tickets.show', $this->ticket->id),
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("{$this->ticket->request_number} Status Updated")
            ->line("The status of {$this->ticket->request_number} has been updated.")
            ->line("From: {$this->oldStatus}")
            ->line("To: {$this->newStatus}")
            ->action('View Ticket', route('tickets.show', $this->ticket))
            ->line('Thank you for using our ticketing system!');
    }
}
