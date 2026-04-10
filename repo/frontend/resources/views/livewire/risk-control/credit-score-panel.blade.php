<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Credit Scores</h1>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Shows</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chargebacks</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Refunds</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Violations</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restriction</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Until</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($scores as $creditScore)
                    <tr class="cursor-pointer hover:bg-gray-50" wire:click="toggleExpand({{ $creditScore['user_id'] }})">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $creditScore['user']['username'] ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $score = $creditScore['score'] ?? 0;
                                if ($score > 600) {
                                    $scoreColor = 'text-green-700 bg-green-100';
                                } elseif ($score >= 300) {
                                    $scoreColor = 'text-yellow-700 bg-yellow-100';
                                } else {
                                    $scoreColor = 'text-red-700 bg-red-100';
                                }
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-bold {{ $scoreColor }}">
                                {{ $score }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $creditScore['no_show_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $creditScore['chargeback_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $creditScore['refund_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $creditScore['violation_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($creditScore['restriction_level'] ?? 'none')
                                @case('none')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">None</span>
                                    @break
                                @case('gray')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Gray</span>
                                    @break
                                @case('black')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Black</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ !empty($creditScore['restriction_until']) ? \Carbon\Carbon::parse($creditScore['restriction_until'])->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                            <svg class="h-5 w-5 transition-transform {{ $expandedUserId === $creditScore['user_id'] ? 'rotate-90' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </td>
                    </tr>

                    {{-- Expanded Details --}}
                    @if($expandedUserId === $creditScore['user_id'])
                        <tr>
                            <td colspan="9" class="px-6 py-4 bg-gray-50">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div class="bg-white rounded-lg border p-4">
                                        <p class="text-xs font-medium text-gray-500 mb-1">User</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ $creditScore['user']['display_name'] ?? $creditScore['user']['username'] ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500">ID: {{ $creditScore['user_id'] }}</p>
                                    </div>
                                    <div class="bg-white rounded-lg border p-4">
                                        <p class="text-xs font-medium text-gray-500 mb-1">Credit Score</p>
                                        <p class="text-2xl font-bold {{ $score > 600 ? 'text-green-600' : ($score >= 300 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $score }}
                                        </p>
                                    </div>
                                    <div class="bg-white rounded-lg border p-4">
                                        <p class="text-xs font-medium text-gray-500 mb-1">Incident Counts</p>
                                        <div class="space-y-1 text-sm">
                                            <div class="flex justify-between"><span class="text-gray-600">No-shows:</span> <span class="font-medium">{{ $creditScore['no_show_count'] ?? 0 }}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Chargebacks:</span> <span class="font-medium">{{ $creditScore['chargeback_count'] ?? 0 }}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Refunds:</span> <span class="font-medium">{{ $creditScore['refund_count'] ?? 0 }}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Violations:</span> <span class="font-medium">{{ $creditScore['violation_count'] ?? 0 }}</span></div>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-lg border p-4">
                                        <p class="text-xs font-medium text-gray-500 mb-1">Restriction</p>
                                        <p class="text-sm font-semibold capitalize">{{ $creditScore['restriction_level'] ?? 'none' }}</p>
                                        @if(!empty($creditScore['restriction_until']))
                                            <p class="text-xs text-gray-500 mt-1">Until: {{ \Carbon\Carbon::parse($creditScore['restriction_until'])->format('M d, Y H:i') }}</p>
                                        @endif
                                        @if(!empty($creditScore['updated_at']))
                                            <p class="text-xs text-gray-500 mt-1">Last updated: {{ \Carbon\Carbon::parse($creditScore['updated_at'])->format('M d, Y H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">No credit scores found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if(!empty($meta))
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Page {{ $meta['current_page'] ?? $page }} of {{ $meta['last_page'] ?? 1 }}
            </div>
            <div class="flex space-x-2">
                @if(($meta['current_page'] ?? 1) > 1)
                    <button wire:click="previousPage" class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Previous</button>
                @endif
                @if(($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1))
                    <button wire:click="nextPage" class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Next</button>
                @endif
            </div>
        </div>
    @endif
</div>
