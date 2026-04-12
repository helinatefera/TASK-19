<?php

namespace App\Livewire\Campaign;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use RuntimeException;

#[Layout('layouts.app')]
class CampaignDetail extends Component
{
    protected ApiClient $apiClient;

    public int $campaignId;
    public array $campaign = [];
    public int $selectedTierId = 0;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function mount(int $campaignId): void
    {
        $this->campaignId = $campaignId;
        $this->loadCampaign();
    }

    public function loadCampaign(): void
    {
        try {
            $response = $this->apiClient->get("/api/campaigns/{$this->campaignId}");
            $this->campaign = $response['campaign'] ?? $response;
        } catch (RuntimeException $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function refreshCampaign(): void
    {
        $this->loadCampaign();
    }

    public function approve(): void
    {
        try {
            $this->apiClient->post("/api/campaigns/{$this->campaignId}/approve");
            session()->flash('success', 'Campaign approved successfully.');
            $this->loadCampaign();
        } catch (RuntimeException $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function reject(string $reason): void
    {
        if (trim($reason) === '') {
            $this->addError('rejectReason', 'A rejection reason is required.');
            return;
        }

        try {
            $this->apiClient->post("/api/campaigns/{$this->campaignId}/reject", [
                'notes' => $reason,
            ]);
            session()->flash('success', 'Campaign rejected.');
            $this->loadCampaign();
        } catch (RuntimeException $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function toggleVisibility(): void
    {
        $currentVisibility = $this->campaign['visibility'] ?? 'offline';
        $newVisibility = $currentVisibility === 'online' ? 'offline' : 'online';

        try {
            $this->apiClient->post("/api/campaigns/{$this->campaignId}/visibility", [
                'visibility' => $newVisibility,
            ]);
            session()->flash('success', "Campaign visibility changed to {$newVisibility}.");
            $this->loadCampaign();
        } catch (RuntimeException $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function closeCampaign(): void
    {
        try {
            $this->apiClient->post("/api/campaigns/{$this->campaignId}/close");
            session()->flash('success', 'Campaign closed.');
            $this->loadCampaign();
        } catch (RuntimeException $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function submitForReview(): void
    {
        try {
            $this->apiClient->post("/api/campaigns/{$this->campaignId}/submit");
            session()->flash('success', 'Campaign submitted for review.');
            $this->loadCampaign();
        } catch (RuntimeException $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function contribute(int $tierId): void
    {
        $scope = "idempotency:contribute:{$this->campaignId}:{$tierId}";
        $key = session($scope);
        if (! $key) {
            $key = 'contrib-' . $this->campaignId . '-' . $tierId . '-' . uniqid();
            session()->put($scope, $key);
        }

        try {
            $response = $this->apiClient->post('/api/orders', [
                'campaign_id' => $this->campaignId,
                'reward_tier_id' => $tierId,
                'request_key' => $key,
            ], ['X-Idempotency-Key' => $key]);

            session()->forget($scope);

            $orderId = $response['id'] ?? ($response['data']['id'] ?? null);
            if ($orderId) {
                $this->redirect(route('orders.detail', ['orderId' => $orderId]));
                return;
            }

            session()->flash('success', 'Contribution order created successfully.');
            $this->loadCampaign();
        } catch (\RuntimeException $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function render(): View
    {
        $user = session('api_user');
        $roles = array_column($user['roles'] ?? [], 'name');
        $isModerator = $user && !empty(array_intersect(['moderator', 'admin'], $roles));
        $isOwner = $user && ($user['id'] ?? null) === ($this->campaign['creator_id'] ?? null);

        $reviews = [];
        try {
            $reviewResponse = $this->apiClient->get("/api/campaigns/{$this->campaignId}/reviews");
            $reviews = $reviewResponse['data'] ?? $reviewResponse;
        } catch (RuntimeException $e) {
            // Reviews may not be available
        }

        $daysRemaining = null;
        $endsAt = $this->campaign['ends_at'] ?? null;
        if ($endsAt) {
            $endsAtDate = \Carbon\Carbon::parse($endsAt);
            if ($endsAtDate->isFuture()) {
                $daysRemaining = (int) now()->diffInDays($endsAtDate, false);
            }
        }

        $targetAmount = $this->campaign['target_amount'] ?? 0;
        $pledgedAmount = $this->campaign['pledged_amount'] ?? 0;
        $progressPercent = $targetAmount > 0
            ? min(100, round(($pledgedAmount / $targetAmount) * 100, 1))
            : 0;

        return view('livewire.campaign.campaign-detail', [
            'isModerator' => $isModerator,
            'isOwner' => $isOwner,
            'reviews' => $reviews,
            'daysRemaining' => $daysRemaining,
            'progressPercent' => $progressPercent,
        ]);
    }
}
