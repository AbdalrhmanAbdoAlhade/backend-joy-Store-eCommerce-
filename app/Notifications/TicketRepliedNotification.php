<?php
// app/Notifications/TicketRepliedNotification.php
namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketRepliedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketMessage $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('رد جديد على التذكرة #' . $this->ticket->ticket_number)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('تم إضافة رد جديد على تذكرتك:')
            ->line($this->ticket->subject)
            ->line('الرد: ' . \Str::limit($this->message->message, 100))
            ->action('عرض التذكرة', url('/tickets/' . $this->ticket->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket_replied',
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'message' => 'رد جديد على تذكرتك #' . $this->ticket->ticket_number,
        ];
    }
}
