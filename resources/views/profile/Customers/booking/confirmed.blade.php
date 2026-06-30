@extends('profile.Customers.app_customers')

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-12">
    <div class="mx-auto max-w-4xl px-6">
        <section class="overflow-hidden rounded-[34px] bg-white shadow-[0_24px_70px_rgba(21,43,29,0.12)] ring-1 ring-black/5">
            <div class="grid gap-0 md:grid-cols-[0.95fr_1.05fr]">
                <div class="bg-gradient-to-br from-green-900 via-green-800 to-[#163d2a] p-8 text-white">
                    <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-100/70">Booking Confirmed</p>
                    <h1 class="mt-4 text-3xl font-semibold">Your stay is locked in.</h1>
                    <p class="mt-4 text-sm leading-7 text-green-50/85">
                        Your payment was processed successfully through ToyyibPay.
                    </p>

                    <div class="mt-8 rounded-[24px] border border-white/10 bg-white/10 p-5">
                        <p class="text-sm text-green-50/80">Booking reference</p>
                        <p class="mt-2 text-2xl font-semibold">#{{ $booking->booking_id }}</p>
                        <p class="mt-4 text-sm text-green-50/80">Status</p>
                        <p class="mt-2 inline-flex rounded-full bg-white/15 px-4 py-2 text-sm font-semibold uppercase tracking-[0.2em]">
                            {{ $booking->booking_status }}
                        </p>
                    </div>
                </div>

                <div class="p-8">
                    <div class="flex gap-4">
                        <img src="{{ asset($booking->image_path ?: 'assets/images/homepage/home.jpg') }}"
                            alt="{{ $booking->homestay_name }}"
                            class="h-28 w-28 rounded-[24px] object-cover">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900">{{ $booking->homestay_name }}</h2>
                            <p class="mt-2 text-sm text-gray-500">
                                {{ trim(collect([$booking->city, $booking->state])->filter()->implode(', ')) ?: 'Malaysia' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 space-y-4 rounded-[24px] bg-[#fbf7ee] p-5 text-sm text-gray-700">
                        <div class="flex items-center justify-between">
                            <span>Check-in</span>
                            <span class="font-semibold">{{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Check-out</span>
                            <span class="font-semibold">{{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Total nights</span>
                            <span class="font-semibold">{{ $nights }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-[#eadfce] pt-4">
                            <span>Total paid</span>
                            <span class="text-lg font-semibold text-green-700">RM {{ number_format($booking->total_price, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-3 sm:grid-cols-2">
                        <a href="{{ $dashboardRoute ?? route('user.dashboard') }}"
                            class="inline-flex flex-1 items-center justify-center rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                            View my bookings
                        </a>
                        <a href="{{ route('home-page') }}"
                            class="inline-flex flex-1 items-center justify-center rounded-full border border-[#d9d0be] px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-green-700 hover:text-green-700">
                            Explore more homes
                        </a>
                       
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
