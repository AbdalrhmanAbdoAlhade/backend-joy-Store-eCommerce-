<?php
// app/Enums/OrderStatus.php
namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case ReadyToShip = 'ready_to_ship';
    case Shipped = 'shipped';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned = 'returned';
    case Refunded = 'refunded';
    case Failed = 'failed';

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::Pending => in_array($newStatus, [self::Confirmed, self::Cancelled, self::Failed]),
            self::Confirmed => in_array($newStatus, [self::Processing, self::Cancelled]),
            self::Processing => in_array($newStatus, [self::ReadyToShip, self::Cancelled]),
            self::ReadyToShip => in_array($newStatus, [self::Shipped, self::Cancelled]),
            self::Shipped => in_array($newStatus, [self::OutForDelivery, self::Delivered]),
            self::OutForDelivery => in_array($newStatus, [self::Delivered]),
            self::Delivered => in_array($newStatus, [self::Returned]),
            self::Cancelled, self::Returned, self::Refunded, self::Failed => false,
            default => false,
        };
    }
}
