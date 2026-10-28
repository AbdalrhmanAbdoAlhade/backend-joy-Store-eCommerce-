<?php
// app/Enums/InventoryMovementType.php
namespace App\Enums;

enum InventoryMovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Return = 'return';
    case Adjustment = 'adjustment';
    case Damaged = 'damaged';
    case Reservation = 'reservation';
    case Release = 'release';
}
