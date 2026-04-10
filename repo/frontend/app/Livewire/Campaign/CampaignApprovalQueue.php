<?php

namespace App\Livewire\Campaign;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class CampaignApprovalQueue extends Component
{
    protected ApiClient $apiClient;

    public bool $showRejectModal = false;
    public string $rejectReason = '';
    public ?int $rejectItemId = null;
    public ?string $rejectItemType = null;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function approveCampaign(int $campaignId): void
    {
        try {
            $this->apiClient->post("/api/campaigns/{$campaignId}/approve");
            session()->flash('success', 'Campaign approved.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function approveProgram(int $programId): void
    {
        try {
            $this->apiClient->post("/api/programs/{$programId}/approve");
            session()->flash('success', 'Program approved.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openRejectModal(int $itemId, string $itemType): void
    {
        $this->rejectItemId = $itemId;
        $this->rejectItemType = $itemType;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectItemId = null;
        $this->rejectItemType = null;
        $this->rejectReason = '';
    }

    public function confirmReject(): void
    {
        if (trim($this->rejectReason) === '') {
            $this->addError('rejectReason', 'A rejection reason is required.');
            return;
        }

        try {
            if ($this->rejectItemType === 'campaign') {
                $this->apiClient->post("/api/campaigns/{$this->rejectItemId}/reject", [
                    'notes' => $this->rejectReason,
                ]);
                session()->flash('success', 'Campaign rejected.');
            } elseif ($this->rejectItemType === 'program') {
                $this->apiClient->post("/api/programs/{$this->rejectItemId}/reject", [
                    'notes' => $this->rejectReason,
                ]);
                session()->flash('success', 'Program rejected.');
            }
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->closeRejectModal();
    }

    public function render(): View
    {
        $items = collect();

        // Fetch pending campaigns (status=pending_review)
        try {
            $campaignResponse = $this->apiClient->get('/api/campaigns', [
                'status' => 'pending_review',
                'per_page' => 50,
            ]);
            $pendingCampaigns = collect($campaignResponse['data'] ?? [])
                ->map(function ($campaign) {
                    return [
                        'id' => $campaign['id'],
                        'type' => 'campaign',
                        'title' => $campaign['title'],
                        'creator' => $campaign['creator']['display_name']
                            ?? $campaign['creator']['username']
                            ?? 'Unknown',
                        'submitted_at' => $campaign['updated_at'] ?? $campaign['created_at'],
                    ];
                });
            $items = $items->merge($pendingCampaigns);
        } catch (RuntimeException $e) {
            // Campaigns may not be accessible
        }

        // Fetch pending programs (status=pending_review)
        try {
            $programResponse = $this->apiClient->get('/api/programs', [
                'status' => 'pending_review',
                'per_page' => 50,
            ]);
            $pendingPrograms = collect($programResponse['data'] ?? [])
                ->map(function ($program) {
                    return [
                        'id' => $program['id'],
                        'type' => 'program',
                        'title' => $program['title'],
                        'creator' => $program['creator']['display_name']
                            ?? $program['creator']['username']
                            ?? 'Unknown',
                        'submitted_at' => $program['updated_at'] ?? $program['created_at'],
                    ];
                });
            $items = $items->merge($pendingPrograms);
        } catch (RuntimeException $e) {
            // Programs may not be accessible
        }

        $items = $items->sortBy('submitted_at')->values();

        return view('livewire.campaign.campaign-approval-queue', [
            'items' => $items,
        ]);
    }
}
