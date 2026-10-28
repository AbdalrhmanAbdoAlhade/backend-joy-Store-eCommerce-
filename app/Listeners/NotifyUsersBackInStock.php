<?php
// app/Listeners/NotifyUsersBackInStock.php
namespace App\Listeners;

use App\Events\BackInStock;
use App\Models\BackInStockNotification;
use App\Notifications\ProductBackInStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyUsersBackInStock implements ShouldQueue
{
    public function handle(BackInStock $event): void
    {
        $notifications = BackInStockNotification::where('product_variant_id', $event->variant->id)
            ->where('notified', false)
            ->with('user')
            ->get();

        foreach ($notifications as $notification) {
            if ($notification->user) {
                $notification->user->notify(
                    new ProductBackInStockNotification($event->variant)
                );
                $notification->update([
                    'notified' => true,
                    'notified_at' => now(),
                ]);
            }
        }
    }
}
