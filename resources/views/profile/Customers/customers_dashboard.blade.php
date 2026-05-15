@extends('profile.Customers.app_customers')

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-10">
    <div class="mx-auto max-w-7xl px-6">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Customer Dashboard</p>
                <h1 class="mt-3 text-3xl font-semibold text-gray-900">Welcome back, {{ Auth::user()->name }}</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">
                    Keep track of upcoming stays, past trips, and places you may want to save for later.
                </p>
            </div>
            <a href="{{ route('home-page') }}"
                class="inline-flex items-center justify-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                Explore homes
            </a>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[28px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <p class="text-sm text-gray-500">Total Bookings</p>
                <p class="mt-3 text-4xl font-semibold text-[#173423]">{{ $totalBookings }}</p>
            </div>
            <div class="rounded-[28px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <p class="text-sm text-gray-500">Active Trips</p>
                <p class="mt-3 text-4xl font-semibold text-[#173423]">{{ $activeTrips }}</p>
            </div>
            <div class="rounded-[28px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <p class="text-sm text-gray-500">Past Stays</p>
                <p class="mt-3 text-4xl font-semibold text-[#173423]">{{ $pastBookings->count() }}</p>
            </div>
            <div class="rounded-[28px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <p class="text-sm text-gray-500">Wishlist</p>
                <p class="mt-3 text-4xl font-semibold text-[#173423]">{{ $wishlistCount }}</p>
            </div>
        </div>

        <div class="mt-8 grid gap-6 xl:grid-cols-[1.35fr_0.95fr]">
            <div class="space-y-6">
                <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5 sm:p-8">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Upcoming</p>
                            <h2 class="mt-2 text-2xl font-semibold text-gray-900">Your next bookings</h2>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse ($upcomingBookings as $booking)
                            <div class="rounded-[24px] border border-[#e9e2d5] bg-[#fcfaf4] p-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $booking->homestay_name ?? 'Homestay #' . $booking->homestay_id }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ trim(collect([$booking->city, $booking->state])->filter()->implode(', ')) ?: 'Malaysia' }}
                                        </p>
                                        <p class="mt-3 text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }} - {{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}
                                        </p>
                                    </div>
                                    <div class="text-left sm:text-right">
                                        <p class="text-sm text-gray-500">Total</p>
                                        <p class="mt-1 text-lg font-semibold text-green-700">
                                            RM {{ number_format($booking->total_price ?? 0, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-[24px] border border-dashed border-[#d8dece] bg-[#fbfcf8] px-6 py-10 text-center">
                                <p class="text-lg font-semibold text-gray-900">No upcoming bookings yet</p>
                                <p class="mt-2 text-sm text-gray-500">Once you book a stay, it will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5 sm:p-8">
                    <div class="mb-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">History</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Stays you booked before</h2>
                    </div>

                    <div class="space-y-4">
                        @forelse ($pastBookings as $booking)
                            <div class="flex flex-col gap-3 rounded-[24px] border border-[#e9e2d5] bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">
                                        {{ $booking->homestay_name ?? 'Homestay #' . $booking->homestay_id }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }} - {{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-[#eef6e7] px-4 py-2 text-sm font-medium text-green-700">
                                    Completed stay
                                </span>
                            </div>
                        @empty
                            <div class="rounded-[24px] border border-dashed border-[#d8dece] bg-[#fbfcf8] px-6 py-10 text-center">
                                <p class="text-lg font-semibold text-gray-900">No past stays yet</p>
                                <p class="mt-2 text-sm text-gray-500">Your completed bookings will be listed here.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5 sm:p-7">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Wishlist</p>
                    <h2 class="mt-2 text-2xl font-semibold text-gray-900">Saved places</h2>

                    @if ($wishlistCount > 0)
                        <p class="mt-3 text-sm text-gray-600">You have {{ $wishlistCount }} saved place{{ $wishlistCount > 1 ? 's' : '' }}.</p>
                    @else
                        <div class="mt-5 rounded-[24px] border border-dashed border-[#d8dece] bg-[#fbfcf8] px-5 py-8 text-center">
                            <p class="text-base font-semibold text-gray-900">Your wishlist is empty for now</p>
                            <p class="mt-2 text-sm text-gray-500">Save homes you like here once the wishlist feature is connected.</p>
                        </div>
                    @endif

                    <div class="mt-6">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Suggested stays</h3>
                        <div class="mt-4 space-y-3">
                            @forelse ($recommendedHomestays as $homestay)
                                <div class="rounded-[22px] border border-[#ece5d8] bg-[#fcfaf5] p-4">
                                    <p class="font-semibold text-gray-900">{{ $homestay->homestay_name }}</p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ trim(collect([$homestay->city, $homestay->state])->filter()->implode(', ')) ?: 'Malaysia' }}
                                    </p>
                                    <p class="mt-2 text-sm font-medium text-green-700">From RM {{ number_format($homestay->base_price ?? 0, 2) }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No suggested stays available right now.</p>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="rounded-[30px] bg-gradient-to-br from-green-900 via-green-800 to-[#163d2a] p-6 text-white shadow-[0_20px_60px_rgba(14,43,29,0.22)] sm:p-7">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-100/70">Notifications</p>
                    <h2 class="mt-2 text-2xl font-semibold">Recent updates</h2>
                    <div class="mt-5 space-y-3">
                        @forelse ($notifications as $notification)
                            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-green-50/90">
                                {{ $notification->message ?? 'You have a new update.' }}
                            </div>
                        @empty
                            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-6 text-sm text-green-50/80">
                                No new notifications yet.
                            </div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
