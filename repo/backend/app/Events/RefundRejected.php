<?php

namespace App\Events;

use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefundRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public RefundRequest $refundRequest,
        public User $reviewer,
    ) {}
}
