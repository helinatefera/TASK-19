<?php

namespace App\Livewire\Order;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;

#[Layout('layouts.app')]
class OrderDetail extends Component
{
    use WithFileUploads;
    protected ApiClient $apiClient;

    public int $orderId;
    public array $order = [];

    // Cancel modal
    public bool $showCancelModal = false;
    public string $cancelReason = '';

    // Refund modal
    public bool $showRefundModal = false;
    public string $refundReason = '';
    public int $refundAmount = 0;

    // After-sales modal
    public bool $showAfterSalesModal = false;
    public string $afterSalesType = '';
    public string $afterSalesReason = '';
    public $afterSalesAttachment;

    // Payment form (staff)
    public string $paymentMethod = 'cash';
    public int $paymentAmount = 0;
    public string $transactionRef = '';

    // Inline review forms for staff
    public string $staffNotes = '';

    // Error/success feedback
    public string $errorMessage = '';
    public string $successMessage = '';

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function mount(int $orderId): void
    {
        $this->orderId = $orderId;
        $this->loadOrder();
        $this->refundAmount = $this->order['amount'] ?? 0;
        $this->paymentAmount = $this->order['amount'] ?? 0;
    }

    public function loadOrder(): void
    {
        $this->order = $this->apiClient->get("/api/orders/{$this->orderId}");

        // Unwrap if the response wraps the order in a 'data' key
        if (isset($this->order['data']) && !isset($this->order['id'])) {
            $this->order = $this->order['data'];
        }
    }

    // --- Cancel ---------------------------------------------------------------

    public function openCancelModal(): void
    {
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancelReason = '';
    }

    public function cancelOrder(): void
    {
        $this->clearMessages();

        $this->validate([
            'cancelReason' => 'required|string|min:3|max:1000',
        ]);

        try {
            $this->apiClient->post("/api/orders/{$this->orderId}/cancel", [
                'reason' => $this->cancelReason,
            ]);
            $this->successMessage = 'Order has been cancelled.';
            $this->showCancelModal = false;
            $this->cancelReason = '';
            $this->loadOrder();
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    // --- Refund ---------------------------------------------------------------

    public function openRefundModal(): void
    {
        $this->refundReason = '';
        $this->refundAmount = $this->order['amount'] ?? 0;
        $this->showRefundModal = true;
    }

    public function closeRefundModal(): void
    {
        $this->showRefundModal = false;
        $this->refundReason = '';
    }

    public function requestRefund(): void
    {
        $this->clearMessages();

        $this->validate([
            'refundReason' => 'required|string|min:3|max:1000',
            'refundAmount' => 'required|integer|min:1|max:' . ($this->order['amount'] ?? 0),
        ]);

        $scope = "idempotency:refund:{$this->orderId}";
        $key = session($scope);
        if (! $key) {
            $key = 'refund-' . $this->orderId . '-' . uniqid();
            session()->put($scope, $key);
        }

        try {
            $this->apiClient->post("/api/orders/{$this->orderId}/refunds", [
                'reason' => $this->refundReason,
                'refund_amount' => $this->refundAmount,
            ], ['X-Idempotency-Key' => $key]);
            session()->forget($scope);
            $this->successMessage = 'Refund request submitted successfully.';
            $this->showRefundModal = false;
            $this->refundReason = '';
            $this->loadOrder();
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function approveRefund(int $refundRequestId): void
    {
        $this->clearMessages();

        try {
            $this->apiClient->post("/api/refund-requests/{$refundRequestId}/approve");
            $this->successMessage = 'Refund request approved.';
            $this->loadOrder();
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function rejectRefund(int $refundRequestId): void
    {
        $this->clearMessages();

        try {
            $this->apiClient->post("/api/refund-requests/{$refundRequestId}/reject");
            $this->successMessage = 'Refund request rejected.';
            $this->loadOrder();
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    // --- After-Sales ----------------------------------------------------------

    public function openAfterSalesModal(): void
    {
        $this->afterSalesType = '';
        $this->afterSalesReason = '';
        $this->showAfterSalesModal = true;
    }

    public function closeAfterSalesModal(): void
    {
        $this->showAfterSalesModal = false;
        $this->afterSalesType = '';
        $this->afterSalesReason = '';
        $this->afterSalesAttachment = null;
    }

    public function submitAfterSales(): void
    {
        $this->clearMessages();

        $validTypes = ['refund', 'exchange', 'complaint', 'other'];

        $this->validate([
            'afterSalesType' => 'required|string|in:' . implode(',', $validTypes),
            'afterSalesReason' => 'required|string|min:3|max:2000',
            'afterSalesAttachment' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $scope = "idempotency:aftersales:{$this->orderId}";
        $key = session($scope);
        if (! $key) {
            $key = 'aftersales-' . $this->orderId . '-' . uniqid();
            session()->put($scope, $key);
        }

        try {
            $checksum = hash_file('sha256', $this->afterSalesAttachment->getRealPath());

            $this->apiClient->postWithFile(
                "/api/orders/{$this->orderId}/after-sales",
                [
                    'type' => $this->afterSalesType,
                    'reason' => $this->afterSalesReason,
                    'client_checksum' => $checksum,
                ],
                'attachment',
                $this->afterSalesAttachment,
                ['X-Idempotency-Key' => $key]
            );

            session()->forget($scope);
            $this->successMessage = 'After-sales request submitted.';
            $this->showAfterSalesModal = false;
            $this->afterSalesType = '';
            $this->afterSalesReason = '';
            $this->afterSalesAttachment = null;
            $this->loadOrder();
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function resolveAfterSales(int $requestId, string $status): void
    {
        $this->clearMessages();

        try {
            $this->apiClient->post("/api/after-sales/{$requestId}/resolve", [
                'status' => $status,
                'staff_notes' => $this->staffNotes,
            ]);

            $this->staffNotes = '';
            $this->successMessage = 'After-sales request updated.';
            $this->loadOrder();
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    // --- Staff: Fulfill -------------------------------------------------------

    public function markFulfilled(): void
    {
        $this->clearMessages();

        try {
            $this->apiClient->post("/api/orders/{$this->orderId}/fulfill");
            $this->successMessage = 'Order marked as fulfilled.';
            $this->loadOrder();
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    // --- Staff: Record Payment ------------------------------------------------

    public function recordPayment(): void
    {
        $this->clearMessages();

        $validMethods = ['cash', 'card_on_file'];

        $this->validate([
            'paymentMethod' => 'required|string|in:' . implode(',', $validMethods),
            'paymentAmount' => 'required|integer|min:1',
            'transactionRef' => 'nullable|string|max:255',
        ]);

        $scope = "idempotency:payment:{$this->orderId}";
        $key = session($scope);
        if (! $key) {
            $key = 'payment-' . $this->orderId . '-' . uniqid();
            session()->put($scope, $key);
        }

        try {
            $this->apiClient->post("/api/orders/{$this->orderId}/payments", [
                'method' => $this->paymentMethod,
                'amount' => $this->paymentAmount,
                'transaction_ref' => $this->transactionRef ?: null,
            ], ['X-Idempotency-Key' => $key]);
            session()->forget($scope);
            $this->successMessage = 'Payment recorded successfully.';
            $this->transactionRef = '';
            $this->loadOrder();
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    // --- Staff: Attendance ----------------------------------------------------

    public function markAttended(bool $attended): void
    {
        $this->clearMessages();

        try {
            $this->apiClient->post("/api/orders/{$this->orderId}/attend", [
                'attended' => $attended,
            ]);
            $this->successMessage = $attended ? 'Marked as attended.' : 'Marked as not attended.';
            $this->loadOrder();
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    // --- Helpers --------------------------------------------------------------

    public function getIsStaffProperty(): bool
    {
        $user = Auth::user();

        return $user && $user->hasRole('staff', 'moderator', 'admin');
    }

    public function getIsPaidProperty(): bool
    {
        $payments = $this->order['payments'] ?? [];

        foreach ($payments as $payment) {
            if (($payment['status'] ?? '') === 'completed') {
                return true;
            }
        }

        return false;
    }

    public function getCanRequestAfterSalesProperty(): bool
    {
        return ($this->order['status'] ?? '') === 'fulfilled'
            && !($this->order['has_pending_after_sales'] ?? false);
    }

    private function clearMessages(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    public function render(): View
    {
        $paymentMethods = ['cash', 'card_on_file'];
        $afterSalesTypes = ['refund', 'exchange', 'complaint', 'other'];

        return view('livewire.order.order-detail', [
            'isStaff' => $this->isStaff,
            'isPaid' => $this->isPaid,
            'canRequestAfterSales' => $this->canRequestAfterSales,
            'paymentMethods' => $paymentMethods,
            'afterSalesTypes' => $afterSalesTypes,
        ]);
    }
}
