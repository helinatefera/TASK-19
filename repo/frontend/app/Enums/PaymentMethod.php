<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case CardOnFile = 'card_on_file';
}
