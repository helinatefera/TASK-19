<?php

namespace App\Livewire\Campaign;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class CampaignList extends Component
{
    protected ApiClient $apiClient;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public int $page = 1;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function updatingSearch(): void
    {
        $this->page = 1;
    }

    public function updatingStatusFilter(): void
    {
        $this->page = 1;
    }

    public function previousPage(): void
    {
        $this->page = max(1, $this->page - 1);
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function gotoPage(int $page): void
    {
        $this->page = max(1, $page);
    }

    public function render(): View
    {
        $user = session('api_user');
        $roles = array_column($user['roles'] ?? [], 'name');
        $isModerator = $user && !empty(array_intersect(['moderator', 'admin'], $roles));

        $params = [
            'page' => $this->page,
            'per_page' => 12,
        ];

        if ($this->search !== '') {
            $params['search'] = $this->search;
        }

        if ($this->statusFilter !== '') {
            $params['status'] = $this->statusFilter;
        }

        try {
            $response = $this->apiClient->get('/api/campaigns', $params);
        } catch (RuntimeException $e) {
            $response = ['data' => [], 'meta' => ['last_page' => 1, 'current_page' => 1, 'total' => 0]];
        }

        $campaigns = $response['data'] ?? [];
        $meta = $response['meta'] ?? [
            'last_page' => $response['last_page'] ?? 1,
            'current_page' => $response['current_page'] ?? 1,
            'total' => $response['total'] ?? 0,
        ];

        $statuses = [
            'draft',
            'pending_review',
            'published',
            'fundraising',
            'success',
            'failure',
            'closed',
        ];

        return view('livewire.campaign.campaign-list', [
            'campaigns' => $campaigns,
            'meta' => $meta,
            'isModerator' => $isModerator,
            'statuses' => $statuses,
        ]);
    }
}
