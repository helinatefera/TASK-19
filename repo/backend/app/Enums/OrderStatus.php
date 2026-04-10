<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Fulfilled = 'fulfilled';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case AfterSales = 'after_sales';
}
