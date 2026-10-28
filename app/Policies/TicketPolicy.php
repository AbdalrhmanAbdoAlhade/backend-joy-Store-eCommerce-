<?php
// app/Policies/TicketPolicy.php
namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // العميل يشوف تذاكره، الموظف يشوف حسب صلاحية
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id || $user->can('tickets.reply');
    }

    public function reply(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->user_id || $user->can('tickets.reply');
    }

    public function assign(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
