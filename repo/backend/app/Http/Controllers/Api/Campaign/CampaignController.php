<?php

namespace App\Http\Controllers\Api\Campaign;

use App\Enums\CampaignStatus;
use App\Enums\CampaignVisibility;
use App\Http\Controllers\Api\BaseController;
use App\Models\BusinessParameter;
use App\Models\Campaign;
use App\Services\Campaign\CampaignLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class CampaignController extends BaseController
{
    public function __construct(
        private readonly CampaignLifecycleService $lifecycleService,
    ) {}

    /**
     * GET /api/campaigns
     */
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::query()
            ->with(['creator', 'rewardTiers']);

        // Public listing: only published (fundraising+) and online campaigns.
        if (! $request->user()?->hasRole('moderator') && ! $request->user()?->hasRole('admin')) {
            $query->whereIn('status', [
                CampaignStatus::Fundraising,
                CampaignStatus::Success,
            ])->where('visibility', CampaignVisibility::Online);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return $this->paginated($query->latest());
    }

    /**
     * GET /api/campaigns/{campaign}
     */
    public function show(Request $request, Campaign $campaign): JsonResponse
    {
        // Public users can only view published (fundraising/success) + online campaigns
        $user = $request->user();
        $isPrivileged = $user && ($user->hasRole('moderator') || $user->hasRole('admin') || $user->id === $campaign->creator_id);

        if (! $isPrivileged) {
            $publicStatuses = [CampaignStatus::Fundraising, CampaignStatus::Success];
            if (! in_array($campaign->status, $publicStatuses) || $campaign->visibility !== CampaignVisibility::Online) {
                return $this->error('Resource not found.', 404);
            }
        }

        $campaign->load(['creator', 'rewardTiers']);

        $fundingProgress = $campaign->target_amount > 0
            ? round(($campaign->pledged_amount / $campaign->target_amount) * 100, 2)
            : 0;

        return $this->success([
            'campaign' => $campaign,
            'funding_progress' => $fundingProgress,
        ]);
    }

    /**
     * POST /api/campaigns
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Campaign::class);

        $minDuration = (int) (BusinessParameter::where('key', 'campaign_min_duration_days')->first()?->getTypedValue() ?? 7);
        $maxDuration = (int) (BusinessParameter::where('key', 'campaign_max_duration_days')->first()?->getTypedValue() ?? 60);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'risk_disclosure' => 'required|string',
            'target_amount' => 'required|integer|min:1',
            'duration_days' => "required|integer|min:{$minDuration}|max:{$maxDuration}",
            'currency' => 'sometimes|string|max:3',
            'reward_tiers' => 'sometimes|array',
            'reward_tiers.*.title' => 'required_with:reward_tiers|string|max:255',
            'reward_tiers.*.price' => 'required_with:reward_tiers|integer|min:0',
            'reward_tiers.*.quantity_total' => 'sometimes|integer|min:0',
            'reward_tiers.*.fulfillment_type' => 'required_with:reward_tiers|string|in:digital,physical,event',
            'reward_tiers.*.sort_order' => 'sometimes|integer|min:0',
        ]);

        $campaign = Campaign::create([
            'creator_id' => $request->user()->id,
            'title' => $request->input('title'),
            'slug' => Str::slug($request->input('title')) . '-' . Str::random(6),
            'description' => $request->input('description'),
            'risk_disclosure' => $request->input('risk_disclosure'),
            'target_amount' => $request->input('target_amount'),
            'pledged_amount' => 0,
            'currency' => $request->input('currency', 'USD'),
            'duration_days' => $request->input('duration_days'),
            'status' => CampaignStatus::Draft,
            'visibility' => CampaignVisibility::Offline,
        ]);

        // Create reward tiers if provided
        if ($request->filled('reward_tiers')) {
            foreach ($request->input('reward_tiers') as $tier) {
                $campaign->rewardTiers()->create([
                    'title' => $tier['title'],
                    'description' => $tier['description'] ?? '',
                    'price' => $tier['price'],
                    'quantity_total' => $tier['quantity_total'] ?? 0,
                    'quantity_claimed' => 0,
                    'fulfillment_type' => $tier['fulfillment_type'],
                    'sort_order' => $tier['sort_order'] ?? 0,
                ]);
            }
        }

        $campaign->load('rewardTiers');

        return $this->success($campaign, 201);
    }

    /**
     * PUT /api/campaigns/{campaign}
     */
    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);

        $minDuration = (int) (BusinessParameter::where('key', 'campaign_min_duration_days')->first()?->getTypedValue() ?? 7);
        $maxDuration = (int) (BusinessParameter::where('key', 'campaign_max_duration_days')->first()?->getTypedValue() ?? 60);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'risk_disclosure' => 'sometimes|string',
            'target_amount' => 'sometimes|integer|min:1',
            'duration_days' => "sometimes|integer|min:{$minDuration}|max:{$maxDuration}",
            'reward_tiers' => 'sometimes|array',
            'reward_tiers.*.title' => 'required_with:reward_tiers|string|max:255',
            'reward_tiers.*.price' => 'required_with:reward_tiers|integer|min:0',
            'reward_tiers.*.quantity_total' => 'sometimes|integer|min:0',
            'reward_tiers.*.fulfillment_type' => 'required_with:reward_tiers|string|in:digital,physical,event',
            'reward_tiers.*.sort_order' => 'sometimes|integer|min:0',
        ]);

        $campaign->update($request->only([
            'title', 'description', 'risk_disclosure', 'target_amount', 'duration_days',
        ]));

        // Replace reward tiers if provided
        if ($request->has('reward_tiers')) {
            $campaign->rewardTiers()->delete();
            foreach ($request->input('reward_tiers') as $tier) {
                $campaign->rewardTiers()->create([
                    'title' => $tier['title'],
                    'description' => $tier['description'] ?? '',
                    'price' => $tier['price'],
                    'quantity_total' => $tier['quantity_total'] ?? 0,
                    'quantity_claimed' => 0,
                    'fulfillment_type' => $tier['fulfillment_type'],
                    'sort_order' => $tier['sort_order'] ?? 0,
                ]);
            }
        }

        return $this->success($campaign->refresh()->load('rewardTiers'));
    }

    /**
     * POST /api/campaigns/{campaign}/submit
     */
    public function submit(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('submit', $campaign);

        try {
            $campaign = $this->lifecycleService->submitForReview($campaign);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($campaign);
    }

    /**
     * POST /api/campaigns/{campaign}/approve
     */
    public function approve(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('approve', $campaign);

        try {
            $campaign = $this->lifecycleService->approve(
                $campaign,
                $request->user(),
                $request->input('notes'),
            );
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($campaign);
    }

    /**
     * POST /api/campaigns/{campaign}/reject
     */
    public function reject(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('reject', $campaign);

        $request->validate([
            'notes' => 'required|string',
        ]);

        try {
            $campaign = $this->lifecycleService->reject(
                $campaign,
                $request->user(),
                $request->input('notes'),
            );
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($campaign);
    }

    /**
     * POST /api/campaigns/{campaign}/close
     */
    public function close(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('close', $campaign);

        $request->validate([
            'notes' => 'sometimes|string|max:2000',
        ]);

        try {
            $campaign = $this->lifecycleService->close(
                $campaign,
                $request->user(),
                $request->input('notes'),
            );
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($campaign);
    }

    /**
     * POST /api/campaigns/{campaign}/visibility
     */
    public function visibility(Request $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('toggleVisibility', $campaign);

        $request->validate([
            'visibility' => 'required|string|in:online,offline',
        ]);

        $campaign->update([
            'visibility' => CampaignVisibility::from($request->input('visibility')),
        ]);

        return $this->success($campaign->refresh());
    }
}
