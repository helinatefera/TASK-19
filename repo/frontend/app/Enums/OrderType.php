<?php

namespace App\Enums;

enum OrderType: string
{
    case Contribution = 'contribution';
    case Reservation = 'reservation';
}
