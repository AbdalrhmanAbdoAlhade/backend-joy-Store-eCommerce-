<?php
// app/Listeners/NotifyAdminsLowStock.php
namespace App\Listeners;

use App\Events\LowStockDetected;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminsLowStock implements ShouldQueue
{
    public function handle(LowStockDetected $event): void
    {
        $admins = User::role(['admin', 'employee'])
            ->permission('inventory.manage')
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new LowStockNotification($event->variant));
        }
    }
}
