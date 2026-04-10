<?php

namespace App\Enums;

enum FulfillmentType: string
{
    case Digital = 'digital';
    case Physical = 'physical';
    case Event = 'event';
}
