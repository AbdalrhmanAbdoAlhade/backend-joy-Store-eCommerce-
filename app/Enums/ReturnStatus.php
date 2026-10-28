<?php
// app/Enums/ReturnStatus.php
namespace App\Enums;

enum ReturnStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Received = 'received';
    case Refunded = 'refunded';
}
