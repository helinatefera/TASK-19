<?php

namespace App\Enums;

enum NotificationType: string
{
    case Inbox = 'inbox';
    case Alert = 'alert';
}
