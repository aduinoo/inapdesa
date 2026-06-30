<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel | RumaRehat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans antialiased">
@include('sweetalert::alert')

<div class="min-h-screen flex flex-col">
    <nav class="border-b bg-white shadow-md">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex h-20 items-center justify-between">
                <div class="flex items-center gap-12">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('assets/images/homepage/rumarehatlogo.png') }}" alt="RumaRehat" class="h-12">
                        <span class="text-xl font-semibold text-green-700">Admin Panel</span>
                    </div>

                    <div class="hidden items-center gap-8 text-sm font-semibold text-gray-700 md:flex">
                        <a href="{{ route('admin.dashboard') }}"
                            class="{{ request()->routeIs('admin.dashboard') ? 'border-b-2 border-green-600 pb-1 text-green-700' : 'hover:text-green-600' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.owner-applications.index') }}"
                            class="{{ request()->routeIs('admin.owner-applications.*') ? 'border-b-2 border-green-600 pb-1 text-green-700' : 'hover:text-green-600' }}">
                            Owner Applications
                        </a>
                        <a href="{{ route('admin.reports.index') }}"
                            class="{{ request()->routeIs('admin.reports.*') ? 'border-b-2 border-green-600 pb-1 text-green-700' : 'hover:text-green-600' }}">
                            Reports
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <span class="hidden text-sm font-medium text-gray-600 sm:block">
                        {{ Str::of(Auth::user()->name)->explode(' ')[0] }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-full bg-red-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-600">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="border-t bg-white md:hidden">
            <div class="space-y-4 px-6 py-4 text-sm font-semibold text-gray-700">
                <a href="{{ route('admin.dashboard') }}" class="block hover:text-green-600">Dashboard</a>
                <a href="{{ route('admin.owner-applications.index') }}" class="block hover:text-green-600">Owner Applications</a>
                <a href="{{ route('admin.reports.index') }}" class="block hover:text-green-600">Reports</a>
            </div>
        </div>
    </nav>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="relative overflow-hidden bg-gradient-to-r from-green-950 via-green-900 to-green-900 text-green-100">
        <div class="pointer-events-none absolute inset-y-0 right-0 w-full bg-right bg-no-repeat bg-contain opacity-25 md:w-1/2"
            style="background-image: url('{{ asset('assets/images/homepage/palms.png') }}');">
        </div>

        <div class="relative mx-auto max-w-7xl px-6 py-14">
            <div class="flex flex-col items-center justify-between gap-8 md:flex-row">
                <div class="flex flex-col items-center text-center md:items-start md:text-left">
                    <img src="{{ asset('assets/images/homepage/rr_footer.png') }}" alt="RumaRehat" class="mb-4 h-28">
                    <p class="max-w-sm text-sm text-green-200">
                        RumaRehat Admin Panel manages users, owner applications, listings, and platform operations.
                    </p>
                </div>

                <div class="flex items-center gap-6 text-sm">
                    <a href="{{ route('home-page') }}" class="hover:text-white">Back to Website</a>
                </div>
            </div>

            <div class="mt-10 flex justify-between border-t border-green-800 pt-6 text-xs text-green-300">
                <p>© {{ date('Y') }} RumaRehat. All rights reserved.</p>
                <p>Admin System</p>
            </div>
        </div>
    </footer>
</div>

@include('profile.shared.swal-form-confirmation')
@stack('scripts')
</body>
</html>
