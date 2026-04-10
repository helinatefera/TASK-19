<x-layouts.app>
    @php
        $apiUser = session('api_user', []);
        $userRoles = $apiUser['roles'] ?? [];
        $roleNames = collect($userRoles)->map(fn($r) => is_array($r) ? ($r['name'] ?? '') : $r)->toArray();
        $hasRole = fn(string $role) => in_array($role, $roleNames);
        $stats = $stats ?? [];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Welcome header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Welcome back, {{ $apiUser['display_name'] ?? $apiUser['username'] ?? 'User' }}!
            </h1>
            <div class="mt-2 flex items-center space-x-2">
                <span class="text-sm text-gray-600">Your role:</span>
                @foreach($roleNames as $roleName)
                    @php
                        $badgeColor = match($roleName) {
                            'admin' => 'bg-red-100 text-red-800',
                            'moderator' => 'bg-yellow-100 text-yellow-800',
                            'staff' => 'bg-blue-100 text-blue-800',
                            'creator' => 'bg-green-100 text-green-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                        {{ ucfirst($roleName) }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Total orders --}}
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $stats['total_orders'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <a href="{{ route('orders.list') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">View all orders &rarr;</a>
                </div>
            </div>

            {{-- Active bookings --}}
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Bookings</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $stats['active_bookings'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <a href="{{ route('orders.list') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">View active bookings &rarr;</a>
                </div>
            </div>

            {{-- Unread notifications --}}
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Unread Notifications</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $stats['unread_notifications'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3">
                    <a href="{{ route('notifications.inbox') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">View notifications &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Quick links based on role --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Common links for all authenticated users --}}
                <a href="{{ route('campaigns.list') }}" class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition">
                    <svg class="h-5 w-5 text-indigo-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Browse Campaigns</span>
                </a>

                <a href="{{ route('programs.list') }}" class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition">
                    <svg class="h-5 w-5 text-indigo-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Browse Programs</span>
                </a>

                <a href="{{ route('vouchers.list') }}" class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition">
                    <svg class="h-5 w-5 text-indigo-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700">My Vouchers</span>
                </a>

                {{-- Creator links --}}
                @if($hasRole('creator') || $hasRole('admin'))
                    <a href="{{ route('campaigns.create') }}" class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition">
                        <svg class="h-5 w-5 text-green-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Create Campaign</span>
                    </a>
                @endif

                {{-- Moderator links --}}
                @if($hasRole('moderator') || $hasRole('admin'))
                    <a href="{{ route('moderation.campaigns') }}" class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-yellow-300 hover:bg-yellow-50 transition">
                        <svg class="h-5 w-5 text-yellow-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Campaign Approval Queue</span>
                    </a>

                    <a href="{{ route('moderation.arbitration') }}" class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-yellow-300 hover:bg-yellow-50 transition">
                        <svg class="h-5 w-5 text-yellow-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Arbitration Queue</span>
                    </a>
                @endif

                {{-- Admin links --}}
                @if($hasRole('admin'))
                    <a href="{{ route('admin.users') }}" class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-red-300 hover:bg-red-50 transition">
                        <svg class="h-5 w-5 text-red-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Manage Users</span>
                    </a>

                    <a href="{{ route('admin.parameters') }}" class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-red-300 hover:bg-red-50 transition">
                        <svg class="h-5 w-5 text-red-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Business Parameters</span>
                    </a>

                    <a href="{{ route('admin.audit-logs') }}" class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-red-300 hover:bg-red-50 transition">
                        <svg class="h-5 w-5 text-red-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Audit Logs</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
