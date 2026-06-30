@php
$isActive = $status === 'active';
$isUpcoming = $status === 'upcoming';
$isPast = $status === 'past';
$isCancelled = $status === 'cancelled';
$isPending = (($booking->booking_status ?? '') === 'pending_payment');

$checkIn = \Carbon\Carbon::parse($booking->check_in);
$checkOut = \Carbon\Carbon::parse($booking->check_out);
$nights = $checkIn->diffInDays($checkOut);
@endphp

<div class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5 sm:p-7 {{ $isCancelled ? 'opacity-70' : '' }}">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex-1 space-y-3">
            <div class="flex flex-wrap items-center gap-3">
                <h3 class="text-xl font-semibold text-gray-900">
                    {{ $booking->homestay_name ?? 'Homestay #' . $booking->homestay_id }}
                </h3>
                @if($isActive)
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        Active now
                    </span>
                @elseif($isUpcoming)
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Upcoming</span>
                @elseif($isPast)
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Completed</span>
                @elseif($isCancelled)
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-600">Cancelled</span>
                @elseif($isPending)
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pending payment</span>
                @endif
            </div>

            <p class="text-sm text-gray-500">
                <i class="fa-solid fa-location-dot mr-1.5 text-green-600"></i>
                {{ trim(collect([$booking->city, $booking->state])->filter()->implode(', ')) ?: 'Malaysia' }}
            </p>

            <div class="flex flex-wrap gap-6">
                <div class="rounded-xl border border-[#e9e2d5] bg-[#fcfaf4] px-4 py-3">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Check-in</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $checkIn->format('D, d M Y') }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $checkIn->format('g:i A') }}</p>
                </div>
                <div class="flex items-center text-gray-300">
                    <i class="fa-solid fa-arrow-right text-lg"></i>
                </div>
                <div class="rounded-xl border border-[#e9e2d5] bg-[#fcfaf4] px-4 py-3">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Check-out</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $checkOut->format('D, d M Y') }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $checkOut->format('g:i A') }}</p>
                </div>
                <div class="rounded-xl border border-[#e9e2d5] bg-[#fcfaf4] px-4 py-3">
                    <p class="text-xs text-gray-400 uppercase tracking-wider">Nights</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $nights }}</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-start gap-3 lg:items-end shrink-0">
            <div class="text-right">
                <p class="text-xs text-gray-400 uppercase tracking-wider">Total paid</p>
                <p class="text-2xl font-bold text-green-700">RM {{ number_format($booking->total_price ?? 0, 2) }}</p>
                @if($nights > 0)
                    <p class="text-xs text-gray-400">RM {{ number_format(($booking->total_price ?? 0) / $nights, 2) }} / night</p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                @if($isActive || $isUpcoming)
                    <a href="{{ route('user.messages.show', $booking->booking_id) }}"
                        class="inline-flex items-center rounded-full bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">
                        <i class="fa-solid fa-comment mr-1.5"></i> Chat owner
                    </a>
                    @if(!$isCancelled)
                        <button onclick="requestCancellation('{{ $booking->booking_id }}')"
                            class="inline-flex items-center rounded-full border border-red-300 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                            <i class="fa-solid fa-ban mr-1.5"></i> Cancel
                        </button>
                    @endif
                @elseif($isPast)
                    <a href="{{ route('user.messages.show', $booking->booking_id) }}"
                        class="inline-flex items-center rounded-full border border-green-600 px-4 py-2 text-sm font-semibold text-green-700 transition hover:bg-green-50">
                        <i class="fa-solid fa-comment mr-1.5"></i> View chat
                    </a>
                @endif
            </div>

            <p class="text-xs text-gray-400">
                <i class="fa-solid fa-calendar-check mr-1"></i>
                Booked {{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}
            </p>
        </div>
    </div>
</div>

