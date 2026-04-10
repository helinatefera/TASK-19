<?php

namespace App\Livewire\Admin;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class AuditLogViewer extends Component
{
    #[Url]
    public string $actionFilter = '';

    #[Url]
    public string $actorFilter = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public int $page = 1;

    protected ApiClient $apiClient;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function updatingActionFilter(): void
    {
        $this->page = 1;
    }

    public function updatingActorFilter(): void
    {
        $this->page = 1;
    }

    public function updatingDateFrom(): void
    {
        $this->page = 1;
    }

    public function updatingDateTo(): void
    {
        $this->page = 1;
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
        $params = ['page' => $this->page, 'per_page' => 25];

        if ($this->actionFilter !== '') {
            $params['action'] = $this->actionFilter;
        }

        if ($this->actorFilter !== '') {
            $params['actor'] = $this->actorFilter;
        }

        if ($this->dateFrom !== '') {
            $params['date_from'] = $this->dateFrom;
        }

        if ($this->dateTo !== '') {
            $params['date_to'] = $this->dateTo;
        }

        try {
            $response = $this->apiClient->get('/api/admin/audit-logs', $params);
            $logs = $response['data'] ?? [];
            $meta = $response['meta'] ?? [];
            $distinctActions = $response['distinct_actions'] ?? [];
        } catch (\RuntimeException $e) {
            $logs = [];
            $meta = [];
            $distinctActions = [];
            session()->flash('error', $e->getMessage());
        }

        return view('livewire.admin.audit-log-viewer', [
            'logs' => $logs,
            'meta' => $meta,
            'distinctActions' => $distinctActions,
        ]);
    }
}
