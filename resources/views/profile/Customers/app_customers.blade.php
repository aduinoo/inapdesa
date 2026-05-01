<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>RumaRehat Customer</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900">
    @include('sweetalert::alert')
    @php
        $accountRoute = auth()->check()
            ? match ((int) auth()->user()->role) {
                1 => route('admin.dashboard'),
                2 => route('user.dashboard'),
                3 => route('owner.dashboard'),
                default => route('dashboard'),
            }
            : route('login');
    @endphp

    <div class="min-h-screen">
        <nav x-data="{ open: false, userOpen: false }" class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 lg:px-6">
                <div class="flex h-16 items-center justify-between">
                    <a href="{{ route('home-page') }}" class="flex items-center gap-3">
                        <img src="{{ asset('assets/images/homepage/rumarehatlogo.png') }}" alt="RumaRehat" class="h-9">
                        <span class="text-lg font-semibold text-green-700">RumaRehat</span>
                    </a>

                    <div class="hidden md:flex items-center gap-8 text-sm font-medium">
                        <a href="{{ route('home-page') }}"
                            class="relative pb-2 {{ request()->routeIs('home-page') ? 'text-green-700 font-semibold' : 'text-gray-700 hover:text-green-600' }}">
                            Homes
                        </a>
                        <a href="{{ route('attractions-and-tours') }}"
                            class="relative pb-2 {{ request()->routeIs('attractions-and-tours*') ? 'text-green-700 font-semibold' : 'text-gray-700 hover:text-green-600' }}">
                            Attraction & Tours
                        </a>
                        <a href="{{ route('maps') }}"
                            class="relative pb-2 {{ request()->routeIs('maps') ? 'text-green-700 font-semibold' : 'text-gray-700 hover:text-green-600' }}">
                            Maps
                        </a>
                    </div>

                    <div class="hidden md:flex items-center gap-2">
                        <a href="{{ route('home-page') }}"
                            class="flex items-center gap-2 rounded-full bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition">
                            Back To Site
                        </a>

                        @auth
                            <div class="relative">
                                <button @click="userOpen = !userOpen"
                                    class="flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition">
                                    <span>{{ Auth::user()->name }}</span>
                                    <svg class="h-4 w-4 text-gray-500 transition-transform" :class="{ 'rotate-180': userOpen }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="userOpen" @click.outside="userOpen = false" x-transition
                                    class="absolute right-0 top-12 w-52 rounded-2xl border border-gray-200 bg-white py-2 shadow-lg">
                                    <a href="{{ $accountRoute }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Dashboard</a>
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                                            Log out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth
                    </div>

                    <div class="md:hidden">
                        <button @click="open = !open" class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path :class="{ 'hidden': open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ 'hidden': !open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="open" x-transition class="md:hidden bg-white border-t border-gray-200 px-4 py-6 space-y-4 text-sm font-medium">
                <a href="{{ route('home-page') }}" class="block text-gray-700 hover:text-green-600">Homes</a>
                <a href="{{ route('attractions-and-tours') }}" class="block text-gray-700 hover:text-green-600">Attraction & Tours</a>
                <a href="{{ route('maps') }}" class="block text-gray-700 hover:text-green-600">Maps</a>
                <a href="{{ $accountRoute }}" class="block text-center rounded-full border py-2 px-4 text-gray-700 font-semibold hover:bg-gray-100">Dashboard</a>
            </div>
        </nav>

        <main class="bg-yellow-50 min-h-[60vh]">
            @yield('content')
        </main>

        <footer class="relative bg-gradient-to-r from-green-950 via-green-900 to-green-900 text-white overflow-hidden">
            <div class="relative max-w-7xl mx-auto px-6 py-16">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-12">
                    <div>
                        <img src="{{ asset('assets/images/homepage/rr_footer.png') }}" alt="RumaRehat" class="h-24 mb-4">
                        <p class="text-sm text-green-100 max-w-xs">
                            Discover unique village stays and meaningful local experiences across Malaysia.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Contact Us</h4>
                        <ul class="space-y-3 text-sm text-green-100">
                            <li><a href="#" class="hover:text-white">Help Center</a></li>
                            <li><a href="#" class="hover:text-white">Cancellation Options</a></li>
                            <li><a href="#" class="hover:text-white">Safety Issues</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Hosting</h4>
                        <ul class="space-y-3 text-sm text-green-100">
                            <li><a href="#" class="hover:text-white">List your RumaRehat</a></li>
                            <li><a href="#" class="hover:text-white">Hosting Guide</a></li>
                            <li><a href="#" class="hover:text-white">Community Forum</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
