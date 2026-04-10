<?php

namespace App\Events;

use App\Models\Dispute;
use App\Models\DisputeDecision;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisputeDecided
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Dispute $dispute,
        public DisputeDecision $decision,
    ) {}
}
