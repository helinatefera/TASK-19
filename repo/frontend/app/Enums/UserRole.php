<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Creator = 'creator';
    case Moderator = 'moderator';
    case Staff = 'staff';
    case Admin = 'admin';
}
