<?php
// app/Events/TicketReplied.php
namespace App\Events;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplied
{
    use Dispatchable, SerializesModels;

    public function __construct(public Ticket $ticket, public TicketMessage $message) {}
}
