<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Write a Review</h1>
    @if(!empty($order))
        <p class="text-gray-600 mb-6">
            For order #{{ $order['confirmation_number'] ?? $order['id'] ?? '' }}
            @if(!empty($order['campaign']))
                &mdash; {{ $order['campaign']['title'] ?? '' }}
            @endif
        </p>
    @endif

    @if($submitted)
        <div class="bg-white shadow rounded-lg p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Review Submitted!</h2>
            <p class="text-gray-600 mb-4">Thank you for your feedback. Your review will become publicly visible after a 72-hour review period.</p>
            <a href="{{ route('orders.list') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Back to Orders</a>
        </div>
    @else
        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($errorMessage)
                <div class="mx-6 mt-6 rounded-md bg-red-50 border border-red-200 p-4">
                    <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                </div>
            @endif

            <div class="px-6 py-6 space-y-8">
                {{-- Review Side --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reviewing as</label>
                    <select wire:model="side" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 border">
                        <option value="user_to_creator">Buyer reviewing Creator</option>
                        <option value="creator_to_user">Creator reviewing Buyer</option>
                    </select>
                </div>

                {{-- Overall Rating --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Overall Rating</label>
                    <div class="flex items-center space-x-1" x-data="{ hovered: 0 }">
                        @for($i = 1; $i <= 5; $i++)
                            <button
                                type="button"
                                wire:click="setOverallRating({{ $i }})"
                                @mouseenter="hovered = {{ $i }}"
                                @mouseleave="hovered = 0"
                                class="focus:outline-none"
                            >
                                <svg
                                    class="h-8 w-8 transition-colors"
                                    :class="(hovered >= {{ $i }} || {{ $i }} <= $wire.overallRating) ? 'text-yellow-400' : 'text-gray-300'"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </button>
                        @endfor
                        <span class="ml-2 text-sm text-gray-500">{{ $overallRating > 0 ? $overallRating . '/5' : 'Select a rating' }}</span>
                    </div>
                    @error('overallRating')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dimension Ratings --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Dimension Ratings</label>
                    <div class="space-y-4">
                        @foreach(['communication' => 'Communication', 'accuracy' => 'Accuracy', 'value' => 'Value'] as $dimKey => $dimLabel)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 w-32">{{ $dimLabel }}</span>
                                <div class="flex items-center space-x-1" x-data="{ hovered: 0 }">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button
                                            type="button"
                                            wire:click="setDimensionRating('{{ $dimKey }}', {{ $i }})"
                                            @mouseenter="hovered = {{ $i }}"
                                            @mouseleave="hovered = 0"
                                            class="focus:outline-none"
                                        >
                                            <svg
                                                class="h-6 w-6 transition-colors"
                                                :class="(hovered >= {{ $i }} || {{ $i }} <= {{ $dimensions[$dimKey] }}) ? 'text-yellow-400' : 'text-gray-300'"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </button>
                                    @endfor
                                    <span class="ml-2 text-xs text-gray-500 w-8">{{ $dimensions[$dimKey] > 0 ? $dimensions[$dimKey] . '/5' : '' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tags --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Tags</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($availableTags as $tag)
                            <button
                                type="button"
                                wire:click="toggleTag('{{ $tag }}')"
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium border transition-colors {{ in_array($tag, $tags) ? 'bg-indigo-100 text-indigo-800 border-indigo-300' : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200' }}"
                            >
                                @if(in_array($tag, $tags))
                                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                                {{ str_replace('_', ' ', ucfirst($tag)) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Body --}}
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                    <textarea
                        id="body"
                        wire:model="body"
                        rows="5"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border px-3 py-2"
                        placeholder="Share your experience..."
                    ></textarea>
                    @error('body')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="flex justify-end">
                    <button
                        wire:click="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="submit">Submit Review</span>
                        <span wire:loading wire:target="submit">Submitting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
