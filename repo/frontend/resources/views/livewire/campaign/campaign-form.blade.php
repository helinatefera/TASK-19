<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('campaigns.list') }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 text-sm">&larr; Back to Campaigns</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">
            {{ $isEditing ? 'Edit Campaign' : 'Create Campaign' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-8">
        {{-- Campaign Details --}}
        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <h2 class="text-lg font-semibold text-gray-900">Campaign Details</h2>

            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" id="title" wire:model="title"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
                       placeholder="Enter campaign title">
                @error('title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" wire:model="description" rows="6"
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
                          placeholder="Describe your campaign..."></textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Risk Disclosure --}}
            <div>
                <label for="risk_disclosure" class="block text-sm font-medium text-gray-700 mb-1">Risk Disclosure</label>
                <textarea id="risk_disclosure" wire:model="risk_disclosure" rows="3"
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
                          placeholder="Disclose potential risks to backers (optional)"></textarea>
                @error('risk_disclosure')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Target Amount & Duration --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="target_amount" class="block text-sm font-medium text-gray-700 mb-1">Target Amount (USD)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                        <input type="number" id="target_amount" wire:model="target_amount" step="0.01" min="1"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border pl-7 pr-3 py-2"
                               placeholder="10000.00">
                    </div>
                    @error('target_amount')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-1">Duration (days)</label>
                    <input type="number" id="duration_days" wire:model="duration_days" min="1" max="365"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
                           placeholder="30">
                    @error('duration_days')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Reward Tiers --}}
        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Reward Tiers</h2>
                <button type="button" wire:click="addRewardTier"
                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                    + Add Tier
                </button>
            </div>

            @if(count($rewardTiers) === 0)
                <p class="text-gray-500 text-sm">No reward tiers yet. Click "Add Tier" to create one.</p>
            @endif

            @foreach($rewardTiers as $index => $tier)
                <div class="border border-gray-200 rounded-lg p-4 space-y-4" wire:key="tier-{{ $index }}">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">Tier #{{ $index + 1 }}</h3>
                        <button type="button" wire:click="removeRewardTier({{ $index }})"
                                class="text-red-600 hover:text-red-800 text-sm font-medium">
                            Remove
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Tier Title --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" wire:model="rewardTiers.{{ $index }}.title"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
                                   placeholder="Tier title">
                            @error("rewardTiers.{$index}.title")
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tier Price --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price (USD)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                                <input type="number" wire:model="rewardTiers.{{ $index }}.price" step="0.01" min="0.01"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border pl-7 pr-3 py-2"
                                       placeholder="25.00">
                            </div>
                            @error("rewardTiers.{$index}.price")
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Tier Description --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model="rewardTiers.{{ $index }}.description" rows="2"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
                                  placeholder="What backers receive at this tier"></textarea>
                        @error("rewardTiers.{$index}.description")
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Quantity --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity (0 = unlimited)</label>
                            <input type="number" wire:model="rewardTiers.{{ $index }}.quantity" min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2"
                                   placeholder="0">
                            @error("rewardTiers.{$index}.quantity")
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fulfillment Type --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fulfillment Type</label>
                            <select wire:model="rewardTiers.{{ $index }}.fulfillment_type"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-3 py-2">
                                @foreach($fulfillmentTypes as $type)
                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                            @error("rewardTiers.{$index}.fulfillment_type")
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('campaigns.list') }}" wire:navigate
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition">
                Cancel
            </a>
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="save">
                    {{ $isEditing ? 'Update Campaign' : 'Create Campaign' }}
                </span>
                <span wire:loading wire:target="save">
                    Saving...
                </span>
            </button>
        </div>
    </form>
</div>
