<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>InapDesa</title>

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="#">
    <link rel="icon" type="image/png" sizes="32x32" href="#">
    <link rel="icon" type="image/png" sizes="16x16" href="#">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Tailwind / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="font-sans antialiased bg-gray-50">
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
        $listPropertyRoute = route('list-property');

        $navNotifications = collect([]);
        $navUnreadCount = 0;
        $groupPaymentNotificationUrl = null;
        if (auth()->check()) {
            $userId = auth()->id();
            $navNotifications = \Illuminate\Support\Facades\DB::table('notifications')
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
            $navUnreadCount = \Illuminate\Support\Facades\DB::table('notifications')
                ->where('user_id', $userId)
                ->where('is_read', 0)
                ->count();

            $pendingGroupPayment = \Illuminate\Support\Facades\DB::table('group_payments')
                ->join('group_payment_members', 'group_payments.id', '=', 'group_payment_members.group_payment_id')
                ->where('group_payment_members.user_id', $userId)
                ->where('group_payment_members.payment_status', 'pending')
                ->where('group_payments.status', 'pending')
                ->select('group_payments.token')
                ->orderByDesc('group_payments.created_at')
                ->first();

            if ($pendingGroupPayment) {
                $groupPaymentNotificationUrl = route('group-payment.show', $pendingGroupPayment->token);
            }
        }
    @endphp

    <div class="">

        <!-- ================= NAVBAR ================= -->
        <nav x-data="{ open: false, userOpen: false, notifOpen: false }" class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 lg:px-6">
                <div class="flex h-16 items-center justify-between">

                    <!-- LEFT: Logo -->
                    <a href="{{ route('home-page') }}" class="flex items-center gap-3">
                        <img src="{{ asset('assets/images/homepage/rumarehatlogo.png') }}" alt="RumaRehat"
                            class="h-9">
                        <span class="text-lg font-semibold text-green-700">RumaRehat</span>
                    </a>

                    <!-- CENTER: Desktop Menu -->
                    <div class="hidden md:flex items-center gap-8 text-sm font-medium">

                        <!-- Homes -->
                        <a href="{{ route('home-page') }}"
                            class="relative pb-2
              {{ request()->routeIs('home-page') ? 'text-green-700 font-semibold' : 'text-gray-700 hover:text-green-600' }}">
                            Homes

                            @if (request()->routeIs('home-page'))
                                <span class="absolute left-0 -bottom-1 w-full h-0.5 bg-green-600 rounded"></span>
                            @endif
                        </a>

                        <!-- Attraction & Tours -->
                        <a href="{{ route('attractions-and-tours') }}"
                            class="relative pb-2
              {{ request()->routeIs('attractions-and-tours')
                  ? 'text-green-700 font-semibold'
                  : 'text-gray-700 hover:text-green-600' }}">
                            Attraction & Tours

                            @if (request()->routeIs('attractions-and-tours'))
                                <span class="absolute left-0 -bottom-1 w-full h-0.5 bg-green-600 rounded"></span>
                            @endif
                        </a>

                        <!-- Maps -->
                        <a href="{{ route('maps') }}"
                            class="relative pb-2
              {{ request()->routeIs('maps')
                  ? 'text-green-700 font-semibold'
                  : 'text-gray-700 hover:text-green-600' }}">
                            Maps

                            @if (request()->routeIs('maps'))
                                <span class="absolute left-0 -bottom-1 w-full h-0.5 bg-green-600 rounded"></span>
                            @endif
                        </a>
                        <!-- Contact Us -->
                        <a href="{{ route('contact') }}"
                            class="relative pb-2
              {{ request()->routeIs('contact') ? 'text-green-700 font-semibold' : 'text-gray-700 hover:text-green-600' }}">
                            Contact Us
                            @if (request()->routeIs('contact'))
                                <span class="absolute left-0 -bottom-1 w-full h-0.5 bg-green-600 rounded"></span>
                            @endif
                        </a>

                    </div>


                    <!-- RIGHT: Actions -->
                    <div class="hidden md:flex items-center gap-2">
                        <!-- List Property -->
                        <a href="{{ $listPropertyRoute }}"
                            class="flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-white transition {{ request()->routeIs('list-property') ? 'bg-green-700 shadow-lg shadow-green-900/15' : 'bg-green-600 hover:bg-green-700' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7h18M3 12h18M3 17h18" />
                            </svg>
                            List Your Property
                        </a>

                        @auth
                            <div class="relative">
                                <button @click="notifOpen = !notifOpen; userOpen = false"
                                    class="relative flex items-center justify-center rounded-full border p-2 text-gray-600 hover:bg-gray-100 transition">
                                    <i class="fa-regular fa-bell text-base"></i>
                                    @if($navUnreadCount > 0)
                                        <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white">
                                            {{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}
                                        </span>
                                    @endif
                                </button>

                                <div x-show="notifOpen" @click.outside="notifOpen = false" x-transition
                                    class="absolute right-0 top-12 w-80 rounded-2xl border border-gray-200 bg-white shadow-xl z-50">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-semibold text-gray-900">Notifications</p>
                                        @if($navUnreadCount > 0)
                                            <button onclick="markAllNotificationsRead()"
                                                class="text-xs font-medium text-green-600 hover:text-green-700">
                                                Mark all read
                                            </button>
                                        @endif
                                    </div>

                                    <div class="max-h-72 overflow-y-auto">
                                        @forelse ($navNotifications as $notif)
                                            @php
                                                $notificationUrl = $notif->link_url ?? null;
                                                if (!$notificationUrl && \Illuminate\Support\Str::startsWith($notif->title ?? '', 'Group payment')) {
                                                    $notificationUrl = $groupPaymentNotificationUrl;
                                                }
                                                $notificationUrl = $notificationUrl ?: $accountRoute . '#notifications';
                                            @endphp
                                            <a href="{{ $notificationUrl }}"
                                                class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition border-b border-gray-50 last:border-0"
                                                @click="notifOpen = false">
                                                @if(!$notif->is_read)
                                                    <span class="mt-1.5 block h-2 w-2 shrink-0 rounded-full bg-green-500"></span>
                                                @else
                                                    <span class="mt-1.5 block w-2 shrink-0"></span>
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-semibold text-gray-800 truncate {{ $notif->is_read ? '' : 'text-green-700' }}">
                                                        {{ $notif->title ?? 'Update' }}
                                                    </p>
                                                    <p class="mt-0.5 text-xs text-gray-500 line-clamp-2">
                                                        {{ $notif->message ?? '' }}
                                                    </p>
                                                    <p class="mt-1 text-[10px] text-gray-400">
                                                        {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                                                    </p>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="px-4 py-8 text-center">
                                                <i class="fa-regular fa-bell-slash text-2xl text-gray-300 mb-2 block"></i>
                                                <p class="text-sm text-gray-500">No notifications yet</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    <a href="{{ $accountRoute }}#notifications"
                                        class="block rounded-b-2xl bg-gray-50 px-4 py-3 text-center text-sm font-semibold text-gray-700 hover:bg-gray-100 transition"
                                        @click="notifOpen = false">
                                        View all notifications
                                    </a>
                                </div>
                            </div>

                            <div class="relative">
                                <button @click="userOpen = !userOpen; notifOpen = false"
                                    class="flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition">
                                    <span>{{ Auth::user()->name }}</span>
                                    <svg class="h-4 w-4 text-gray-500 transition-transform"
                                        :class="{ 'rotate-180': userOpen }" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="userOpen" @click.outside="userOpen = false" x-transition
                                    class="absolute right-0 top-12 w-60 rounded-2xl border border-gray-200 bg-white py-2 shadow-lg">
                                    <a href="{{ $accountRoute }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-gauge w-4 text-green-600"></i> Dashboard
                                    </a>
                                    <a href="{{ route('user.messages.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-comment-dots w-4 text-blue-500"></i> Messages
                                    </a>
                                    <a href="{{ route('user.cancellation-options.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-ban w-4 text-red-500"></i> Cancellations
                                    </a>
                                    <a href="{{ route('user.reports.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-flag w-4 text-amber-500"></i> Reports
                                    </a>
                                    <a href="{{ route('user.bookings.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-calendar-check w-4 text-green-600"></i> Bookings
                                    </a>
                                    <a href="{{ route('user.wishlist.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-heart w-4 text-pink-500"></i> Wishlist
                                    </a>
                                    <div class="mx-3 my-1 border-t border-gray-100"></div>
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fa-solid fa-user-pen w-4 text-blue-600"></i> Profile
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50">
                                            <i class="fa-solid fa-right-from-bracket w-4"></i> Log out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}"
                                class="block text-center rounded-full border py-2 px-4 text-gray-700 hover:bg-gray-100">
                                Log In
                            </a>
                        @endauth




                    </div>

                    <!-- MOBILE: Hamburger -->
                    <div class="md:hidden">
                        <button @click="open = !open"
                            class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path :class="{ 'hidden': open }" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ 'hidden': !open }" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                </div>
            </div>

            <!-- MOBILE MENU -->
            <div x-show="open" x-transition
                class="md:hidden bg-white border-t border-gray-200 px-4 py-6 space-y-4 text-sm font-medium">

                <!-- Homes -->
                <a href="{{ route('home-page') }}"
                    class="relative block pl-3
              {{ request()->routeIs('home-page') ? 'text-green-700 font-semibold' : 'text-gray-700 hover:text-green-600' }}">
                    @if (request()->routeIs('home-page'))
                        <span class="absolute left-0 top-0 h-full w-1 bg-green-600 rounded"></span>
                    @endif
                    Homes
                </a>

                <!-- Attraction & Tours -->
                <a href="{{ route('attractions-and-tours') }}"
                    class="relative block pl-3
              {{ request()->routeIs('attractions-and-tours')
                  ? 'text-green-700 font-semibold'
                  : 'text-gray-700 hover:text-green-600' }}">
                    @if (request()->routeIs('attractions-and-tours'))
                        <span class="absolute left-0 top-0 h-full w-1 bg-green-600 rounded"></span>
                    @endif
                    Attraction & Tours
                </a>

                <!-- Maps -->
                <a href="{{ route('maps') }}"
                    class="relative block pl-3
              {{ request()->routeIs('maps')
                  ? 'text-green-700 font-semibold'
                  : 'text-gray-700 hover:text-green-600' }}">
                    @if (request()->routeIs('maps'))
                        <span class="absolute left-0 top-0 h-full w-1 bg-green-600 rounded"></span>
                    @endif
                    Maps
                </a>

                <!-- Contact Us -->
                <a href="{{ route('contact') }}"
                    class="relative block pl-3
              {{ request()->routeIs('contact') ? 'text-green-700 font-semibold' : 'text-gray-700 hover:text-green-600' }}">
                    @if (request()->routeIs('contact'))
                        <span class="absolute left-0 top-0 h-full w-1 bg-green-600 rounded"></span>
                    @endif
                    Contact Us
                </a>

                <!-- Divider -->
                <div class="pt-4 border-t border-gray-200 space-y-3">

                    <!-- List Property -->
                    <a href="{{ $listPropertyRoute }}"
                        class="block text-center rounded-full py-2 text-white font-semibold {{ request()->routeIs('list-property') ? 'bg-green-700' : 'bg-green-600' }}">
                        List Your Property
                    </a>

                    @auth
                        <a href="{{ $accountRoute }}"
                            class="flex items-center gap-3 rounded-full border py-2 px-4 text-gray-700 font-semibold hover:bg-gray-100">
                            <i class="fa-solid fa-gauge w-4 text-green-600"></i> Dashboard
                        </a>
                        <a href="{{ route('user.messages.index') }}"
                            class="flex items-center gap-3 rounded-full border py-2 px-4 text-gray-700 hover:bg-gray-100">
                            <i class="fa-solid fa-comment-dots w-4 text-blue-500"></i> Messages
                        </a>
                        <a href="{{ route('user.cancellation-options.index') }}"
                            class="flex items-center gap-3 rounded-full border py-2 px-4 text-gray-700 hover:bg-gray-100">
                            <i class="fa-solid fa-ban w-4 text-red-500"></i> Cancellations
                        </a>
                        <a href="{{ route('user.reports.index') }}"
                            class="flex items-center gap-3 rounded-full border py-2 px-4 text-gray-700 hover:bg-gray-100">
                            <i class="fa-solid fa-flag w-4 text-amber-500"></i> Reports
                        </a>
                        <a href="{{ route('user.bookings.index') }}"
                            class="flex items-center gap-3 rounded-full border py-2 px-4 text-gray-700 hover:bg-gray-100">
                            <i class="fa-solid fa-calendar-check w-4 text-green-600"></i> Bookings
                        </a>
                        <a href="{{ route('user.wishlist.index') }}"
                            class="flex items-center gap-3 rounded-full border py-2 px-4 text-gray-700 hover:bg-gray-100">
                            <i class="fa-solid fa-heart w-4 text-pink-500"></i> Wishlist
                        </a>
                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center gap-3 rounded-full border py-2 px-4 text-gray-700 hover:bg-gray-100">
                            <i class="fa-solid fa-user-pen w-4 text-blue-600"></i> Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-3 w-full rounded-full border py-2 px-4 text-red-600 hover:bg-red-50">
                                <i class="fa-solid fa-right-from-bracket w-4"></i> Log Out
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="block text-center rounded-full border py-2 px-4 text-gray-700 hover:bg-gray-100">
                            Log In
                        </a>
                    @endauth
                </div>
            </div>

    </div>
    </nav>

    <!-- ================= END NAVBAR ================= -->

    <!-- ================= CONTENT ================= -->
    <main class="bg-yellow-50">
        @yield('content')
    </main>

    <!-- ================= FOOTER ================= -->
    <footer class="relative bg-gradient-to-r from-green-950 via-green-900 to-green-900 text-white overflow-hidden">

        <!-- Palms background -->
        <div class="absolute inset-y-0 right-0 w-full md:w-1/2 bg-no-repeat bg-right bg-contain opacity-30 pointer-events-none"
            style="background-image: url('{{ asset('assets/images/homepage/palms.png') }}');">
        </div>

        <div class="relative max-w-7xl mx-auto px-6 py-16">

            <!-- Top -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-12">

                <!-- Logo -->
                <div>
                    <img src="{{ asset('assets/images/homepage/rr_footer.png') }}" alt="RumaRehat"
                        class="h-24 mb-4">
                    <p class="text-sm text-green-100 max-w-xs">
                        Discover unique village stays and meaningful local experiences across Malaysia.
                    </p>

                    <div class="flex gap-4 mt-6 text-green-100 text-xl">
                        <a href="#" aria-label="Facebook" class="hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram" class="hover:text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn" class="hover:text-white"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="YouTube" class="hover:text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Us</h4>
                    <ul class="space-y-3 text-sm text-green-100">
                        <li><a href="{{ route('help-center') }}" class="hover:text-white">Help Center</a></li>
                        <li><a href="{{ route('cancellation-options') }}" class="hover:text-white">Cancellation Options</a></li>
                        <li><a href="{{ route('safety-issues') }}" class="hover:text-white">Safety Issues</a></li>
                        <li><a href="{{ route('neighbourhood-concerns') }}" class="hover:text-white">Report neighbourhood concerns</a></li>
                    </ul>
                </div>

                <!-- Hosting -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Hosting</h4>
                    <ul class="space-y-3 text-sm text-green-100">
                        <li><a href="{{ route('list-property') }}" class="hover:text-white">List your RumaRehat</a></li>
                        <li><a href="{{ route('hosting-guide') }}" class="hover:text-white">Hosting Guide</a></li>
                        <li><a href="{{ route('community-forum') }}" class="hover:text-white">Community Forum</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom -->
            <div
                class="border-t border-green-700 mt-12 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-green-200 gap-4">
                <p>© 2026 RumaRehat. All rights reserved.</p>
                <div class="flex gap-6">
                    <a href="{{ route('privacy') }}" class="hover:text-white">Privacy</a>
                    <a href="{{ route('terms') }}" class="hover:text-white">Terms</a>
                </div>
            </div>

        </div>
</footer>



    <!-- ================= END FOOTER ================= -->
    @if (request()->routeIs('attractions-and-tours'))
        <script>
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        try {
                            const response = await fetch('/set-location', {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    lat: pos.coords.latitude,
                                    lon: pos.coords.longitude
                                })
                            });

                            const data = await response.json();

                            if (!response.ok || data.status !== 'ok') {
                                throw new Error(data.message || 'Unable to store user location.');
                            }

                            location.reload();
                        } catch (err) {
                            console.error('Location sync error:', err);
                        }
                    },
                    (err) => {
                        console.error('Geolocation error:', err.message);
                    }, {
                        enableHighAccuracy: false,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            }
        </script>
    @endif
    @include('profile.shared.swal-form-confirmation')
    @stack('scripts')
    @auth
    <script>
        function markAllNotificationsRead() {
            fetch('{{ route('notifications.mark-read') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(r => r.json()).then(() => {
                location.reload();
            }).catch(() => {});
        }
    </script>
    @endauth
    </div>
</body>


</html>
