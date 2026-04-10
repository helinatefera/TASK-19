<?php

namespace App\Enums;

enum ReviewSide: string
{
    case UserToCreator = 'user_to_creator';
    case CreatorToUser = 'creator_to_user';
}
