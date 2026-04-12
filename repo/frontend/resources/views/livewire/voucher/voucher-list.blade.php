<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Vouchers</h1>

    @if(count($vouchers) === 0)
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            You don't have any vouchers yet.
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($vouchers as $voucher)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm font-semibold text-gray-900">{{ $voucher['code'] ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($voucher['status'] ?? '')
                                    @case('active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                        @break
                                    @case('redeemed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Redeemed</span>
                                        @break
                                    @case('expired')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Expired</span>
                                        @break
                                    @case('revoked')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Revoked</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if(!empty($voucher['order']))
                                    <span>#{{ $voucher['order']['confirmation_number'] ?? $voucher['order']['id'] ?? '' }}</span>
                                    @if(!empty($voucher['order']['campaign']))
                                        <span class="text-gray-500">- {{ Str::limit($voucher['order']['campaign']['title'] ?? '', 30) }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if(!empty($voucher['order']['time_slot']['starts_at']))
                                    {{ \Carbon\Carbon::parse($voucher['order']['time_slot']['starts_at'])->format('M d, Y H:i') }}
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if(!empty($voucher['expires_at']))
                                    <span class="{{ \Carbon\Carbon::parse($voucher['expires_at'])->isPast() ? 'text-red-600' : 'text-gray-700' }}">
                                        {{ \Carbon\Carbon::parse($voucher['expires_at'])->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-400">No expiry</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('vouchers.detail', ['voucherId' => $voucher['id']]) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(($meta['last_page'] ?? 1) > 1)
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Page {{ $meta['current_page'] ?? 1 }} of {{ $meta['last_page'] ?? 1 }}
                    ({{ $meta['total'] ?? 0 }} total)
                </div>
                <div class="flex gap-2">
                    @if(($meta['current_page'] ?? 1) > 1)
                        <button
                            wire:click="previousPage"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >Previous</button>
                    @endif
                    @if(($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1))
                        <button
                            wire:click="nextPage"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >Next</button>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
