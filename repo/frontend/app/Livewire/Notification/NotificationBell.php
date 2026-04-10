<?php

namespace App\Livewire\Notification;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    protected ApiClient $apiClient;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function mount(): void
    {
        $this->loadCount();
    }

    public function loadCount(): void
    {
        try {
            $response = $this->apiClient->get('/api/notifications/unread-count');
            $this->unreadCount = $response['unread_count'] ?? 0;
        } catch (\RuntimeException $e) {
            $this->unreadCount = 0;
        }
    }

    public function render(): View
    {
        $this->loadCount();

        return view('livewire.notification.notification-bell');
    }
}
