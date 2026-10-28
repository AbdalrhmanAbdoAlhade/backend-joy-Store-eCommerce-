<?php
// app/Listeners/SendTicketReplyNotification.php
namespace App\Listeners;

use App\Events\TicketReplied;
use App\Notifications\TicketRepliedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTicketReplyNotification implements ShouldQueue
{
    public function handle(TicketReplied $event): void
    {
        // لو الرد من الموظف → أبلغ العميل
        if ($event->message->is_staff) {
            $event->ticket->user->notify(
                new TicketRepliedNotification($event->ticket, $event->message)
            );
        }
        // لو الرد من العميل → أبلغ الموظف المعين أو الأدمن
        else {
            $assignee = $event->ticket->assignee;
            if ($assignee) {
                $assignee->notify(
                    new TicketRepliedNotification($event->ticket, $event->message)
                );
            }
        }
    }
}
