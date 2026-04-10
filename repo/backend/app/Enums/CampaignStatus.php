<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Fundraising = 'fundraising';
    case Success = 'success';
    case Failure = 'failure';
    case Closed = 'closed';
}
