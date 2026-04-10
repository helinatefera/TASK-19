<?php

namespace App\Enums;

enum AnomalyType: string
{
    case ExcessiveRefunds = 'excessive_refunds';
    case DuplicateDeviceFingerprint = 'duplicate_device_fingerprint';
    case SuspiciousActivity = 'suspicious_activity';
    case Chargeback = 'chargeback';
}
