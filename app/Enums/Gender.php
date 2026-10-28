<?php
// app/Enums/Gender.php
namespace App\Enums;

enum Gender: string
{
    case Boys = 'boys';
    case Girls = 'girls';
    case Unisex = 'unisex';
}
