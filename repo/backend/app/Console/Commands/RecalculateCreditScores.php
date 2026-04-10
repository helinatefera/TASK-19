<?php

namespace App\Console\Commands;

use App\Enums\RestrictionLevel;
use App\Models\BusinessParameter;
use App\Models\CreditScore;
use Illuminate\Console\Command;

class RecalculateCreditScores extends Command
{
    protected $signature = 'risk:recalculate-credit-scores';

    protected $description = 'Recalculate all user credit scores based on current penalty parameters';

    private const BASE_SCORE = 1000;

    private const DEFAULT_NO_SHOW_PENALTY = 50;

    private const DEFAULT_CHARGEBACK_PENALTY = 100;

    private const DEFAULT_REFUND_PENALTY = 30;

    private const DEFAULT_VIOLATION_PENALTY = 75;

    private const DEFAULT_GRAY_THRESHOLD = 600;

    private const DEFAULT_BLACK_THRESHOLD = 300;

    private const DEFAULT_BLACKLIST_DURATION_DAYS = 90;

    public function handle(): int
    {
        $noShowPenalty = $this->getParameter('credit_score_no_show_penalty', self::DEFAULT_NO_SHOW_PENALTY);
        $chargebackPenalty = $this->getParameter('credit_score_chargeback_penalty', self::DEFAULT_CHARGEBACK_PENALTY);
        $refundPenalty = $this->getParameter('credit_score_refund_penalty', self::DEFAULT_REFUND_PENALTY);
        $violationPenalty = $this->getParameter('credit_score_violation_penalty', self::DEFAULT_VIOLATION_PENALTY);
        $grayThreshold = $this->getParameter('credit_score_gray_threshold', self::DEFAULT_GRAY_THRESHOLD);
        $blackThreshold = $this->getParameter('credit_score_black_threshold', self::DEFAULT_BLACK_THRESHOLD);
        $blacklistDurationDays = $this->getParameter('blacklist_duration_days', self::DEFAULT_BLACKLIST_DURATION_DAYS);

        $updated = 0;

        CreditScore::query()->chunk(200, function ($scores) use (
            $noShowPenalty,
            $chargebackPenalty,
            $refundPenalty,
            $violationPenalty,
            $grayThreshold,
            $blackThreshold,
            $blacklistDurationDays,
            &$updated,
        ) {
            foreach ($scores as $creditScore) {
                $score = self::BASE_SCORE
                    - ($creditScore->no_show_count * $noShowPenalty)
                    - ($creditScore->chargeback_count * $chargebackPenalty)
                    - ($creditScore->refund_count * $refundPenalty)
                    - ($creditScore->violation_count * $violationPenalty);

                $score = max(0, $score);

                $restrictionLevel = RestrictionLevel::None;
                $restrictionUntil = null;

                if ($score < $blackThreshold) {
                    $restrictionLevel = RestrictionLevel::Black;
                    $restrictionUntil = now()->addDays($blacklistDurationDays);
                } elseif ($score < $grayThreshold) {
                    $restrictionLevel = RestrictionLevel::Gray;
                }

                $creditScore->update([
                    'score' => $score,
                    'restriction_level' => $restrictionLevel,
                    'restriction_until' => $restrictionUntil,
                ]);

                $updated++;
            }
        });

        $this->info("Recalculated {$updated} credit score(s).");

        return self::SUCCESS;
    }

    private function getParameter(string $key, int $default): int
    {
        $param = BusinessParameter::where('key', $key)->first();

        return $param ? (int) $param->getTypedValue() : $default;
    }
}
