<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" wire:poll.30s>
    <h1 class="text-2xl font-bold text-gray-900 mb-8">Approval Queue</h1>

    @if($items->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="mt-4 text-gray-500 text-lg">No items pending review.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creator</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($items as $item)
                        <tr wire:key="item-{{ $item['type'] }}-{{ $item['id'] }}">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $item['title'] }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $item['type'] === 'campaign' ? 'bg-indigo-100 text-indigo-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ ucfirst($item['type']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $item['creator'] }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($item['submitted_at'])->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @if($item['type'] === 'campaign')
                                    <button wire:click="approveCampaign({{ $item['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700 transition disabled:opacity-50">
                                        <span wire:loading.remove wire:target="approveCampaign({{ $item['id'] }})">Approve</span>
                                        <span wire:loading wire:target="approveCampaign({{ $item['id'] }})">...</span>
                                    </button>
                                @else
                                    <button wire:click="approveProgram({{ $item['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700 transition disabled:opacity-50">
                                        <span wire:loading.remove wire:target="approveProgram({{ $item['id'] }})">Approve</span>
                                        <span wire:loading wire:target="approveProgram({{ $item['id'] }})">...</span>
                                    </button>
                                @endif

                                <button wire:click="openRejectModal({{ $item['id'] }}, '{{ $item['type'] }}')"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700 transition">
                                    Reject
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Reject Modal --}}
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                     wire:click="closeRejectModal"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Reject {{ ucfirst($rejectItemType) }}
                    </h3>
                    <div>
                        <label for="rejectReasonInput" class="block text-sm font-medium text-gray-700 mb-1">Reason for rejection</label>
                        <textarea id="rejectReasonInput" wire:model="rejectReason" rows="4"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
                                  placeholder="Explain why this item is being rejected..."></textarea>
                        @error('rejectReason')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-3 mt-4">
                        <button wire:click="closeRejectModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition">
                            Cancel
                        </button>
                        <button wire:click="confirmReject"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="confirmReject">Confirm Reject</span>
                            <span wire:loading wire:target="confirmReject">Rejecting...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
