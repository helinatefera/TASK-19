<?php

namespace App\Livewire\Notification;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class NotificationInbox extends Component
{
    #[Url]
    public string $filter = 'all';

    #[Url]
    public string $typeFilter = 'all';

    public int $page = 1;

    protected ApiClient $apiClient;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function updatingFilter(): void
    {
        $this->page = 1;
    }

    public function updatingTypeFilter(): void
    {
        $this->page = 1;
    }

    public function markAsRead(string $id): void
    {
        try {
            $this->apiClient->post("/api/notifications/{$id}/read");
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function markAllAsRead(): void
    {
        try {
            $this->apiClient->post('/api/notifications/read-all');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
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
        $params = ['page' => $this->page, 'per_page' => 20];

        if ($this->filter === 'read') {
            $params['read'] = 'true';
        } elseif ($this->filter === 'unread') {
            $params['read'] = 'false';
        }

        if ($this->typeFilter !== 'all') {
            $params['type'] = $this->typeFilter;
        }

        try {
            $response = $this->apiClient->get('/api/notifications', $params);
            $notifications = $response['data'] ?? [];
            $meta = $response['meta'] ?? [
                'last_page' => $response['last_page'] ?? 1,
                'current_page' => $response['current_page'] ?? 1,
                'total' => $response['total'] ?? 0,
                'per_page' => $response['per_page'] ?? 20,
            ];
            $notificationTypes = $response['notification_types'] ?? [];
        } catch (\RuntimeException $e) {
            $notifications = [];
            $meta = [];
            $notificationTypes = [];
            session()->flash('error', $e->getMessage());
        }

        return view('livewire.notification.notification-inbox', [
            'notifications' => $notifications,
            'meta' => $meta,
            'notificationTypes' => $notificationTypes,
        ]);
    }
}
