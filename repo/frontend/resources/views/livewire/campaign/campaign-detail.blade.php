<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" wire:poll.10s="refreshCampaign">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-2">
            <a href="{{ route('campaigns.list') }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 text-sm">&larr; Back to Campaigns</a>
        </div>

        @php
            $statusValue = $campaign['status'] ?? 'draft';
            $visibilityValue = $campaign['visibility'] ?? 'offline';
        @endphp

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-3xl font-bold text-gray-900">{{ $campaign['title'] ?? '' }}</h1>

            <div class="flex items-center gap-2 mt-2 sm:mt-0">
                {{-- Status Badge --}}
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @switch($statusValue)
                        @case('draft') bg-gray-100 text-gray-800 @break
                        @case('pending_review') bg-yellow-100 text-yellow-800 @break
                        @case('published') bg-blue-100 text-blue-800 @break
                        @case('fundraising') bg-green-100 text-green-800 @break
                        @case('success') bg-emerald-100 text-emerald-800 @break
                        @case('failure') bg-red-100 text-red-800 @break
                        @case('closed') bg-gray-100 text-gray-600 @break
                    @endswitch
                ">
                    {{ ucwords(str_replace('_', ' ', $statusValue)) }}
                </span>

                {{-- Visibility Badge --}}
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    {{ $visibilityValue === 'online' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                    {{ ucfirst($visibilityValue) }}
                </span>
            </div>
        </div>

        <p class="text-sm text-gray-500 mt-1">
            Created by {{ $campaign['creator']['display_name'] ?? $campaign['creator']['username'] ?? 'Unknown' }}
            @if($daysRemaining !== null)
                &middot; {{ $daysRemaining }} {{ Str::plural('day', $daysRemaining) }} remaining
            @elseif(isset($campaign['ends_at']) && $campaign['ends_at'] && \Carbon\Carbon::parse($campaign['ends_at'])->isPast())
                &middot; <span class="text-red-600">Campaign ended</span>
            @endif
        </p>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-3 mb-8">
        @if($isOwner && $statusValue === 'draft')
            <a href="{{ route('campaigns.edit', ['campaignId' => $campaign['id']]) }}" wire:navigate
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                Edit Campaign
            </a>
            <button wire:click="submitForReview"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-md hover:bg-yellow-600 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="submitForReview">Submit for Review</span>
                <span wire:loading wire:target="submitForReview">Submitting...</span>
            </button>
        @endif

        @if($isModerator && $statusValue === 'pending_review')
            <button wire:click="approve"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="approve">Approve</span>
                <span wire:loading wire:target="approve">Approving...</span>
            </button>

            <div x-data="{ showReject: false, reason: '' }">
                <button @click="showReject = true"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition">
                    Reject
                </button>

                {{-- Reject Modal --}}
                <div x-show="showReject" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showReject = false"></div>
                        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Campaign</h3>
                            <textarea x-model="reason" rows="4" placeholder="Reason for rejection..."
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"></textarea>
                            @error('rejectReason')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <div class="flex justify-end gap-3 mt-4">
                                <button @click="showReject = false"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition">
                                    Cancel
                                </button>
                                <button @click="$wire.reject(reason).then(() => { showReject = false; reason = ''; })"
                                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition">
                                    Confirm Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($isModerator)
            <button wire:click="toggleVisibility"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="toggleVisibility">
                    Toggle {{ $visibilityValue === 'online' ? 'Offline' : 'Online' }}
                </span>
                <span wire:loading wire:target="toggleVisibility">Toggling...</span>
            </button>

            @if(in_array($statusValue, ['success', 'failure']))
                <button wire:click="closeCampaign"
                        wire:loading.attr="disabled"
                        wire:confirm="Are you sure you want to close this campaign?"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="closeCampaign">Close Campaign</span>
                    <span wire:loading wire:target="closeCampaign">Closing...</span>
                </button>
            @endif
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Campaign Info --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Description --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Description</h2>
                <div class="prose prose-sm max-w-none text-gray-700">
                    {!! nl2br(e($campaign['description'] ?? '')) !!}
                </div>
            </div>

            {{-- Risk Disclosure --}}
            @if(!empty($campaign['risk_disclosure']))
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-yellow-800 mb-4">Risk Disclosure</h2>
                    <div class="prose prose-sm max-w-none text-yellow-700">
                        {!! nl2br(e($campaign['risk_disclosure'])) !!}
                    </div>
                </div>
            @endif

            {{-- Reward Tiers --}}
            @php $rewardTiers = $campaign['reward_tiers'] ?? []; @endphp
            @if(!empty($rewardTiers))
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Reward Tiers</h2>
                    <div class="space-y-4">
                        @foreach($rewardTiers as $tier)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $tier['title'] }}</h3>
                                        @if(!empty($tier['description']))
                                            <p class="text-sm text-gray-600 mt-1">{{ $tier['description'] }}</p>
                                        @endif
                                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                            <span class="font-medium text-indigo-600">${{ number_format(($tier['price'] ?? 0) / 100, 2) }}</span>
                                            @if(($tier['quantity_total'] ?? 0) > 0)
                                                <span>{{ ($tier['quantity_total'] ?? 0) - ($tier['quantity_claimed'] ?? 0) }} / {{ $tier['quantity_total'] }} remaining</span>
                                            @else
                                                <span>Unlimited</span>
                                            @endif
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                {{ ucfirst($tier['fulfillment_type'] ?? 'digital') }}
                                            </span>
                                        </div>
                                    </div>
                                    @if(session('api_user'))
                                        <button wire:click="contribute({{ $tier['id'] }})"
                                                wire:loading.attr="disabled"
                                                class="flex-shrink-0 inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition disabled:opacity-50">
                                            <span wire:loading.remove wire:target="contribute({{ $tier['id'] }})">Contribute</span>
                                            <span wire:loading wire:target="contribute({{ $tier['id'] }})">Processing...</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Time Slots --}}
            @php $timeSlots = $campaign['time_slots'] ?? []; @endphp
            @if(!empty($timeSlots))
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Time Slots</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($timeSlots as $slot)
                                    @php
                                        $seatCapacity = $slot['seat_capacity'] ?? 0;
                                        $seatsBooked = $slot['seats_booked'] ?? 0;
                                        $available = $seatCapacity - $seatsBooked;
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($slot['starts_at'])->format('M d, Y g:i A') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="{{ $available > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $available }} / {{ $seatCapacity }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if($available > 0)
                                                <a href="{{ route('booking.seat-map', ['timeSlotId' => $slot['id']]) }}" wire:navigate
                                                   class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700 transition">
                                                    Book Now
                                                </a>
                                            @else
                                                <span class="text-xs text-red-500 font-medium">Sold Out</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Reviews --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Reviews</h2>
                @if(empty($reviews))
                    <p class="text-gray-500 text-sm">No reviews yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($reviews as $review)
                            <div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ $review['public_alias'] ?? $review['reviewer']['display_name'] ?? 'Anonymous' }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($review['created_at'])->diffForHumans() }}
                                    </span>
                                </div>
                                <div class="flex items-center mb-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= ($review['overall_rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}"
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                @if(!empty($review['body']))
                                    <p class="text-sm text-gray-600">{{ $review['body'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Funding Summary --}}
        <div class="space-y-6">
            @php
                $pledgedAmount = $campaign['pledged_amount'] ?? 0;
                $targetAmount = $campaign['target_amount'] ?? 0;
            @endphp
            <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Funding Progress</h2>

                {{-- Amount --}}
                <div class="text-center mb-4">
                    <p class="text-3xl font-bold text-indigo-600">
                        ${{ number_format($pledgedAmount / 100, 2) }}
                    </p>
                    <p class="text-sm text-gray-500">
                        pledged of ${{ number_format($targetAmount / 100, 2) }} goal
                    </p>
                </div>

                {{-- Progress Bar --}}
                <div class="mb-4">
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-indigo-600 h-3 rounded-full transition-all duration-500"
                             style="width: {{ $progressPercent }}%"></div>
                    </div>
                    <p class="text-sm text-gray-500 mt-1 text-center">{{ $progressPercent }}% funded</p>
                </div>

                {{-- Stats --}}
                <div class="space-y-3 border-t border-gray-200 pt-4">
                    @if($daysRemaining !== null)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Days Remaining</span>
                            <span class="font-medium text-gray-900">{{ $daysRemaining }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Currency</span>
                        <span class="font-medium text-gray-900">{{ $campaign['currency'] ?? 'USD' }}</span>
                    </div>
                    @if(!empty($campaign['starts_at']))
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Started</span>
                            <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($campaign['starts_at'])->format('M d, Y') }}</span>
                        </div>
                    @endif
                    @if(!empty($campaign['ends_at']))
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Ends</span>
                            <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($campaign['ends_at'])->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
