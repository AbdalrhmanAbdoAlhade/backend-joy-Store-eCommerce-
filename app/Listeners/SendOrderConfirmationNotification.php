<?php
// app/Listeners/SendOrderConfirmationNotification.php
namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\OrderConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderConfirmationNotification implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        if ($event->order->user) {
            $event->order->user->notify(new OrderConfirmedNotification($event->order));
        }
    }
}
