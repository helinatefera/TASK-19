<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $isStaff ? 'All Orders' : 'My Orders' }}</h1>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="sm:w-48">
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select
                    id="statusFilter"
                    wire:model.live="statusFilter"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border px-3 py-2"
                >
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:w-48">
                <label for="typeFilter" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select
                    id="typeFilter"
                    wire:model.live="typeFilter"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border px-3 py-2"
                >
                    <option value="">All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if(count($orders) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Confirmation</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaign / Program</th>
                            @if($isStaff)
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            @endif
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-mono font-medium text-indigo-600">{{ $order['confirmation_number'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ ($order['order_type'] ?? '') === 'contribution'
                                            ? 'bg-purple-100 text-purple-800'
                                            : 'bg-teal-100 text-teal-800' }}
                                    ">
                                        {{ ucfirst($order['order_type'] ?? '') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $order['campaign']['title'] ?? $order['venue_program']['title'] ?? '-' }}
                                </td>
                                @if($isStaff)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order['user']['display_name'] ?? $order['user']['username'] ?? '-' }}
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    ${{ number_format(($order['amount'] ?? 0) / 100, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-blue-100 text-blue-800',
                                            'fulfilled' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'refunded' => 'bg-gray-100 text-gray-800',
                                            'after_sales' => 'bg-orange-100 text-orange-800',
                                        ];
                                        $color = $statusColors[$order['status'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                        {{ ucfirst(str_replace('_', ' ', $order['status'] ?? '')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($order['created_at'] ?? '')->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a
                                        href="{{ route('orders.detail', ['orderId' => $order['id']]) }}"
                                        class="text-indigo-600 hover:text-indigo-900"
                                        wire:navigate
                                    >View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(($meta['last_page'] ?? 1) > 1)
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
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
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($statusFilter || $typeFilter)
                        Try adjusting your filters.
                    @else
                        You have no orders yet.
                    @endif
                </p>
            </div>
        @endif
    </div>

</div>
