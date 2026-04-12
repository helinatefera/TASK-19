<?php

namespace App\Livewire\Campaign;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class CampaignForm extends Component
{
    protected ApiClient $apiClient;

    public ?int $campaignId = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string|min:10')]
    public string $description = '';

    #[Validate('nullable|string|max:5000')]
    public string $risk_disclosure = '';

    #[Validate('required|numeric|min:1')]
    public float $target_amount = 0;

    #[Validate('required|integer|min:7|max:60')]
    public int $duration_days = 30;

    /** @var array<int, array{title: string, description: string, price: float, quantity: int, fulfillment_type: string}> */
    public array $rewardTiers = [];

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function mount(?int $campaignId = null): void
    {
        if ($campaignId) {
            try {
                $response = $this->apiClient->get("/api/campaigns/{$campaignId}");
                $campaign = $response['campaign'] ?? $response;

                $this->campaignId = $campaign['id'];
                $this->title = $campaign['title'] ?? '';
                $this->description = $campaign['description'] ?? '';
                $this->risk_disclosure = $campaign['risk_disclosure'] ?? '';
                $this->target_amount = ($campaign['target_amount'] ?? 0) / 100;
                $this->duration_days = $campaign['duration_days'] ?? 30;

                $rewardTiers = $campaign['reward_tiers'] ?? [];
                $this->rewardTiers = array_map(fn ($tier) => [
                    'title' => $tier['title'] ?? '',
                    'description' => $tier['description'] ?? '',
                    'price' => ($tier['price'] ?? 0) / 100,
                    'quantity' => $tier['quantity_total'] ?? 0,
                    'fulfillment_type' => $tier['fulfillment_type'] ?? 'digital',
                ], $rewardTiers);
            } catch (RuntimeException $e) {
                session()->flash('error', $e->getMessage());
            }
        }
    }

    public function addRewardTier(): void
    {
        $this->rewardTiers[] = [
            'title' => '',
            'description' => '',
            'price' => 0,
            'quantity' => 0,
            'fulfillment_type' => 'digital',
        ];
    }

    public function removeRewardTier(int $index): void
    {
        unset($this->rewardTiers[$index]);
        $this->rewardTiers = array_values($this->rewardTiers);
    }

    public function save(): mixed
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'risk_disclosure' => 'nullable|string|max:5000',
            'target_amount' => 'required|numeric|min:1',
            'duration_days' => 'required|integer|min:7|max:60',
            'rewardTiers' => 'array',
            'rewardTiers.*.title' => 'required|string|max:255',
            'rewardTiers.*.description' => 'nullable|string|max:1000',
            'rewardTiers.*.price' => 'required|numeric|min:0.01',
            'rewardTiers.*.quantity' => 'required|integer|min:0',
            'rewardTiers.*.fulfillment_type' => 'required|string|in:digital,physical,event',
        ]);

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'risk_disclosure' => $this->risk_disclosure,
            'target_amount' => (int) round($this->target_amount * 100),
            'duration_days' => $this->duration_days,
            'reward_tiers' => array_map(fn ($tier, $index) => [
                'title' => $tier['title'],
                'description' => $tier['description'] ?? '',
                'price' => (int) round($tier['price'] * 100),
                'quantity_total' => $tier['quantity'],
                'fulfillment_type' => $tier['fulfillment_type'],
                'sort_order' => $index,
            ], $this->rewardTiers, array_keys($this->rewardTiers)),
        ];

        try {
            if ($this->campaignId) {
                $response = $this->apiClient->put("/api/campaigns/{$this->campaignId}", $data);
                $campaign = $response['campaign'] ?? $response;
            } else {
                $scope = 'idempotency:campaign:create';
                $key = session($scope);
                if (! $key) {
                    $key = 'campaign-create-' . uniqid();
                    session()->put($scope, $key);
                }

                $response = $this->apiClient->post('/api/campaigns', $data, [
                    'X-Idempotency-Key' => $key,
                ]);
                session()->forget($scope);
                $campaign = $response['campaign'] ?? $response;
            }

            $campaignId = $campaign['id'] ?? $this->campaignId;

            session()->flash('success', $this->campaignId ? 'Campaign updated.' : 'Campaign created.');

            return $this->redirect(route('campaigns.detail', ['campaignId' => $campaignId]), navigate: true);
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return null;
        }
    }

    public function render(): View
    {
        $fulfillmentTypes = ['digital', 'physical', 'event'];

        return view('livewire.campaign.campaign-form', [
            'fulfillmentTypes' => $fulfillmentTypes,
            'isEditing' => $this->campaignId !== null,
        ]);
    }
}
