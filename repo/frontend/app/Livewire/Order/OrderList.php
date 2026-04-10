<?php

namespace App\Livewire\Order;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class OrderList extends Component
{
    protected ApiClient $apiClient;

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $typeFilter = '';

    public int $page = 1;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function updatingStatusFilter(): void
    {
        $this->page = 1;
    }

    public function updatingTypeFilter(): void
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

    public function gotoPage(int $page): void
    {
        $this->page = $page;
    }

    public function render(): View
    {
        $params = [
            'page' => $this->page,
            'per_page' => 15,
        ];

        if ($this->statusFilter !== '') {
            $params['status'] = $this->statusFilter;
        }

        if ($this->typeFilter !== '') {
            $params['order_type'] = $this->typeFilter;
        }

        try {
            $response = $this->apiClient->get('/api/orders', $params);
        } catch (\RuntimeException $e) {
            $response = ['data' => [], 'last_page' => 1, 'current_page' => 1, 'total' => 0];
        }

        $orders = $response['data'] ?? [];
        $meta = $response['meta'] ?? [
            'last_page' => $response['last_page'] ?? 1,
            'current_page' => $response['current_page'] ?? 1,
            'total' => $response['total'] ?? 0,
            'per_page' => $response['per_page'] ?? 15,
        ];
        $isStaff = auth()->check() && auth()->user()->hasRole('staff', 'moderator', 'admin');

        $statuses = ['pending', 'confirmed', 'fulfilled', 'cancelled', 'refunded', 'after_sales'];
        $types = ['contribution', 'reservation'];

        return view('livewire.order.order-list', [
            'orders' => $orders,
            'meta' => $meta,
            'isStaff' => $isStaff,
            'statuses' => $statuses,
            'types' => $types,
        ]);
    }
}
