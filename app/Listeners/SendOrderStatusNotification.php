<?php
// app/Listeners/SendOrderStatusNotification.php
namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderStatusNotification implements ShouldQueue
{
    public function handle(OrderStatusChanged $event): void
    {
        if ($event->order->user) {
            $event->order->user->notify(
                new OrderStatusUpdatedNotification($event->order, $event->toStatus)
            );
        }
    }
}
