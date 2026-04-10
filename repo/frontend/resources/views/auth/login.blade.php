<x-layouts.guest>
    <h2 class="text-2xl font-bold text-gray-900 text-center mb-6">Sign In</h2>

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-4">
            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Username --}}
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                value="{{ old('username') }}"
                required
                autofocus
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Enter your username"
            >
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Enter your password"
            >
        </div>

        {{-- Remember me --}}
        <div class="flex items-center">
            <input
                type="checkbox"
                id="remember"
                name="remember"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            >
            <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
        </div>

        {{-- Submit --}}
        <div>
            <button
                type="submit"
                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
            >
                Sign In
            </button>
        </div>
    </form>
</x-layouts.guest>
