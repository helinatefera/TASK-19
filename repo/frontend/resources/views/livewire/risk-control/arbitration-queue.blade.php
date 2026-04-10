<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Arbitration Queue</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Dispute List --}}
        <div class="lg:col-span-1">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-700">Pending Disputes ({{ count($disputes) }})</h2>
                </div>

                @if(empty($disputes))
                    <div class="p-6 text-center text-gray-500 text-sm">
                        No pending disputes.
                    </div>
                @else
                    <ul class="divide-y divide-gray-200 max-h-[600px] overflow-y-auto">
                        @foreach($disputes as $dispute)
                            <li
                                wire:click="selectDispute({{ $dispute['id'] }})"
                                class="px-4 py-3 cursor-pointer transition-colors {{ $selectedDisputeId === $dispute['id'] ? 'bg-indigo-50 border-l-4 border-indigo-500' : 'hover:bg-gray-50' }}"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900">Dispute #{{ $dispute['id'] }}</span>
                                    @switch($dispute['status'] ?? '')
                                        @case('open')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Open</span>
                                            @break
                                        @case('under_review')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Under Review</span>
                                            @break
                                        @case('escalated')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Escalated</span>
                                            @break
                                        @case('resolved')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Resolved</span>
                                            @break
                                    @endswitch
                                </div>
                                <p class="text-xs text-gray-600 truncate">{{ Str::limit($dispute['reason'] ?? '', 60) }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($dispute['created_at'])->diffForHumans() }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Dispute Detail & Decision --}}
        <div class="lg:col-span-2">
            @if($selectedDispute)
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    {{-- Dispute Header --}}
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Dispute #{{ $selectedDispute['id'] }}</h2>
                        <button wire:click="clearSelection" class="text-sm text-gray-500 hover:text-gray-700">Close</button>
                    </div>

                    {{-- Dispute Info --}}
                    <div class="px-6 py-4 border-b border-gray-200 space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500">Initiator</p>
                                <p class="text-sm text-gray-900">{{ $selectedDispute['initiator']['username'] ?? 'Unknown' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Respondent</p>
                                <p class="text-sm text-gray-900">{{ $selectedDispute['respondent']['username'] ?? 'Unknown' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Order</p>
                                <p class="text-sm text-gray-900">#{{ $selectedDispute['order']['confirmation_number'] ?? $selectedDispute['order_id'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Campaign</p>
                                <p class="text-sm text-gray-900">{{ $selectedDispute['order']['campaign']['title'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Status</p>
                                <p class="text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $selectedDispute['status'] ?? '') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Filed</p>
                                <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($selectedDispute['created_at'])->format('M d, Y H:i') }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-gray-500 mb-1">Reason</p>
                            <p class="text-sm text-gray-700 bg-gray-50 rounded-md p-3">{{ $selectedDispute['reason'] ?? '' }}</p>
                        </div>
                    </div>

                    {{-- Decision History --}}
                    @if(!empty($selectedDispute['decisions']))
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Decision History</h3>
                            <div class="space-y-3">
                                @foreach($selectedDispute['decisions'] as $past)
                                    <div class="bg-gray-50 rounded-md p-3 border border-gray-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-medium text-gray-500">
                                                By {{ $past['decided_by_username'] ?? $past['decided_by']['username'] ?? 'Unknown' }}
                                                on {{ \Carbon\Carbon::parse($past['created_at'])->format('M d, Y H:i') }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ ($past['decision'] ?? '') === 'favor_initiator' ? 'bg-blue-100 text-blue-800' :
                                                   (($past['decision'] ?? '') === 'favor_respondent' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800') }}">
                                                {{ str_replace('_', ' ', ucfirst($past['decision'] ?? '')) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-700 mb-1"><strong>Reasoning:</strong> {{ $past['reasoning'] ?? '' }}</p>
                                        @if(!empty($past['action_taken']))
                                            <p class="text-sm text-gray-700"><strong>Action:</strong> {{ $past['action_taken'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Decision Form --}}
                    @if(($selectedDispute['status'] ?? '') !== 'resolved')
                        <div class="px-6 py-4">
                            @if($decisionSubmitted)
                                <div class="rounded-md bg-green-50 border border-green-200 p-4 text-center">
                                    <p class="text-sm font-medium text-green-800">Decision submitted successfully.</p>
                                </div>
                            @else
                                <h3 class="text-sm font-semibold text-gray-700 mb-4">Make a Decision</h3>

                                @if($errorMessage)
                                    <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-3">
                                        <p class="text-sm text-red-800">{{ $errorMessage }}</p>
                                    </div>
                                @endif

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Decision</label>
                                        <select
                                            wire:model="decision"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
                                        >
                                            <option value="">Select a decision...</option>
                                            <option value="favor_initiator">Favor Initiator</option>
                                            <option value="favor_respondent">Favor Respondent</option>
                                            <option value="dismissed">Dismissed</option>
                                        </select>
                                        @error('decision')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Reasoning</label>
                                        <textarea
                                            wire:model="reasoning"
                                            rows="4"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
                                            placeholder="Provide detailed reasoning for this decision..."
                                        ></textarea>
                                        @error('reasoning')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Action Taken (optional)</label>
                                        <textarea
                                            wire:model="actionTaken"
                                            rows="2"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
                                            placeholder="Describe any actions taken..."
                                        ></textarea>
                                    </div>

                                    <div class="flex justify-end">
                                        <button
                                            wire:click="decide"
                                            wire:loading.attr="disabled"
                                            wire:confirm="Are you sure? This decision is immutable and cannot be changed."
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                        >
                                            <span wire:loading.remove wire:target="decide">Submit Decision</span>
                                            <span wire:loading wire:target="decide">Submitting...</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-white shadow rounded-lg p-12 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-sm">Select a dispute from the queue to view details and make a decision.</p>
                </div>
            @endif
        </div>
    </div>
</div>
