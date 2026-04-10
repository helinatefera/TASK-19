<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'CivicCrowd' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>

    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    @php
        $apiUser = session('api_user');
        $isAuthenticated = !empty($apiUser);
        $userRoles = $apiUser['roles'] ?? [];
        $roleNames = collect($userRoles)->map(fn($r) => is_array($r) ? ($r['name'] ?? '') : $r)->toArray();
        $hasRole = fn(string $role) => in_array($role, $roleNames);
    @endphp

    {{-- Navigation --}}
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                {{-- Left side --}}
                <div class="flex items-center space-x-8">
                    {{-- Logo / Title --}}
                    <a href="{{ url('/') }}" class="flex items-center space-x-2">
                        <span class="text-xl font-bold text-indigo-600">CivicCrowd</span>
                    </a>

                    {{-- Public links --}}
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="{{ route('campaigns.list') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Campaigns</a>
                        <a href="{{ route('programs.list') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Programs</a>
                    </div>

                    {{-- Authenticated links --}}
                    @if($isAuthenticated)
                        <div class="hidden md:flex items-center space-x-4">
                            <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="{{ route('orders.list') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Orders</a>
                            <a href="{{ route('vouchers.list') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Vouchers</a>
                            <a href="{{ route('notifications.inbox') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Notifications</a>
                        </div>

                        {{-- Moderation dropdown (moderator / admin) --}}
                        @if($hasRole('moderator') || $hasRole('admin'))
                            <div class="hidden md:flex items-center relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.outside="open = false" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium inline-flex items-center">
                                    Moderation
                                    <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open" x-transition class="absolute top-full left-0 mt-1 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                    <a href="{{ route('moderation.campaigns') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Campaigns Queue</a>
                                    <a href="{{ route('moderation.arbitration') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Arbitration</a>
                                </div>
                            </div>
                        @endif

                        {{-- Admin dropdown --}}
                        @if($hasRole('admin'))
                            <div class="hidden md:flex items-center relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.outside="open = false" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium inline-flex items-center">
                                    Admin
                                    <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open" x-transition class="absolute top-full left-0 mt-1 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                    <a href="{{ route('admin.users') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Users</a>
                                    <a href="{{ route('admin.parameters') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Parameters</a>
                                    <a href="{{ route('admin.audit-logs') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Audit</a>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Right side --}}
                <div class="flex items-center space-x-4">
                    @if($isAuthenticated)
                        {{-- Notification bell --}}
                        @livewire('notification.notification-bell')

                        {{-- Username --}}
                        <span class="text-sm text-gray-700 font-medium">{{ $apiUser['display_name'] ?? $apiUser['username'] ?? '' }}</span>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Login</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if(session('success'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 rounded-md bg-yellow-50 border border-yellow-200 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Main content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-sm text-gray-500">&copy; {{ date('Y') }} CivicCrowd. All rights reserved.</p>
        </div>
    </footer>

    {{-- Alpine.js for dropdown interactivity --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewireScripts
</body>
</html>
