<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Campaigns</h1>

        @if(session('api_user'))
            <a href="{{ route('campaigns.create') }}" wire:navigate
               class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                + New Campaign
            </a>
        @endif
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search campaigns..."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border"
            />
        </div>
        <div class="w-full sm:w-48">
            <select
                wire:model.live="statusFilter"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border"
            >
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Campaign Grid --}}
    @if(empty($campaigns))
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">No campaigns found.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($campaigns as $campaign)
                @php
                    $targetAmount = $campaign['target_amount'] ?? 0;
                    $pledgedAmount = $campaign['pledged_amount'] ?? 0;
                    $progress = $targetAmount > 0
                        ? min(100, round(($pledgedAmount / $targetAmount) * 100, 1))
                        : 0;
                    $endsAt = $campaign['ends_at'] ?? null;
                    $daysRemaining = null;
                    $ended = false;
                    if ($endsAt) {
                        $endsAtDate = \Carbon\Carbon::parse($endsAt);
                        if ($endsAtDate->isFuture()) {
                            $daysRemaining = (int) now()->diffInDays($endsAtDate, false);
                        } else {
                            $ended = true;
                        }
                    }
                    $statusValue = $campaign['status'] ?? 'draft';
                @endphp
                <a href="{{ route('campaigns.detail', ['campaignId' => $campaign['id']]) }}" wire:navigate
                   class="block bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
                    <div class="p-5">
                        {{-- Title & Status --}}
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900 leading-tight line-clamp-2">
                                {{ $campaign['title'] }}
                            </h3>
                            <span class="ml-2 flex-shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
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
                        </div>

                        {{-- Creator --}}
                        <p class="text-sm text-gray-500 mb-3">
                            by {{ $campaign['creator']['display_name'] ?? $campaign['creator']['username'] ?? 'Unknown' }}
                        </p>

                        {{-- Progress Bar --}}
                        <div class="mb-2">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>${{ number_format($pledgedAmount / 100, 2) }}</span>
                                <span>${{ number_format($targetAmount / 100, 2) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300"
                                     style="width: {{ $progress }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $progress }}% funded</p>
                        </div>

                        {{-- Days Remaining --}}
                        <div class="text-sm text-gray-500">
                            @if($daysRemaining !== null)
                                <span>{{ $daysRemaining }} {{ Str::plural('day', $daysRemaining) }} remaining</span>
                            @elseif($ended)
                                <span class="text-red-600">Ended</span>
                            @else
                                <span>Not yet started</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if(($meta['last_page'] ?? 1) > 1)
            <div class="mt-8 flex items-center justify-center gap-2">
                <button wire:click="previousPage"
                        @if($page <= 1) disabled @endif
                        class="px-3 py-1 text-sm border rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <span class="text-sm text-gray-600">
                    Page {{ $meta['current_page'] ?? $page }} of {{ $meta['last_page'] ?? 1 }}
                </span>
                <button wire:click="nextPage"
                        @if($page >= ($meta['last_page'] ?? 1)) disabled @endif
                        class="px-3 py-1 text-sm border rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        @endif
    @endif
</div>
