<x-layouts.app>
    {{-- Hero section --}}
    <div class="bg-indigo-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight">
                CivicCrowd
            </h1>
            <p class="mt-4 text-xl text-indigo-200">
                Community Campaigns &amp; Events
            </p>
            <p class="mt-6 max-w-2xl mx-auto text-lg text-indigo-100">
                Discover and support community-driven campaigns, book events at local venues,
                and connect with creators who are making a difference in your neighborhood.
            </p>
            <div class="mt-10 flex justify-center space-x-4">
                <a href="{{ route('campaigns.list') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50 transition shadow-sm">
                    Browse Campaigns
                </a>
                <a href="{{ route('programs.list') }}" class="inline-flex items-center px-6 py-3 border border-white text-base font-medium rounded-md text-white hover:bg-indigo-600 transition">
                    View Programs
                </a>
            </div>
        </div>
    </div>

    {{-- Features section --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">How It Works</h2>
            <p class="mt-4 text-lg text-gray-600">Join the community in three simple steps</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Feature 1 --}}
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 mb-4">
                    <svg class="h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Discover</h3>
                <p class="mt-2 text-gray-600">
                    Browse community campaigns and venue programs happening near you. Find events that match your interests.
                </p>
            </div>

            {{-- Feature 2 --}}
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 mb-4">
                    <svg class="h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Book &amp; Support</h3>
                <p class="mt-2 text-gray-600">
                    Reserve your seats, support campaigns with contributions, and receive digital vouchers for your bookings.
                </p>
            </div>

            {{-- Feature 3 --}}
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 mb-4">
                    <svg class="h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Engage</h3>
                <p class="mt-2 text-gray-600">
                    Attend events, leave reviews, and build your community reputation. Earn trust and unlock exclusive rewards.
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
