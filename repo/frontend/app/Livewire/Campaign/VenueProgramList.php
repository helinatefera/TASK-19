<?php

namespace App\Livewire\Campaign;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class VenueProgramList extends Component
{
    protected ApiClient $apiClient;

    #[Url]
    public string $search = '';

    public int $page = 1;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function updatingSearch(): void
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
        $params = [
            'page' => $this->page,
            'per_page' => 12,
        ];

        if ($this->search !== '') {
            $params['search'] = $this->search;
        }

        try {
            $response = $this->apiClient->get('/api/programs', $params);
        } catch (RuntimeException $e) {
            $response = ['data' => [], 'meta' => ['last_page' => 1, 'current_page' => 1, 'total' => 0]];
        }

        $programs = $response['data'] ?? [];
        $meta = $response['meta'] ?? [
            'last_page' => $response['last_page'] ?? 1,
            'current_page' => $response['current_page'] ?? 1,
            'total' => $response['total'] ?? 0,
        ];

        return view('livewire.campaign.venue-program-list', [
            'programs' => $programs,
            'meta' => $meta,
        ]);
    }
}
