<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'CivicCrowd - Login' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>

    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="text-3xl font-bold text-indigo-600">CivicCrowd</a>
            <p class="mt-2 text-sm text-gray-600">Community Campaigns &amp; Events</p>
        </div>

        {{-- Card --}}
        <div class="bg-white shadow-lg rounded-lg px-8 py-8">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
