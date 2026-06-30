@extends($layout)

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Inbox</p>
                <h1 class="mt-3 text-3xl font-semibold text-gray-900">{{ $booking->homestay_name }}</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">
                    {{ $otherPartyLabel }}: {{ $otherPartyLabel === 'Guest' ? $booking->customer_name : $booking->owner_name }}
                </p>
            </div>
            <a href="{{ $indexRoute }}"
                class="inline-flex items-center justify-center rounded-full border border-[#d9d0be] bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-green-700 hover:text-green-700">
                Back to messages
            </a>
        </div>

        @php
            $otherPartyName = $otherPartyLabel === 'Guest' ? $booking->customer_name : $booking->owner_name;
            $initials = collect(explode(' ', trim($otherPartyName)))
                ->filter()
                ->take(2)
                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                ->implode('');
        @endphp

        <div class="grid gap-6 xl:grid-cols-[1.45fr_0.75fr]">
            <section class="overflow-hidden rounded-[32px] border border-[#ebe2d3] bg-white shadow-[0_24px_70px_rgba(28,41,33,0.08)]">
                <div class="border-b border-[#efe7da] bg-gradient-to-r from-[#fcfaf5] via-white to-[#f7f3ea] px-6 py-5">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#173423] text-sm font-semibold text-white">
                            {{ $initials ?: 'RR' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">{{ $otherPartyName }}</h2>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Booking #{{ $booking->booking_id }} &middot; {{ ucfirst($booking->booking_status) }}
                                    </p>
                                </div>
                                <div class="rounded-full bg-[#eef5eb] px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-green-700">
                                    In chat
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-[#fcfaf5] px-4 py-4 sm:px-6">
                    <div class="max-h-[540px] min-h-[420px] space-y-4 overflow-y-auto pr-1">
                        @forelse ($messages as $message)
                            @php
                                $isMine = (int) $message->sender_id === (int) $currentUserId;
                            @endphp
                            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[85%]">
                                    @unless ($isMine)
                                        <p class="mb-2 px-1 text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">
                                            {{ $message->sender_name }}
                                        </p>
                                    @endunless

                                    <div class="{{ $isMine ? 'rounded-[24px] rounded-br-[8px] bg-green-600 text-white' : 'rounded-[24px] rounded-bl-[8px] bg-white text-gray-800 ring-1 ring-[#eadfce]' }} px-4 py-3 shadow-sm">
                                        <p class="text-sm leading-6">{{ $message->message }}</p>
                                    </div>

                                    <p class="mt-2 px-1 text-xs {{ $isMine ? 'text-right text-gray-400' : 'text-gray-400' }}">
                                        {{ \Carbon\Carbon::parse($message->created_at)->format('d M Y, h:i A') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="flex h-[420px] items-center justify-center">
                                <div class="text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-2xl text-green-700 ring-1 ring-[#eadfce]">
                                        &#9998;
                                    </div>
                                    <p class="mt-5 text-lg font-semibold text-gray-900">Start the conversation</p>
                                    <p class="mt-2 text-sm text-gray-500">Send the first message below.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <form action="{{ $storeRoute }}" method="POST" class="border-t border-[#efe7da] bg-white p-4 sm:p-5">
                    @csrf
                    <div class="rounded-[28px] border border-[#ddd4c2] bg-[#fcfaf5] p-3 shadow-sm">
                        <label for="message" class="sr-only">Message</label>
                        <textarea id="message" name="message" rows="3"
                            class="w-full resize-none border-none bg-transparent px-2 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-0"
                            placeholder="Write a message about check-in, arrival time, or anything the guest/owner should know...">{{ old('message') }}</textarea>

                        <div class="flex items-center justify-between gap-4 border-t border-[#ece3d5] px-2 pt-3">
                            <p class="text-xs text-gray-500">Keep messages related to this booking.</p>
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-full bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700">
                                Send
                            </button>
                        </div>
                    </div>

                    @error('message')
                        <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </form>
            </section>

            <aside class="space-y-6">
                <section class="rounded-[30px] border border-[#ebe2d3] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)]">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Stay Details</p>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-[22px] bg-[#fbf7ee] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Booking reference</p>
                            <p class="mt-2 text-lg font-semibold text-gray-900">#{{ $booking->booking_id }}</p>
                        </div>

                        <div class="rounded-[22px] bg-[#fbf7ee] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Dates</p>
                            <p class="mt-2 text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }} - {{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}
                            </p>
                        </div>

                        <div class="rounded-[22px] bg-[#fbf7ee] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Location</p>
                            <p class="mt-2 text-sm font-medium text-gray-900">
                                {{ trim(collect([$booking->city, $booking->state])->filter()->implode(', ')) ?: 'Malaysia' }}
                            </p>
                        </div>

                        <div class="rounded-[22px] bg-[#fbf7ee] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Booking total</p>
                            <p class="mt-2 text-sm font-medium text-gray-900">RM {{ number_format($booking->total_price, 2) }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-[30px] border border-[#ebe2d3] bg-gradient-to-br from-green-900 via-green-800 to-[#163d2a] p-6 text-white shadow-[0_18px_50px_rgba(23,52,35,0.18)]">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-100/70">Chat Tips</p>
                    <div class="mt-4 space-y-3 text-sm leading-6 text-green-50/85">
                        <p>Use this space like a stay chat: ask about check-in time, parking, arrival details, and house rules.</p>
                        <p>Both sides only see chats for real bookings, so the conversation stays private to the reservation.</p>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
