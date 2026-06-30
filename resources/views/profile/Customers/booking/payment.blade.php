@extends('profile.Customers.app_customers')

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-10">
    <div class="mx-auto max-w-6xl px-6">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Payment</p>
            <h1 class="mt-3 text-3xl font-semibold text-gray-900">Choose your payment method</h1>
            <p class="mt-2 text-sm leading-6 text-gray-600">
                Select how you'd like to pay, then you'll be redirected to ToyyibPay to complete your booking securely.
            </p>
        </div>

        @if (session('error'))
            <div class="mb-6 rounded-[22px] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr]">
            <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <div class="flex gap-4">
                    <img src="{{ asset($pendingBooking['image']) }}" alt="{{ $pendingBooking['homestay_name'] }}"
                        class="h-28 w-28 rounded-[24px] object-cover">
                    <div>
                        <p class="text-xl font-semibold text-gray-900">{{ $pendingBooking['homestay_name'] }}</p>
                        <p class="mt-2 text-sm text-gray-500">{{ $pendingBooking['location'] }}</p>
                        <p class="mt-3 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($pendingBooking['check_in'])->format('d M Y') }}
                            to
                            {{ \Carbon\Carbon::parse($pendingBooking['check_out'])->format('d M Y') }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">{{ $pendingBooking['guest_count'] }} guest(s)</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-[#eee5d7] pt-6">
                    <h2 class="text-lg font-semibold text-gray-900">Price details</h2>
                    <div class="mt-4 space-y-3 text-sm text-gray-600">
                        <div class="flex items-center justify-between">
                            <span>RM {{ number_format($pendingBooking['price_per_night'], 2) }} x {{ $pendingBooking['nights'] }} night(s)</span>
                            <span>RM {{ number_format($pendingBooking['subtotal'], 2) }}</span>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-[#eee5d7] pt-4 text-base font-semibold text-gray-900">
                        <span>Total</span>
                        <span>RM {{ number_format($pendingBooking['total'], 2) }}</span>
                    </div>
                </div>
            </section>

            <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <h2 class="text-2xl font-semibold text-gray-900">Payment method</h2>
                <p class="mt-2 text-sm text-gray-500">Choose how you want to pay via ToyyibPay.</p>

                <form action="{{ route('user.bookings.process-payment') }}" method="POST" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="payment_channel" value="0">

                    <label class="flex cursor-pointer items-center gap-4 rounded-[22px] border-2 border-green-600 bg-green-50/50 p-5 transition"
                        for="channel_fpx">
                        <input type="radio" id="channel_fpx" name="payment_channel" value="0"
                            class="h-5 w-5 accent-green-600" checked>
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.401 0-4.845.175-7.252.582V21M3 9h18" />
                                </svg>
                                <p class="text-base font-semibold text-gray-900">Online Banking (FPX)</p>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Pay directly from your bank account. Supported banks: Maybank, CIMB, Public Bank, RHB, and more.</p>
                        </div>
                    </label>

                    @error('payment_channel')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="rounded-[22px] bg-[#fbf7ee] p-4 text-sm text-gray-600">
                        <p class="font-semibold text-gray-900">Secure payment</p>
                        <p class="mt-2">You'll be redirected to ToyyibPay to complete your payment. Your booking details are saved and will be confirmed once payment is successful.</p>
                    </div>

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                        Proceed to ToyyibPay — RM {{ number_format($pendingBooking['total'], 2) }}
                    </button>
                </form>

                <div class="mt-6 border-t border-[#eee5d7] pt-6">
                    <a href="{{ route('group-payment.create') }}"
                        class="flex items-center gap-4 rounded-[22px] border-2 border-dashed border-green-300 bg-green-50/30 p-4 transition hover:border-green-400 hover:bg-green-50/60">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-5 w-5 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Split with friends</p>
                            <p class="text-xs text-gray-500">Invite others by email to share the cost. Everyone pays within 1 hour.</p>
                        </div>
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection