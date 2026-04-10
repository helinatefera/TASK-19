<?php

namespace App\Livewire\Voucher;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class VoucherDisplay extends Component
{
    protected ApiClient $apiClient;

    public int $voucherId;
    public array $voucher = [];

    public string $errorMessage = '';

    public bool $redeemed = false;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function mount(int $voucher): void
    {
        $this->voucherId = $voucher;
        $this->loadVoucher();
    }

    public function loadVoucher(): void
    {
        $response = $this->apiClient->get("/api/vouchers/{$this->voucherId}");

        // Unwrap if the response wraps the voucher in a 'data' key
        if (isset($response['data']) && !isset($response['id'])) {
            $this->voucher = $response['data'];
        } else {
            $this->voucher = $response;
        }
    }

    public function redeem(): void
    {
        $this->errorMessage = '';

        $user = Auth::user();

        if (!$user || !$user->hasRole('staff', 'moderator', 'admin')) {
            $this->errorMessage = 'You do not have permission to redeem vouchers.';
            return;
        }

        try {
            $this->apiClient->post("/api/vouchers/{$this->voucherId}/redeem");
            $this->loadVoucher();
            $this->redeemed = true;
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render(): View
    {
        $canRedeem = Auth::check() && Auth::user()->hasRole('staff', 'moderator', 'admin');

        return view('livewire.voucher.voucher-display', [
            'canRedeem' => $canRedeem,
        ]);
    }
}
