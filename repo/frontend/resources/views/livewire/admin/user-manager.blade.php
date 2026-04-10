<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
        <button
            wire:click="openCreateModal"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            Create User
        </button>
    </div>

    {{-- Search --}}
    <div class="mb-6">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search by username or display name..."
            class="block w-full sm:w-96 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
        >
    </div>

    {{-- Users Table --}}
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Display Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr x-data="{ editing: false }">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $user['username'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $user['display_name'] ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div x-show="!editing">
                                <div class="flex flex-wrap gap-1 items-center">
                                    @php
                                        $userRoles = $user['roles'] ?? [];
                                    @endphp
                                    @forelse($userRoles as $role)
                                        @php
                                            $roleName = is_array($role) ? ($role['name'] ?? '') : $role;
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $roleName }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400">No roles</span>
                                    @endforelse
                                    <button @click="editing = true" class="ml-2 text-xs text-indigo-600 hover:text-indigo-800">Edit</button>
                                </div>
                            </div>
                            <div x-show="editing" x-cloak>
                                @php
                                    $userRoleNames = collect($userRoles)->map(fn($r) => is_array($r) ? ($r['name'] ?? '') : $r)->toArray();
                                @endphp
                                <div
                                    x-data="{
                                        selectedRoles: @js($userRoleNames),
                                        toggleRole(role) {
                                            const idx = this.selectedRoles.indexOf(role);
                                            if (idx > -1) {
                                                this.selectedRoles.splice(idx, 1);
                                            } else {
                                                this.selectedRoles.push(role);
                                            }
                                        },
                                        save() {
                                            $wire.updateRoles({{ $user['id'] }}, this.selectedRoles);
                                            editing = false;
                                        }
                                    }"
                                    class="flex flex-wrap gap-2 items-center"
                                >
                                    @foreach($allRoles as $role)
                                        @php
                                            $roleName = is_array($role) ? ($role['name'] ?? '') : $role;
                                        @endphp
                                        <label class="inline-flex items-center space-x-1 text-xs">
                                            <input
                                                type="checkbox"
                                                :checked="selectedRoles.includes('{{ $roleName }}')"
                                                @change="toggleRole('{{ $roleName }}')"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            >
                                            <span>{{ $roleName }}</span>
                                        </label>
                                    @endforeach
                                    <button @click="save()" class="ml-2 text-xs text-green-600 hover:text-green-800 font-medium">Save</button>
                                    <button @click="editing = false" class="text-xs text-gray-500 hover:text-gray-700">Cancel</button>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ !empty($user['created_at']) ? \Carbon\Carbon::parse($user['created_at'])->format('M d, Y') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No users found.</td>
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

    {{-- Create User Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeCreateModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                {{-- Modal panel --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modal-title">Create New User</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Username</label>
                                <input
                                    type="text"
                                    wire:model="newUsername"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
                                >
                                @error('newUsername')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password</label>
                                <input
                                    type="password"
                                    wire:model="newPassword"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
                                >
                                @error('newPassword')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Display Name</label>
                                <input
                                    type="text"
                                    wire:model="newDisplayName"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($allRoles as $role)
                                        @php
                                            $roleName = is_array($role) ? ($role['name'] ?? '') : $role;
                                        @endphp
                                        <label class="inline-flex items-center space-x-2 text-sm">
                                            <input
                                                type="checkbox"
                                                value="{{ $roleName }}"
                                                wire:model="newRoles"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            >
                                            <span>{{ $roleName }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            wire:click="createUser"
                            wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            Create
                        </button>
                        <button
                            wire:click="closeCreateModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
