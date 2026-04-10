<?php

namespace App\Services\Review;

use App\Enums\OrderStatus;
use App\Events\ReviewSubmitted;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use RuntimeException;

class ReviewService
{
    private const VISIBILITY_DELAY_HOURS = 72;

    public function submit(Order $order, User $reviewer, array $data): Review
    {
        if ($order->status !== OrderStatus::Fulfilled) {
            throw new RuntimeException('Reviews can only be submitted for fulfilled orders.');
        }

        // Determine reviewee based on the review side
        $side = $data['side'] ?? 'user_to_creator';
        if ($side === 'user_to_creator') {
            $revieweeId = $order->campaign?->creator_id ?? $order->user_id;
        } else {
            $revieweeId = $order->user_id;
        }

        $review = Review::create(array_merge($data, [
            'order_id' => $order->id,
            'reviewer_id' => $reviewer->id,
            'reviewee_id' => $revieweeId,
            'public_alias' => $this->generatePublicAlias($reviewer),
            'visible_after' => now()->addHours($this->getVisibilityDelayHours()),
            'is_visible' => false,
        ]));

        ReviewSubmitted::dispatch($review);

        return $review;
    }

    public function publishPendingReviews(): int
    {
        return Review::query()
            ->where('is_visible', false)
            ->whereNotNull('visible_after')
            ->where('visible_after', '<=', now())
            ->update(['is_visible' => true]);
    }

    private function getVisibilityDelayHours(): int
    {
        $param = \App\Models\BusinessParameter::where('key', 'review_visibility_delay_hours')->first();
        return $param ? (int) $param->getTypedValue() : 72;
    }

    private function generatePublicAlias(User $user): string
    {
        // Deterministic: same user always gets the same alias
        $hash = hash_hmac('sha256', (string) $user->id, config('app.key', 'civiccrowd-salt'));
        $suffix = substr($hash, 0, 8);

        return 'Reviewer-' . $suffix;
    }
}
