<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('vouchers.list') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">&larr; Back to Vouchers</a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">Voucher Details</h1>
        </div>

        {{-- Voucher Code --}}
        <div class="px-6 py-8 bg-gray-50 text-center border-b border-gray-200">
            <p class="text-sm text-gray-500 mb-2">Voucher Code</p>
            <p class="font-mono text-4xl font-bold tracking-widest text-gray-900 select-all">{{ $voucher['code'] ?? '' }}</p>
        </div>

        {{-- Status --}}
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Status</span>
                <div>
                    @switch($voucher['status'] ?? '')
                        @case('active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Active</span>
                            @break
                        @case('redeemed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">Redeemed</span>
                            @break
                        @case('expired')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">Expired</span>
                            @break
                        @case('revoked')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Revoked</span>
                            @break
                    @endswitch
                </div>
            </div>
        </div>

        {{-- Order Info --}}
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Linked Order</span>
                @if(!empty($voucher['order']))
                    <span class="text-sm text-gray-900">#{{ $voucher['order']['confirmation_number'] ?? $voucher['order']['id'] ?? '' }}</span>
                @else
                    <span class="text-sm text-gray-400">N/A</span>
                @endif
            </div>
        </div>

        {{-- Event Info --}}
        @if(!empty($voucher['order']['campaign']))
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500">Event / Campaign</span>
                    <span class="text-sm text-gray-900">{{ $voucher['order']['campaign']['title'] ?? '' }}</span>
                </div>
            </div>
        @endif

        {{-- Event Date --}}
        @if(!empty($voucher['order']['time_slot']))
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500">Event Date</span>
                    <span class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($voucher['order']['time_slot']['starts_at'])->format('M d, Y H:i') }}</span>
                </div>
            </div>
        @endif

        {{-- Expiry --}}
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Expires</span>
                @if(!empty($voucher['expires_at']))
                    <span class="text-sm {{ \Carbon\Carbon::parse($voucher['expires_at'])->isPast() ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                        {{ \Carbon\Carbon::parse($voucher['expires_at'])->format('M d, Y H:i') }}
                    </span>
                @else
                    <span class="text-sm text-gray-400">No expiry</span>
                @endif
            </div>
        </div>

        {{-- Redeemed Info --}}
        @if(($voucher['status'] ?? '') === 'redeemed')
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500">Redeemed At</span>
                    <span class="text-sm text-gray-900">{{ !empty($voucher['redeemed_at']) ? \Carbon\Carbon::parse($voucher['redeemed_at'])->format('M d, Y H:i') : '' }}</span>
                </div>
            </div>
            @if(!empty($voucher['redeemed_by']))
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Redeemed By</span>
                        <span class="text-sm text-gray-900">{{ $voucher['redeemed_by']['display_name'] ?? $voucher['redeemed_by']['username'] ?? '' }}</span>
                    </div>
                </div>
            @endif
        @endif

        {{-- Redeem Button / Messages --}}
        @if($canRedeem)
            <div class="px-6 py-6">
                @if($redeemed)
                    <div class="rounded-md bg-green-50 border border-green-200 p-4 text-center">
                        <p class="text-sm font-medium text-green-800">Voucher redeemed successfully!</p>
                    </div>
                @elseif($errorMessage)
                    <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-4">
                        <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                    </div>
                    @if(($voucher['status'] ?? '') === 'active')
                        <button wire:click="redeem" wire:confirm="Are you sure you want to redeem this voucher?" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Redeem Voucher
                        </button>
                    @endif
                @elseif(($voucher['status'] ?? '') === 'active')
                    <button wire:click="redeem" wire:confirm="Are you sure you want to redeem this voucher?" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Redeem Voucher
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
