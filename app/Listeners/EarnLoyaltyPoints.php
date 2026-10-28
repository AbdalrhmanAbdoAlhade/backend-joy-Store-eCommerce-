<?php
// app/Listeners/EarnLoyaltyPoints.php
namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Enums\OrderStatus;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Contracts\Queue\ShouldQueue;

class EarnLoyaltyPoints implements ShouldQueue
{
    public function __construct(protected LoyaltyService $loyalty) {}

    public function handle(OrderStatusChanged $event): void
    {
        if ($event->toStatus === OrderStatus::Delivered) {
            $this->loyalty->earnFromOrder($event->order);
        }
    }
}
