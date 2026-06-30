@extends('profile.Customers.app_customers')

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-10">
    <div class="mx-auto max-w-4xl px-6">
        @if (session('success'))
            <div class="mb-6 rounded-[22px] border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-[22px] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @php
            $isExpired = $groupPayment->status === 'expired' || now()->greaterThan($groupPayment->deadline_at);
            $isCancelled = $groupPayment->status === 'cancelled';
            $isCompleted = $groupPayment->status === 'paid';
        @endphp

        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Group Payment</p>
            <h1 class="mt-3 text-3xl font-semibold text-gray-900">{{ $groupPayment->homestay_name }}</h1>
            <p class="mt-2 text-sm text-gray-500">{{ $groupPayment->location }}</p>

            @if ($isCompleted)
                <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-green-100 px-4 py-2 text-sm font-semibold text-green-700">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    All members paid — Booking confirmed
                </div>
            @elseif ($isCancelled)
                <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-red-100 px-4 py-2 text-sm font-semibold text-red-700">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                    Cancelled
                </div>
            @elseif ($isExpired)
                <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-red-100 px-4 py-2 text-sm font-semibold text-red-700">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                    Expired — not all members paid in time
                </div>
            @else
                <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-700"
                    x-data="{ deadline: new Date(@js($groupPayment->deadline_at->toIso8601String())).getTime() }"
                    x-init="setInterval(() => {
                        let diff = deadline - Date.now();
                        if (diff <= 0) { $el.querySelector('[data-timer]').textContent = 'Expired'; return; }
                        let m = Math.floor(diff / 60000);
                        let s = Math.floor((diff % 60000) / 1000);
                        $el.querySelector('[data-timer]').textContent = m + 'm ' + s + 's remaining';
                    }, 1000)">
                    <svg class="h-4 w-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>
                    <span data-timer>Waiting for payments...</span>
                </div>
            @endif
        </div>

        <div class="grid gap-8 lg:grid-cols-[1fr_1.2fr]">
            <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                @if ($groupPayment->image)
                    <img src="{{ asset($groupPayment->image) }}" alt="{{ $groupPayment->homestay_name }}"
                        class="h-40 w-full rounded-[24px] object-cover">
                @endif

                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <span>Check-in</span>
                        <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($groupPayment->check_in)->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Check-out</span>
                        <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($groupPayment->check_out)->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Guests</span>
                        <span class="font-medium text-gray-900">{{ $groupPayment->guest_count }}</span>
                    </div>
                    <div class="flex justify-between border-t border-[#eee5d7] pt-3">
                        <span class="font-semibold text-gray-900">Total price</span>
                        <span class="font-semibold text-gray-900">RM {{ number_format($groupPayment->total_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Per person</span>
                        <span class="font-semibold text-green-700">RM {{ number_format($groupPayment->share_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Progress</span>
                        <span class="font-semibold text-gray-900">{{ $paidCount }}/{{ $totalCount }} paid</span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                        <div class="h-full rounded-full bg-green-600 transition-all" style="width: {{ $totalCount > 0 ? ($paidCount / $totalCount * 100) : 0 }}%"></div>
                    </div>
                </div>

                @if ($groupPayment->status === 'pending' && !$isExpired && !$isCancelled)
                    <div class="mt-6 space-y-3">
                        @if ($currentMember)
                            @if ($currentMember->payment_status === 'pending')
                                @if ($currentMember->user_id)
                                    <form action="{{ route('group-payment.pay', $groupPayment->token) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex w-full items-center justify-center rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                                            Pay RM {{ number_format($currentMember->share_amount, 2) }}
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('group-payment.accept', $groupPayment->token) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="pay_now" value="1">
                                        <button type="submit"
                                            class="inline-flex w-full items-center justify-center rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                                            Accept & Pay RM {{ number_format($currentMember->share_amount, 2) }}
                                        </button>
                                    </form>
                                @endif
                            @else
                                <div class="rounded-[22px] bg-green-50 p-4 text-center text-sm font-semibold text-green-700">
                                    You have paid your share
                                </div>
                            @endif

                            @if ($currentMember->is_initiator && !$isExpired && !$isCancelled)
                                <form action="{{ route('group-payment.cancel', $groupPayment->token) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to cancel this group payment?')">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-full border border-red-300 bg-white px-5 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                                        Cancel group payment
                                    </button>
                                </form>
                            @endif
                        @elseif (!Auth::check())
                            <a href="{{ route('login') }}"
                                class="inline-flex w-full items-center justify-center rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                                Login to join & pay
                            </a>
                        @else
                            <div class="rounded-[22px] bg-amber-50 p-4 text-center text-sm font-semibold text-amber-700">
                                This invitation is for a different email address.
                            </div>
                        @endif
                    </div>
                @endif
            </section>

            <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <h2 class="text-lg font-semibold text-gray-900">Members ({{ $totalCount }})</h2>

                <div class="mt-4 space-y-3">
                    @foreach ($members as $member)
                        <div class="flex items-center justify-between rounded-[18px] border border-gray-100 bg-gray-50/50 p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $member->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' }}">
                                    @if ($member->payment_status === 'paid')
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                    @else
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3a1 1 0 002 0V7z" clip-rule="evenodd" /></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $member->name ?? $member->email }}
                                        @if ($member->is_initiator)
                                            <span class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">Organizer</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $member->email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">RM {{ number_format($member->share_amount, 2) }}</p>
                                <p class="text-xs {{ $member->payment_status === 'paid' ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $member->payment_status === 'paid' ? 'Paid' : 'Pending' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($groupPayment->status === 'pending' && !$isExpired && !$isCancelled)
                    <div class="mt-6 rounded-[22px] bg-[#fbf7ee] p-4">
                        <p class="text-sm font-semibold text-gray-900">Share this link</p>
                        <p class="mt-1 text-xs text-gray-500">Send this link to your friends so they can join and pay.</p>
                        <div class="mt-3 flex gap-2">
                            <input type="text" readonly value="{{ route('group-payment.show', $groupPayment->token) }}"
                                class="flex-1 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-600"
                                onclick="this.select()">
                            <button onclick="navigator.clipboard.writeText('{{ route('group-payment.show', $groupPayment->token) }}'); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy', 2000)"
                                class="rounded-xl bg-green-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-green-700">
                                Copy
                            </button>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
