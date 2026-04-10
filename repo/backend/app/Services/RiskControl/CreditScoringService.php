<?php

namespace App\Services\RiskControl;

use App\Enums\RestrictionLevel;
use App\Models\BusinessParameter;
use App\Models\CreditScore;
use App\Models\User;

class CreditScoringService
{
    private const BASE_SCORE = 1000;

    private const DEFAULT_NO_SHOW_PENALTY = 50;

    private const DEFAULT_CHARGEBACK_PENALTY = 100;

    private const DEFAULT_REFUND_PENALTY = 30;

    private const DEFAULT_VIOLATION_PENALTY = 75;

    private const DEFAULT_GRAY_THRESHOLD = 600;

    private const DEFAULT_BLACK_THRESHOLD = 300;

    public function evaluate(User $user): CreditScore
    {
        $creditScore = $user->creditScore ?? CreditScore::create([
            'user_id' => $user->id,
            'score' => self::BASE_SCORE,
            'no_show_count' => 0,
            'chargeback_count' => 0,
            'refund_count' => 0,
            'violation_count' => 0,
            'restriction_level' => RestrictionLevel::None,
        ]);

        $score = $this->calculateScore($creditScore);

        $creditScore->update([
            'score' => $score,
        ]);

        $this->applyRestrictions($creditScore->refresh());

        return $creditScore->refresh();
    }

    public function recordNoShow(User $user): void
    {
        $creditScore = $user->creditScore ?? CreditScore::create([
            'user_id' => $user->id,
            'score' => self::BASE_SCORE,
            'no_show_count' => 0,
            'chargeback_count' => 0,
            'refund_count' => 0,
            'violation_count' => 0,
            'restriction_level' => RestrictionLevel::None,
        ]);

        $creditScore->increment('no_show_count');
        $creditScore->increment('violation_count');

        $this->evaluate($user->refresh());
    }

    public function recordChargeback(User $user): void
    {
        $creditScore = $user->creditScore ?? CreditScore::create([
            'user_id' => $user->id,
            'score' => self::BASE_SCORE,
            'no_show_count' => 0,
            'chargeback_count' => 0,
            'refund_count' => 0,
            'violation_count' => 0,
            'restriction_level' => RestrictionLevel::None,
        ]);

        $creditScore->increment('chargeback_count');
        $creditScore->increment('violation_count');

        $this->evaluate($user->refresh());
    }

    public function recordRefund(User $user): void
    {
        $creditScore = $user->creditScore ?? CreditScore::create([
            'user_id' => $user->id,
            'score' => self::BASE_SCORE,
            'no_show_count' => 0,
            'chargeback_count' => 0,
            'refund_count' => 0,
            'violation_count' => 0,
            'restriction_level' => RestrictionLevel::None,
        ]);

        $creditScore->increment('refund_count');

        $this->evaluate($user->refresh());
    }

    public function applyRestrictions(CreditScore $score): void
    {
        $grayThreshold = $this->getParameter('credit_score_gray_threshold', self::DEFAULT_GRAY_THRESHOLD);
        $blackThreshold = $this->getParameter('credit_score_black_threshold', self::DEFAULT_BLACK_THRESHOLD);

        $level = RestrictionLevel::None;

        if ($score->score < $blackThreshold) {
            $level = RestrictionLevel::Black;
        } elseif ($score->score < $grayThreshold) {
            $level = RestrictionLevel::Gray;
        }

        $updateData = [
            'restriction_level' => $level,
        ];

        if ($level === RestrictionLevel::Black) {
            $updateData['restriction_until'] = now()->addDays(
                (int) (BusinessParameter::where('key', 'blacklist_duration_days')->first()?->getTypedValue() ?? 90)
            );
        } elseif ($level === RestrictionLevel::Gray) {
            $updateData['restriction_until'] = null;
        }

        $score->update($updateData);
    }

    private function calculateScore(CreditScore $creditScore): int
    {
        $noShowPenalty = $this->getParameter('credit_score_no_show_penalty', self::DEFAULT_NO_SHOW_PENALTY);
        $chargebackPenalty = $this->getParameter('credit_score_chargeback_penalty', self::DEFAULT_CHARGEBACK_PENALTY);
        $refundPenalty = $this->getParameter('credit_score_refund_penalty', self::DEFAULT_REFUND_PENALTY);
        $violationPenalty = $this->getParameter('credit_score_violation_penalty', self::DEFAULT_VIOLATION_PENALTY);

        $score = self::BASE_SCORE
            - ($creditScore->no_show_count * $noShowPenalty)
            - ($creditScore->chargeback_count * $chargebackPenalty)
            - ($creditScore->refund_count * $refundPenalty)
            - ($creditScore->violation_count * $violationPenalty);

        return max(0, $score);
    }

    private function getParameter(string $key, int $default): int
    {
        $param = BusinessParameter::where('key', $key)->first();

        return $param ? (int) $param->getTypedValue() : $default;
    }
}
