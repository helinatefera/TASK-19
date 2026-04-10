<div>
    @if(count($reviews) === 0)
        <div class="text-center py-8 text-gray-500">
            No reviews yet for this campaign.
        </div>
    @else
        <div class="space-y-6">
            @foreach($reviews as $review)
                <div class="bg-white border border-gray-200 rounded-lg p-5">
                    {{-- Header: alias and date --}}
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-gray-700">{{ $review['public_alias'] ?? 'Anonymous' }}</span>
                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($review['created_at'] ?? '')->format('M d, Y') }}</span>
                    </div>

                    {{-- Overall stars --}}
                    <div class="flex items-center mb-3">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="h-5 w-5 {{ $i <= ($review['overall_rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                        <span class="ml-2 text-sm text-gray-600">{{ $review['overall_rating'] ?? 0 }}/5</span>
                    </div>

                    {{-- Body --}}
                    @if(!empty($review['body']))
                        <p class="text-sm text-gray-700 mb-3">{{ $review['body'] }}</p>
                    @endif

                    {{-- Dimension ratings --}}
                    @php $dimensions = $review['dimensions'] ?? []; @endphp
                    @if(count($dimensions) > 0)
                        <div class="flex flex-wrap gap-4 mb-3">
                            @foreach($dimensions as $dimension)
                                <div class="flex items-center space-x-1">
                                    <span class="text-xs font-medium text-gray-500 capitalize">{{ $dimension['dimension'] ?? '' }}:</span>
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="h-3.5 w-3.5 {{ $i <= ($dimension['rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Tags --}}
                    @php $reviewTags = $review['tags'] ?? []; @endphp
                    @if(count($reviewTags) > 0)
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($reviewTags as $tag)
                                @php $tagValue = is_array($tag) ? ($tag['tag'] ?? '') : $tag; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                    {{ str_replace('_', ' ', ucfirst($tagValue)) }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if(($meta['last_page'] ?? 1) > 1)
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Page {{ $meta['current_page'] ?? 1 }} of {{ $meta['last_page'] ?? 1 }}
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
