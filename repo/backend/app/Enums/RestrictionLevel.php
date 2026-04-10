<?php

namespace App\Enums;

enum RestrictionLevel: string
{
    case None = 'none';
    case Gray = 'gray';
    case Black = 'black';
}
