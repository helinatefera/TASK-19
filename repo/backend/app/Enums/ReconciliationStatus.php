<?php

namespace App\Enums;

enum ReconciliationStatus: string
{
    case Pending = 'pending';
    case Matched = 'matched';
    case Discrepancy = 'discrepancy';
    case Resolved = 'resolved';
}
