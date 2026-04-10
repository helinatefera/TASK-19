<?php

namespace App\Livewire\Voucher;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class VoucherList extends Component
{
    protected ApiClient $apiClient;

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
            $response = $this->apiClient->get('/api/vouchers', [
                'page' => $this->page,
                'per_page' => 15,
            ]);
        } catch (\RuntimeException $e) {
            $response = ['data' => [], 'meta' => ['last_page' => 1, 'current_page' => 1, 'total' => 0]];
        }

        $vouchers = $response['data'] ?? [];
        $meta = $response['meta'] ?? ['last_page' => 1, 'current_page' => 1, 'total' => 0];

        return view('livewire.voucher.voucher-list', [
            'vouchers' => $vouchers,
            'meta' => $meta,
        ]);
    }
}
