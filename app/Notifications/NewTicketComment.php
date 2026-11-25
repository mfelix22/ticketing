<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\RequestComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketComment extends Notification // Removed ShouldQueue for immediate notifications
{
    use Queueable;

    protected $ticket;
    protected $comment;

    public function __construct(Ticket $ticket, RequestComment $comment)
    {
        $this->ticket = $ticket;
        $this->comment = $comment;
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
            'message' => "New comment on {$this->ticket->request_number}",
            'comment_preview' => \Illuminate\Support\Str::limit($this->comment->comment, 100),
            'commenter_name' => $this->comment->user->name,
            'url' => route('tickets.show', $this->ticket->id),
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("New Comment on {$this->ticket->request_number}")
            ->line("A new comment has been added to {$this->ticket->request_number}:")
            ->line("\"{$this->comment->comment}\"")
            ->line("Comment by: {$this->comment->user->name}")
            ->action('View Ticket', route('tickets.show', $this->ticket))
            ->line('Thank you for using our ticketing system!');
    }
}
