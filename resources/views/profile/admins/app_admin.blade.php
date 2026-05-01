<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel | RumaRehat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 font-sans antialiased">

<div class="min-h-screen flex flex-col">

    <!-- ================= ADMIN NAVBAR ================= -->
    <nav class="bg-white shadow-md border-b">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex items-center justify-between h-20">

                <!-- LEFT: Logo + Menu -->
                <div class="flex items-center gap-12">

                    <!-- Logo -->
                    <div class="flex items-center gap-3">
                        <img
                            src="{{ asset('assets/images/homepage/rumarehatlogo.png') }}"
                            alt="RumaRehat"
                            class="h-12"
                        >
                        <span class="text-xl font-semibold text-green-700">
                            Admin Panel
                        </span>
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-gray-700">
                        <a href="{{ route('admin.dashboard') }}"
                           class="{{ request()->routeIs('admin.dashboard') ? 'text-green-700 border-b-2 border-green-600 pb-1' : 'hover:text-green-600' }}">
                            Dashboard
                        </a>

                    <a href="{{ route('admin.dashboard') }}"
   class="{{ request()->routeIs('admin.dashboard') ? 'text-green-700 border-b-2 border-green-600 pb-1' : 'hover:text-green-600' }}">
    Owners
</a>



                        <a href="#" class="hover:text-green-600">
                            Listings
                        </a>

                        <a href="#" class="hover:text-green-600">
                            Pricings
                        </a>

                        <a href="#" class="hover:text-green-600">
                            System Checkup
                        </a>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="flex items-center gap-4">
                    <span class="hidden sm:block text-sm font-medium text-gray-600">
                        {{ Str::of(Auth::user()->name)->explode(' ')[0] }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="rounded-full bg-red-500 px-5 py-2 text-sm font-semibold text-white hover:bg-red-600 transition">
                            Log Out
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="md:hidden border-t bg-white">
            <div class="px-6 py-4 space-y-4 text-sm font-semibold text-gray-700">
                <a href="{{ route('admin.dashboard') }}" class="block hover:text-green-600">
                    Dashboard
                </a>
               <a href="{{ route('admin.dashboard') }}"
   class="{{ request()->routeIs('admin.dashboard') ? 'text-green-700 border-b-2 border-green-600 pb-1' : 'hover:text-green-600' }}">
    Owners
</a>

                <a href="#" class="block hover:text-green-600">
                    Listings
                </a>
                <a href="#" class="block hover:text-green-600">
                    Pricings
                </a>
                <a href="#" class="block hover:text-green-600">
                    System Checkup
                </a>
            </div>
        </div>
    </nav>

    <!-- ================= PAGE CONTENT ================= -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- ================= FOOTER ================= -->
    <footer class="relative bg-gradient-to-r from-green-950 via-green-900 to-green-900 text-green-100 overflow-hidden">

        <!-- Palm Background -->
        <div
            class="absolute inset-y-0 right-0 w-full md:w-1/2 bg-no-repeat bg-right bg-contain opacity-25 pointer-events-none"
            style="background-image: url('{{ asset('assets/images/homepage/palms.png') }}');">
        </div>

        <div class="relative max-w-7xl mx-auto px-6 py-14">

            <div class="flex flex-col md:flex-row justify-between items-center gap-8">

                <!-- Logo + Description -->
                <div class="flex flex-col items-center md:items-start text-center md:text-left">
                    <img
                        src="{{ asset('assets/images/homepage/rr_footer.png') }}"
                        alt="RumaRehat"
                        class="h-28 mb-4"
                    >

                    <p class="max-w-sm text-sm text-green-200">
                        RumaRehat Admin Panel – manage users, listings, pricing,
                        and system health efficiently and securely.
                    </p>
                </div>

                <!-- Footer Links -->
                <div class="flex items-center gap-6 text-sm">
                    <a href="{{ route('home-page') }}" class="hover:text-white">
                        Back to Website
                    </a>
                </div>
            </div>

            <div class="border-t border-green-800 mt-10 pt-6 text-xs text-green-300 flex justify-between">
                <p>© {{ date('Y') }} RumaRehat. All rights reserved.</p>
                <p>Admin System</p>
            </div>

        </div>
    </footer>

</div>

</body>
</html>
