<?php

namespace App\Enums;

enum AfterSalesType: string
{
    case Refund = 'refund';
    case Exchange = 'exchange';
    case Complaint = 'complaint';
    case Other = 'other';
}
