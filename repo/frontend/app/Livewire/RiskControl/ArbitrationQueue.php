<?php

namespace App\Livewire\RiskControl;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ArbitrationQueue extends Component
{
    public ?int $selectedDisputeId = null;

    public string $decision = '';

    public string $reasoning = '';

    public string $actionTaken = '';

    public string $errorMessage = '';

    public bool $decisionSubmitted = false;

    protected ApiClient $apiClient;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function selectDispute(int $id): void
    {
        $this->selectedDisputeId = $id;
        $this->reset(['decision', 'reasoning', 'actionTaken', 'errorMessage', 'decisionSubmitted']);
    }

    public function clearSelection(): void
    {
        $this->selectedDisputeId = null;
        $this->reset(['decision', 'reasoning', 'actionTaken', 'errorMessage', 'decisionSubmitted']);
    }

    public function decide(): void
    {
        $this->errorMessage = '';

        $this->validate([
            'decision' => 'required|in:favor_initiator,favor_respondent,dismissed',
            'reasoning' => 'required|string|min:10|max:5000',
            'actionTaken' => 'nullable|string|max:2000',
        ]);

        try {
            $this->apiClient->post("/api/disputes/{$this->selectedDisputeId}/decide", [
                'decision' => $this->decision,
                'reasoning' => $this->reasoning,
                'action_taken' => $this->actionTaken,
            ]);

            $this->decisionSubmitted = true;
            $this->reset(['decision', 'reasoning', 'actionTaken']);
        } catch (\RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render(): View
    {
        try {
            $response = $this->apiClient->get('/api/disputes', [
                'status' => 'open,under_review,escalated',
            ]);
            $disputes = $response['data'] ?? [];
        } catch (\RuntimeException $e) {
            $disputes = [];
            session()->flash('error', $e->getMessage());
        }

        $selectedDispute = null;
        if ($this->selectedDisputeId) {
            try {
                $selectedDispute = $this->apiClient->get("/api/disputes/{$this->selectedDisputeId}");
                $selectedDispute = $selectedDispute['data'] ?? $selectedDispute;
            } catch (\RuntimeException $e) {
                $selectedDispute = null;
            }
        }

        return view('livewire.risk-control.arbitration-queue', [
            'disputes' => $disputes,
            'selectedDispute' => $selectedDispute,
        ]);
    }
}
