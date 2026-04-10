<?php

namespace App\Events;

use App\Models\AnomalyFlag;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnomalyDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public AnomalyFlag $anomalyFlag,
    ) {}
}
