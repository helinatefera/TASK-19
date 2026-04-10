<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Business Parameters</h1>

    @if(empty($parameters))
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            No business parameters configured.
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($parameters as $param)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-medium text-gray-900">
                                {{ $param['key'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(in_array($param['type'] ?? '', ['bool', 'boolean']))
                                    <label class="inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            wire:model="editValues.{{ $param['key'] }}"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            {{ filter_var($editValues[$param['key']] ?? $param['value'], FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}
                                        >
                                        <span class="ml-2 text-sm text-gray-700">{{ filter_var($editValues[$param['key']] ?? $param['value'], FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No' }}</span>
                                    </label>
                                @elseif(in_array($param['type'] ?? '', ['int', 'integer', 'float', 'double']))
                                    <input
                                        type="number"
                                        wire:model="editValues.{{ $param['key'] }}"
                                        step="{{ in_array($param['type'] ?? '', ['float', 'double']) ? '0.01' : '1' }}"
                                        class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-1.5 px-2"
                                    >
                                @else
                                    <input
                                        type="text"
                                        wire:model="editValues.{{ $param['key'] }}"
                                        class="block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-1.5 px-2"
                                    >
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">{{ $param['type'] ?? 'string' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $param['description'] ?? '' }}">
                                {{ $param['description'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    wire:click="save('{{ $param['key'] }}')"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    Save
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
