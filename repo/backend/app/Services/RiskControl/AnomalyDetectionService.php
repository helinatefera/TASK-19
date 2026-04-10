<?php

namespace App\Services\RiskControl;

use App\Enums\AnomalyType;
use App\Models\AnomalyFlag;
use App\Models\DeviceFingerprint;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class AnomalyDetectionService
{
    private const DEFAULT_REFUND_THRESHOLD = 3;

    private const DEFAULT_REFUND_WINDOW_DAYS = 30;

    public function checkRefundFrequency(User $user): ?AnomalyFlag
    {
        $windowDays = $this->getParameter('anomaly_refund_window_days', self::DEFAULT_REFUND_WINDOW_DAYS);
        $threshold = $this->getParameter('anomaly_refund_threshold', self::DEFAULT_REFUND_THRESHOLD);

        $recentRefundCount = RefundRequest::query()
            ->where('requested_by', $user->id)
            ->where('created_at', '>=', now()->subDays($windowDays))
            ->count();

        if ($recentRefundCount > $threshold) {
            return $this->flagAnomaly(
                $user,
                AnomalyType::ExcessiveRefunds,
                [
                    'refund_count' => $recentRefundCount,
                    'window_days' => $windowDays,
                    'threshold' => $threshold,
                ]
            );
        }

        return null;
    }

    public function detectDuplicateDevices(): Collection
    {
        $duplicates = DeviceFingerprint::query()
            ->select('fingerprint_hash')
            ->selectRaw('COUNT(DISTINCT user_id) as user_count')
            ->groupBy('fingerprint_hash')
            ->havingRaw('COUNT(DISTINCT user_id) > 1')
            ->get();

        $flagged = collect();

        foreach ($duplicates as $duplicate) {
            $userIds = DeviceFingerprint::query()
                ->where('fingerprint_hash', $duplicate->fingerprint_hash)
                ->distinct()
                ->pluck('user_id');

            foreach ($userIds as $userId) {
                $existing = AnomalyFlag::query()
                    ->where('user_id', $userId)
                    ->where('type', AnomalyType::DuplicateDeviceFingerprint)
                    ->whereNull('resolved_at')
                    ->first();

                if (! $existing) {
                    $user = User::find($userId);

                    if ($user) {
                        $flag = $this->flagAnomaly(
                            $user,
                            AnomalyType::DuplicateDeviceFingerprint,
                            [
                                'fingerprint_hash' => $duplicate->fingerprint_hash,
                                'shared_user_ids' => $userIds->toArray(),
                            ]
                        );
                        $flagged->push($flag);
                    }
                }
            }
        }

        return $flagged;
    }

    private function getParameter(string $key, int $default): int
    {
        $param = \App\Models\BusinessParameter::where('key', $key)->first();

        return $param ? (int) $param->getTypedValue() : $default;
    }

    public function flagAnomaly(User $user, AnomalyType $type, array $evidence = []): AnomalyFlag
    {
        return AnomalyFlag::create([
            'user_id' => $user->id,
            'type' => $type,
            'description' => "Anomaly detected: {$type->value}",
            'evidence' => $evidence,
        ]);
    }
}
