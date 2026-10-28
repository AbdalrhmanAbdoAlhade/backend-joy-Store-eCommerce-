<?php
// app/Providers/EventServiceProvider.php
namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\LowStockDetected;
use App\Events\BackInStock;
use App\Events\TicketReplied;
use App\Listeners\SendOrderConfirmationNotification;
use App\Listeners\SendOrderStatusNotification;
use App\Listeners\EarnLoyaltyPoints;
use App\Listeners\NotifyAdminsLowStock;
use App\Listeners\NotifyUsersBackInStock;
use App\Listeners\SendTicketReplyNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderCreated::class => [
            SendOrderConfirmationNotification::class,
        ],
        OrderStatusChanged::class => [
            SendOrderStatusNotification::class,
            EarnLoyaltyPoints::class,
        ],
        LowStockDetected::class => [
            NotifyAdminsLowStock::class,
        ],
        BackInStock::class => [
            NotifyUsersBackInStock::class,
        ],
        TicketReplied::class => [
            SendTicketReplyNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
