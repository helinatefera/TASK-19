<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Audit Log</h1>

    {{-- Filters --}}
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Action</label>
                <select
                    wire:model.live="actionFilter"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
                >
                    <option value="">All Actions</option>
                    @foreach($distinctActions as $action)
                        <option value="{{ $action }}">{{ $action }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Actor</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="actorFilter"
                    placeholder="Search by username..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
                >
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input
                    type="date"
                    wire:model.live="dateFrom"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
                >
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input
                    type="date"
                    wire:model.live="dateTo"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
                >
            </div>
        </div>
    </div>

    {{-- Log Table --}}
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changes</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr x-data="{ expanded: false }">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($log['created_at'])->format('M d, Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log['actor']['username'] ?? $log['actor_username'] ?? 'System' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $log['action'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @php
                                $auditableType = $log['auditable_type'] ?? '';
                                $basename = class_basename($auditableType);
                            @endphp
                            <span class="font-mono text-xs">{{ $basename }}</span>
                            <span class="text-gray-400">#{{ $log['auditable_id'] ?? '' }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if(!empty($log['old_values']) || !empty($log['new_values']))
                                <button @click="expanded = !expanded" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                    <span x-show="!expanded">Show details</span>
                                    <span x-show="expanded">Hide details</span>
                                </button>
                                <div x-show="expanded" x-cloak class="mt-2 space-y-2">
                                    @if(!empty($log['old_values']))
                                        <div>
                                            <span class="text-xs font-medium text-red-600">Old values:</span>
                                            <pre class="mt-1 text-xs bg-red-50 border border-red-100 rounded p-2 overflow-x-auto max-w-md">{{ json_encode($log['old_values'], JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @endif
                                    @if(!empty($log['new_values']))
                                        <div>
                                            <span class="text-xs font-medium text-green-600">New values:</span>
                                            <pre class="mt-1 text-xs bg-green-50 border border-green-100 rounded p-2 overflow-x-auto max-w-md">{{ json_encode($log['new_values'], JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400">No changes recorded</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No audit log entries found.</td>
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
