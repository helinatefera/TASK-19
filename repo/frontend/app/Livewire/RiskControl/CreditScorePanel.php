<?php

namespace App\Livewire\RiskControl;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreditScorePanel extends Component
{
    public ?int $expandedUserId = null;

    public int $page = 1;

    protected ApiClient $apiClient;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function toggleExpand(int $userId): void
    {
        if ($this->expandedUserId === $userId) {
            $this->expandedUserId = null;
        } else {
            $this->expandedUserId = $userId;
        }
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function render(): View
    {
        try {
            $response = $this->apiClient->get('/api/risk/credit-scores', [
                'page' => $this->page,
                'per_page' => 20,
            ]);
            $scores = $response['data'] ?? [];
            $meta = $response['meta'] ?? [];
        } catch (\RuntimeException $e) {
            $scores = [];
            $meta = [];
            session()->flash('error', $e->getMessage());
        }

        return view('livewire.risk-control.credit-score-panel', [
            'scores' => $scores,
            'meta' => $meta,
        ]);
    }
}
