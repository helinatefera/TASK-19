<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ================================================================== --}}
    {{-- SUCCESS / ERROR MESSAGES                                           --}}
    {{-- ================================================================== --}}
    @if($successMessage)
        <div class="mb-6 rounded-md bg-green-50 border border-green-200 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800">{{ $successMessage }}</p>
            </div>
        </div>
    @endif
    @if($errorMessage)
        <div class="mb-6 rounded-md bg-red-50 border border-red-200 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
                <p class="ml-3 text-sm font-medium text-red-800">{{ $errorMessage }}</p>
            </div>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-6 rounded-md bg-red-50 border border-red-200 p-4">
            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================================================================== --}}
    {{-- 1. ORDER HEADER                                                    --}}
    {{-- ================================================================== --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="flex-1">
                <p class="text-sm text-gray-500 uppercase tracking-wider font-medium">Confirmation Number</p>
                <p class="text-3xl font-mono font-bold text-indigo-700 mt-1">{{ $order['confirmation_number'] ?? '' }}</p>

                <div class="mt-4 flex flex-wrap gap-2">
                    {{-- Type Badge --}}
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ ($order['order_type'] ?? '') === 'contribution'
                            ? 'bg-purple-100 text-purple-800'
                            : 'bg-teal-100 text-teal-800' }}
                    ">
                        {{ ucfirst($order['order_type'] ?? '') }}
                    </span>

                    {{-- Status Badge --}}
                    @php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'confirmed' => 'bg-blue-100 text-blue-800',
                            'fulfilled' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            'refunded' => 'bg-gray-100 text-gray-800',
                            'after_sales' => 'bg-orange-100 text-orange-800',
                        ];
                        $statusColor = $statusColors[$order['status'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                        {{ ucfirst(str_replace('_', ' ', $order['status'] ?? '')) }}
                    </span>

                    {{-- Attendance Badge (if reservation) --}}
                    @if(($order['order_type'] ?? '') === 'reservation')
                        @if(($order['attended'] ?? null) === true)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Attended</span>
                        @elseif(($order['attended'] ?? null) === false)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Not Attended</span>
                        @endif
                    @endif
                </div>
            </div>

            <div class="text-right space-y-2">
                <p class="text-3xl font-bold text-gray-900">${{ number_format(($order['amount'] ?? 0) / 100, 2) }}</p>
                <p class="text-sm text-gray-500">{{ strtoupper($order['currency'] ?? 'USD') }}</p>
            </div>
        </div>

        {{-- Meta details --}}
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-200">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Campaign / Program</p>
                <p class="text-sm font-medium text-gray-900 mt-1">{{ $order['campaign']['title'] ?? $order['venue_program']['title'] ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">User</p>
                <p class="text-sm font-medium text-gray-900 mt-1">{{ $order['user']['display_name'] ?? $order['user']['username'] ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Created</p>
                <p class="text-sm font-medium text-gray-900 mt-1">{{ \Carbon\Carbon::parse($order['created_at'] ?? '')->format('M j, Y g:i A') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">
                    @if(!empty($order['fulfilled_at']))
                        Fulfilled
                    @elseif(!empty($order['cancelled_at']))
                        Cancelled
                    @else
                        Last Updated
                    @endif
                </p>
                <p class="text-sm font-medium text-gray-900 mt-1">
                    @if(!empty($order['fulfilled_at']))
                        {{ \Carbon\Carbon::parse($order['fulfilled_at'])->format('M j, Y g:i A') }}
                    @elseif(!empty($order['cancelled_at']))
                        {{ \Carbon\Carbon::parse($order['cancelled_at'])->format('M j, Y g:i A') }}
                    @else
                        {{ \Carbon\Carbon::parse($order['updated_at'] ?? '')->format('M j, Y g:i A') }}
                    @endif
                </p>
            </div>
        </div>

        @if(!empty($order['cancellation_reason']))
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                <p class="text-sm text-red-800"><span class="font-medium">Cancellation reason:</span> {{ $order['cancellation_reason'] }}</p>
            </div>
        @endif

        @if(!empty($order['refund_deadline']))
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-sm text-blue-800">
                    <span class="font-medium">Refund deadline:</span> {{ \Carbon\Carbon::parse($order['refund_deadline'])->format('M j, Y') }}
                    @if(\Carbon\Carbon::parse($order['refund_deadline'])->isPast())
                        <span class="text-red-600 font-medium">(Expired)</span>
                    @endif
                </p>
            </div>
        @endif

        @if(!empty($order['seat_quantity']))
            <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-md">
                <p class="text-sm text-gray-800">
                    <span class="font-medium">Seats:</span> {{ $order['seat_quantity'] }}
                    @if(!empty($order['time_slot']))
                        &mdash; {{ \Carbon\Carbon::parse($order['time_slot']['starts_at'])->format('M j, Y g:i A') }} to {{ \Carbon\Carbon::parse($order['time_slot']['ends_at'])->format('g:i A') }}
                    @endif
                </p>
            </div>
        @endif
    </div>

    {{-- ================================================================== --}}
    {{-- 2. PAYMENT SECTION                                                 --}}
    {{-- ================================================================== --}}
    @php
        $payments = $order['payments'] ?? [];
        $completedPayments = array_filter($payments, fn($p) => ($p['status'] ?? '') === 'completed');
    @endphp
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment</h2>

        @if(count($completedPayments) > 0)
            {{-- Payment details --}}
            <div class="space-y-3">
                @foreach($payments as $payment)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 rounded-lg border
                        {{ ($payment['status'] ?? '') === 'completed' ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}
                    ">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ ucfirst(str_replace('_', ' ', $payment['method'] ?? '')) }}
                                &mdash;
                                ${{ number_format(($payment['amount'] ?? 0) / 100, 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                @if(!empty($payment['paid_at']))
                                    Paid {{ \Carbon\Carbon::parse($payment['paid_at'])->format('M j, Y g:i A') }}
                                @endif
                                @if(!empty($payment['transaction_ref']))
                                    &middot; Ref: {{ $payment['transaction_ref'] }}
                                @endif
                            </p>
                        </div>
                        <div class="mt-2 sm:mt-0">
                            @php
                                $payStatusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'refunded' => 'bg-gray-100 text-gray-800',
                                ];
                                $payColor = $payStatusColors[$payment['status'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payColor }}">
                                {{ ucfirst($payment['status'] ?? '') }}
                            </span>
                        </div>
                    </div>

                    {{-- Associated receipts --}}
                    @foreach($payment['receipts'] ?? [] as $receipt)
                        <div class="ml-4 p-3 bg-gray-50 border border-gray-200 rounded-md text-sm">
                            <p class="text-gray-700">
                                <span class="font-medium">Receipt:</span> {{ $receipt['receipt_number'] ?? '' }}
                                &middot; Generated {{ \Carbon\Carbon::parse($receipt['generated_at'] ?? '')->format('M j, Y g:i A') }}
                            </p>
                        </div>
                    @endforeach
                @endforeach
            </div>
        @else
            {{-- No payment yet --}}
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-4">
                <p class="text-sm text-yellow-800 font-medium">No payment recorded for this order.</p>
            </div>

            {{-- Staff: Record Payment Form --}}
            @if($isStaff)
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Record Payment</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="paymentMethod" class="block text-xs font-medium text-gray-600 mb-1">Method</label>
                            <select
                                id="paymentMethod"
                                wire:model="paymentMethod"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border px-3 py-2"
                            >
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="paymentAmount" class="block text-xs font-medium text-gray-600 mb-1">Amount (cents)</label>
                            <input
                                type="number"
                                id="paymentAmount"
                                wire:model="paymentAmount"
                                min="1"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border px-3 py-2"
                            >
                        </div>
                        <div>
                            <label for="transactionRef" class="block text-xs font-medium text-gray-600 mb-1">Transaction Ref</label>
                            <input
                                type="text"
                                id="transactionRef"
                                wire:model="transactionRef"
                                placeholder="Optional"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border px-3 py-2"
                            >
                        </div>
                    </div>
                    <div class="mt-4">
                        <button
                            wire:click="recordPayment"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <span wire:loading.remove wire:target="recordPayment">Record Payment</span>
                            <span wire:loading wire:target="recordPayment" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- ================================================================== --}}
    {{-- 3. VOUCHER SECTION                                                 --}}
    {{-- ================================================================== --}}
    @php $vouchers = $order['vouchers'] ?? []; @endphp
    @if(count($vouchers) > 0)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Vouchers</h2>

            <div class="space-y-4">
                @foreach($vouchers as $voucher)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                {{-- Large monospace code display --}}
                                <div class="bg-gray-900 text-green-400 font-mono text-2xl font-bold tracking-widest px-6 py-4 rounded-lg text-center select-all">
                                    {{ $voucher['code'] ?? '' }}
                                </div>
                            </div>
                            <div class="text-right space-y-1">
                                @php
                                    $voucherColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'redeemed' => 'bg-blue-100 text-blue-800',
                                        'expired' => 'bg-gray-100 text-gray-800',
                                        'revoked' => 'bg-red-100 text-red-800',
                                    ];
                                    $vColor = $voucherColors[$voucher['status'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $vColor }}">
                                    {{ ucfirst($voucher['status'] ?? '') }}
                                </span>
                                @if(!empty($voucher['expires_at']))
                                    <p class="text-xs text-gray-500">Expires: {{ \Carbon\Carbon::parse($voucher['expires_at'])->format('M j, Y') }}</p>
                                @endif
                            </div>
                        </div>
                        @if(!empty($voucher['redeemed_at']))
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md text-sm text-blue-800">
                                Redeemed on {{ \Carbon\Carbon::parse($voucher['redeemed_at'])->format('M j, Y g:i A') }}
                                @if(!empty($voucher['redeemed_by']))
                                    by {{ $voucher['redeemed_by']['display_name'] ?? $voucher['redeemed_by']['username'] ?? '' }}
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ================================================================== --}}
    {{-- 4. LOGISTICS MILESTONES                                            --}}
    {{-- ================================================================== --}}
    @php $milestones = $order['logistics_milestones'] ?? []; @endphp
    @if(count($milestones) > 0)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Logistics Milestones</h2>

            <div class="relative">
                {{-- Vertical timeline line --}}
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                <div class="space-y-6">
                    @foreach($milestones as $milestone)
                        @php
                            $milestoneStatusConfig = [
                                'pending' => ['ring' => 'ring-gray-300', 'bg' => 'bg-gray-200', 'icon' => 'bg-gray-400', 'text' => 'text-gray-500'],
                                'in_progress' => ['ring' => 'ring-blue-300', 'bg' => 'bg-blue-100', 'icon' => 'bg-blue-500', 'text' => 'text-blue-700'],
                                'completed' => ['ring' => 'ring-green-300', 'bg' => 'bg-green-100', 'icon' => 'bg-green-500', 'text' => 'text-green-700'],
                            ];
                            $mConfig = $milestoneStatusConfig[$milestone['status'] ?? 'pending'] ?? $milestoneStatusConfig['pending'];
                        @endphp
                        <div class="relative flex items-start pl-10">
                            {{-- Timeline dot --}}
                            <div class="absolute left-2 top-1 w-5 h-5 rounded-full ring-2 {{ $mConfig['ring'] }} {{ $mConfig['icon'] }} flex items-center justify-center">
                                @if(($milestone['status'] ?? '') === 'completed')
                                    <svg class="w-3 h-3 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>

                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-gray-900">{{ $milestone['title'] ?? '' }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $mConfig['bg'] }} {{ $mConfig['text'] }}">
                                        {{ ucfirst(str_replace('_', ' ', $milestone['status'] ?? '')) }}
                                    </span>
                                </div>
                                @if(!empty($milestone['description']))
                                    <p class="text-sm text-gray-500 mt-1">{{ $milestone['description'] }}</p>
                                @endif
                                @if(!empty($milestone['completed_at']))
                                    <p class="text-xs text-gray-400 mt-1">Completed {{ \Carbon\Carbon::parse($milestone['completed_at'])->format('M j, Y g:i A') }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================== --}}
    {{-- 5. ACTIONS SECTION                                                 --}}
    {{-- ================================================================== --}}
    @php
        $canCancel = $order['can_cancel'] ?? false;
        $canRefund = $order['can_refund'] ?? false;
        $hasPendingAfterSales = $order['has_pending_after_sales'] ?? false;
        $orderStatus = $order['status'] ?? '';
        $orderType = $order['order_type'] ?? '';

        $showActions = $canCancel
            || ($canRefund && !$hasPendingAfterSales)
            || $canRequestAfterSales
            || ($isStaff && $orderStatus === 'confirmed' && $isPaid)
            || ($isStaff && $orderType === 'reservation');
    @endphp

    @if($showActions)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>

            <div class="flex flex-wrap gap-3">
                {{-- Cancel --}}
                @if($canCancel)
                    <button
                        wire:click="openCancelModal"
                        class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Cancel Order
                    </button>
                @endif

                {{-- Request Refund --}}
                @if($canRefund && !$hasPendingAfterSales)
                    <button
                        wire:click="openRefundModal"
                        class="inline-flex items-center px-4 py-2 border border-yellow-300 text-sm font-medium rounded-md text-yellow-700 bg-white hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                    >
                        Request Refund
                    </button>
                @endif

                {{-- After-Sales --}}
                @if($canRequestAfterSales)
                    <button
                        wire:click="openAfterSalesModal"
                        class="inline-flex items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-md text-orange-700 bg-white hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                    >
                        After-Sales Request
                    </button>
                @endif

                {{-- Staff: Fulfill --}}
                @if($isStaff && $orderStatus === 'confirmed' && $isPaid)
                    <button
                        wire:click="markFulfilled"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        wire:confirm="Are you sure you want to mark this order as fulfilled?"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    >
                        <span wire:loading.remove wire:target="markFulfilled">Mark Fulfilled</span>
                        <span wire:loading wire:target="markFulfilled" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Processing...
                        </span>
                    </button>
                @endif

                {{-- Staff: Mark Attendance (Reservations) --}}
                @if($isStaff && $orderType === 'reservation')
                    <div class="flex items-center gap-2 ml-2">
                        <span class="text-sm text-gray-600 font-medium">Attendance:</span>
                        <button
                            wire:click="markAttended(true)"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-3 py-1.5 border text-sm font-medium rounded-md
                                {{ ($order['attended'] ?? null) === true
                                    ? 'border-green-500 bg-green-100 text-green-800'
                                    : 'border-gray-300 text-gray-600 bg-white hover:bg-gray-50' }}
                            "
                        >
                            <span wire:loading.remove wire:target="markAttended(true)">Attended</span>
                            <span wire:loading wire:target="markAttended(true)">...</span>
                        </button>
                        <button
                            wire:click="markAttended(false)"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-3 py-1.5 border text-sm font-medium rounded-md
                                {{ ($order['attended'] ?? null) === false
                                    ? 'border-red-500 bg-red-100 text-red-800'
                                    : 'border-gray-300 text-gray-600 bg-white hover:bg-gray-50' }}
                            "
                        >
                            <span wire:loading.remove wire:target="markAttended(false)">Not Attended</span>
                            <span wire:loading wire:target="markAttended(false)">...</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ================================================================== --}}
    {{-- 6. REFUND REQUESTS                                                 --}}
    {{-- ================================================================== --}}
    @php $refundRequests = $order['refund_requests'] ?? []; @endphp
    @if(count($refundRequests) > 0)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Refund Requests</h2>

            <div class="space-y-4">
                @foreach($refundRequests as $refundReq)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    @php
                                        $refundStatusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                        ];
                                        $rColor = $refundStatusColors[$refundReq['status'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $rColor }}">
                                        {{ ucfirst($refundReq['status'] ?? '') }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-900">${{ number_format(($refundReq['refund_amount'] ?? 0) / 100, 2) }}</span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $refundReq['reason'] ?? '' }}</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    By {{ $refundReq['requester']['display_name'] ?? $refundReq['requester']['username'] ?? 'Unknown' }}
                                    &middot; {{ \Carbon\Carbon::parse($refundReq['created_at'] ?? '')->format('M j, Y g:i A') }}
                                </p>
                                @if(!empty($refundReq['reviewer']))
                                    <p class="text-xs text-gray-400 mt-1">
                                        Reviewed by {{ $refundReq['reviewer']['display_name'] ?? $refundReq['reviewer']['username'] ?? '' }}
                                        @if(!empty($refundReq['reviewed_at']))
                                            on {{ \Carbon\Carbon::parse($refundReq['reviewed_at'])->format('M j, Y g:i A') }}
                                        @endif
                                    </p>
                                @endif
                            </div>

                            {{-- Staff inline approve/reject --}}
                            @if($isStaff && ($refundReq['status'] ?? '') === 'pending')
                                <div class="flex gap-2">
                                    <button
                                        wire:click="approveRefund({{ $refundReq['id'] }})"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                                    >
                                        <span wire:loading.remove wire:target="approveRefund({{ $refundReq['id'] }})">Approve</span>
                                        <span wire:loading wire:target="approveRefund({{ $refundReq['id'] }})">...</span>
                                    </button>
                                    <button
                                        wire:click="rejectRefund({{ $refundReq['id'] }})"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                                    >
                                        <span wire:loading.remove wire:target="rejectRefund({{ $refundReq['id'] }})">Reject</span>
                                        <span wire:loading wire:target="rejectRefund({{ $refundReq['id'] }})">...</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ================================================================== --}}
    {{-- 7. AFTER-SALES REQUESTS                                            --}}
    {{-- ================================================================== --}}
    @php $afterSalesRequests = $order['after_sales_requests'] ?? []; @endphp
    @if(count($afterSalesRequests) > 0)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">After-Sales Requests</h2>

            <div class="space-y-4">
                @foreach($afterSalesRequests as $afterSales)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    @php
                                        $asStatusColors = [
                                            'submitted' => 'bg-yellow-100 text-yellow-800',
                                            'under_review' => 'bg-blue-100 text-blue-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                        ];
                                        $asColor = $asStatusColors[$afterSales['status'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $asColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $afterSales['status'] ?? '')) }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ ucfirst($afterSales['type'] ?? '') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $afterSales['reason'] ?? '' }}</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    By {{ $afterSales['user']['display_name'] ?? $afterSales['user']['username'] ?? 'Unknown' }}
                                    &middot; {{ \Carbon\Carbon::parse($afterSales['created_at'] ?? '')->format('M j, Y g:i A') }}
                                </p>
                                @if(!empty($afterSales['staff_notes']))
                                    <div class="mt-2 p-2 bg-gray-50 border border-gray-200 rounded text-sm">
                                        <p class="text-xs text-gray-500 font-medium">Staff Notes:</p>
                                        <p class="text-gray-700">{{ $afterSales['staff_notes'] }}</p>
                                    </div>
                                @endif
                                @if(!empty($afterSales['resolver']))
                                    <p class="text-xs text-gray-400 mt-1">
                                        Resolved by {{ $afterSales['resolver']['display_name'] ?? $afterSales['resolver']['username'] ?? '' }}
                                        @if(!empty($afterSales['resolved_at']))
                                            on {{ \Carbon\Carbon::parse($afterSales['resolved_at'])->format('M j, Y g:i A') }}
                                        @endif
                                    </p>
                                @endif
                            </div>

                            {{-- Staff inline review/resolve --}}
                            @if($isStaff && in_array($afterSales['status'] ?? '', ['submitted', 'under_review']))
                                <div class="space-y-2 min-w-[160px]">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Staff Notes</label>
                                        <textarea
                                            wire:model="staffNotes"
                                            rows="2"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs border px-2 py-1"
                                            placeholder="Add notes..."
                                        ></textarea>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            wire:click="resolveAfterSales({{ $afterSales['id'] }}, 'approved')"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                                        >
                                            <span wire:loading.remove wire:target="resolveAfterSales({{ $afterSales['id'] }}, 'approved')">Approve</span>
                                            <span wire:loading wire:target="resolveAfterSales({{ $afterSales['id'] }}, 'approved')">...</span>
                                        </button>
                                        <button
                                            wire:click="resolveAfterSales({{ $afterSales['id'] }}, 'rejected')"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                                        >
                                            <span wire:loading.remove wire:target="resolveAfterSales({{ $afterSales['id'] }}, 'rejected')">Reject</span>
                                            <span wire:loading wire:target="resolveAfterSales({{ $afterSales['id'] }}, 'rejected')">...</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ================================================================== --}}
    {{-- 8. REVIEWS                                                         --}}
    {{-- ================================================================== --}}
    @php $reviews = $order['reviews'] ?? []; @endphp
    @if(count($reviews) > 0)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Reviews</h2>

            <div class="space-y-4">
                @foreach($reviews as $review)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center gap-3 mb-2">
                            {{-- Star rating --}}
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= ($review['overall_rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-200' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd" />
                                    </svg>
                                @endfor
                                <span class="ml-2 text-sm text-gray-600">{{ $review['overall_rating'] ?? 0 }}/5</span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                {{ ucfirst(str_replace('_', ' ', $review['side'] ?? '')) }}
                            </span>
                        </div>
                        @if(!empty($review['body']))
                            <p class="text-sm text-gray-700">{{ $review['body'] }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-2">
                            By {{ $review['public_alias'] ?? $review['reviewer']['display_name'] ?? $review['reviewer']['username'] ?? 'Anonymous' }}
                            &middot; {{ \Carbon\Carbon::parse($review['created_at'] ?? '')->format('M j, Y') }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(($order['status'] ?? '') === 'fulfilled')
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Reviews</h2>
            <p class="text-sm text-gray-500">No reviews yet. This order is eligible for review.</p>
        </div>
    @endif

    {{-- ================================================================== --}}
    {{-- CANCEL MODAL                                                       --}}
    {{-- ================================================================== --}}
    <div
        x-data="{ show: @entangle('showCancelModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="cancel-modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background overlay --}}
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="show = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="cancel-modal-title">Cancel Order</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to cancel this order? This action cannot be undone.</p>
                        </div>
                        <div class="mt-4">
                            <label for="cancelReason" class="block text-sm font-medium text-gray-700 text-left">Reason for cancellation</label>
                            <textarea
                                id="cancelReason"
                                wire:model="cancelReason"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm border px-3 py-2"
                                placeholder="Please provide a reason..."
                            ></textarea>
                            @error('cancelReason') <p class="mt-1 text-xs text-red-600 text-left">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button
                        wire:click="cancelOrder"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm"
                    >
                        <span wire:loading.remove wire:target="cancelOrder">Cancel Order</span>
                        <span wire:loading wire:target="cancelOrder" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Processing...
                        </span>
                    </button>
                    <button
                        wire:click="closeCancelModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                    >
                        Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- REFUND MODAL                                                       --}}
    {{-- ================================================================== --}}
    <div
        x-data="{ show: @entangle('showRefundModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="refund-modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="show = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                        <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="refund-modal-title">Request Refund</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Submit a refund request for this order. Staff will review and process your request.</p>
                        </div>
                        <div class="mt-4 space-y-4 text-left">
                            <div>
                                <label for="refundAmount" class="block text-sm font-medium text-gray-700">Refund Amount (cents)</label>
                                <input
                                    type="number"
                                    id="refundAmount"
                                    wire:model="refundAmount"
                                    min="1"
                                    max="{{ $order['amount'] ?? 0 }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-sm border px-3 py-2"
                                >
                                <p class="mt-1 text-xs text-gray-500">Order total: ${{ number_format(($order['amount'] ?? 0) / 100, 2) }}</p>
                                @error('refundAmount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="refundReason" class="block text-sm font-medium text-gray-700">Reason</label>
                                <textarea
                                    id="refundReason"
                                    wire:model="refundReason"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-sm border px-3 py-2"
                                    placeholder="Describe why you need a refund..."
                                ></textarea>
                                @error('refundReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button
                        wire:click="requestRefund"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:col-start-2 sm:text-sm"
                    >
                        <span wire:loading.remove wire:target="requestRefund">Submit Request</span>
                        <span wire:loading wire:target="requestRefund" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Processing...
                        </span>
                    </button>
                    <button
                        wire:click="closeRefundModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                    >
                        Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- AFTER-SALES MODAL                                                  --}}
    {{-- ================================================================== --}}
    <div
        x-data="{ show: @entangle('showAfterSalesModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="after-sales-modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="show = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100">
                        <svg class="h-6 w-6 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.653-4.655m0 0a2.678 2.678 0 0 1 3.586 0l4.655-5.653a2.548 2.548 0 0 1 3.586 3.586l-5.653 4.655" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="after-sales-modal-title">After-Sales Request</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Submit an after-sales request for this order. Our team will review and respond.</p>
                        </div>
                        <div class="mt-4 space-y-4 text-left">
                            <div>
                                <label for="afterSalesType" class="block text-sm font-medium text-gray-700">Request Type</label>
                                <select
                                    id="afterSalesType"
                                    wire:model="afterSalesType"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm border px-3 py-2"
                                >
                                    <option value="">Select a type...</option>
                                    @foreach($afterSalesTypes as $asType)
                                        <option value="{{ $asType }}">{{ ucfirst($asType) }}</option>
                                    @endforeach
                                </select>
                                @error('afterSalesType') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="afterSalesReason" class="block text-sm font-medium text-gray-700">Reason</label>
                                <textarea
                                    id="afterSalesReason"
                                    wire:model="afterSalesReason"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm border px-3 py-2"
                                    placeholder="Describe your issue..."
                                ></textarea>
                                @error('afterSalesReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Attachment (required)</label>
                                <input type="file" wire:model="afterSalesAttachment"
                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @error('afterSalesAttachment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button
                        wire:click="submitAfterSales"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:col-start-2 sm:text-sm"
                    >
                        <span wire:loading.remove wire:target="submitAfterSales">Submit Request</span>
                        <span wire:loading wire:target="submitAfterSales" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Processing...
                        </span>
                    </button>
                    <button
                        wire:click="closeAfterSalesModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                    >
                        Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Back to orders link --}}
    <div class="mt-6">
        <a href="{{ route('orders.list') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
            &larr; Back to Orders
        </a>
    </div>

</div>
