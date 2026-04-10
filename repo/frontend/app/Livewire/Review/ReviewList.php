<?php

namespace App\Livewire\Review;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ReviewList extends Component
{
    protected ApiClient $apiClient;

    public int $campaignId;

    public int $page = 1;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
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
            $response = $this->apiClient->get("/api/campaigns/{$this->campaignId}/reviews", [
                'page' => $this->page,
                'per_page' => 10,
            ]);
        } catch (\RuntimeException $e) {
            $response = ['data' => [], 'meta' => ['last_page' => 1, 'current_page' => 1, 'total' => 0]];
        }

        $reviews = $response['data'] ?? [];
        $meta = $response['meta'] ?? ['last_page' => 1, 'current_page' => 1, 'total' => 0];

        return view('livewire.review.review-list', [
            'reviews' => $reviews,
            'meta' => $meta,
        ]);
    }
}
