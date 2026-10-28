<?php
// app/Enums/UserStatus.php
namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Banned = 'banned';
    case Pending = 'pending';
}
