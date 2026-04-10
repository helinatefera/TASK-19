<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Venue Programs</h1>
    </div>

    {{-- Search --}}
    <div class="mb-6">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search programs by title, description, or location..."
            class="w-full sm:max-w-md rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border"
        />
    </div>

    {{-- Program Grid --}}
    @if(empty($programs))
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">No venue programs found.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($programs as $program)
                @php
                    $timeSlots = $program['time_slots'] ?? [];
                    $nextSlot = collect($timeSlots)
                        ->filter(fn ($slot) => isset($slot['starts_at']) && \Carbon\Carbon::parse($slot['starts_at'])->isFuture())
                        ->sortBy('starts_at')
                        ->first();
                @endphp
                <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
                    <div class="p-5">
                        {{-- Title --}}
                        <h3 class="text-lg font-semibold text-gray-900 leading-tight line-clamp-2 mb-2">
                            {{ $program['title'] }}
                        </h3>

                        {{-- Location --}}
                        @if(!empty($program['location']))
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="truncate">{{ $program['location'] }}</span>
                            </div>
                        @endif

                        {{-- Description --}}
                        @if(!empty($program['description']))
                            <p class="text-sm text-gray-600 line-clamp-3 mb-3">{{ $program['description'] }}</p>
                        @endif

                        {{-- Creator --}}
                        <p class="text-xs text-gray-400 mb-3">
                            by {{ $program['creator']['display_name'] ?? $program['creator']['username'] ?? 'Unknown' }}
                        </p>

                        {{-- Next Event --}}
                        <div class="border-t border-gray-100 pt-3">
                            @if($nextSlot)
                                @php
                                    $slotCapacity = $nextSlot['seat_capacity'] ?? 0;
                                    $slotBooked = $nextSlot['seats_booked'] ?? 0;
                                    $slotAvailable = $slotCapacity - $slotBooked;
                                @endphp
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wider">Next Event</p>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($nextSlot['starts_at'])->format('M d, Y g:i A') }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $slotAvailable }} {{ Str::plural('seat', $slotAvailable) }} available
                                        </p>
                                    </div>
                                    @if($slotAvailable > 0)
                                        <a href="{{ route('booking.seat-map', ['timeSlotId' => $nextSlot['id']]) }}" wire:navigate
                                           class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700 transition">
                                            Book
                                        </a>
                                    @else
                                        <span class="text-xs text-red-500 font-medium">Sold Out</span>
                                    @endif
                                </div>
                            @else
                                <p class="text-sm text-gray-400">No upcoming events</p>
                            @endif
                        </div>
                    </div>
                </div>
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
