@extends('profile.Customers.app_customers')

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-10">
    <div class="mx-auto max-w-7xl px-6">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">My Bookings</p>
                <h1 class="mt-3 text-3xl font-semibold text-gray-900">All your stays</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">
                    Track active, upcoming, past, and cancelled bookings in one place.
                </p>
            </div>
            <a href="{{ route('home-page') }}"
                class="inline-flex items-center justify-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                <i class="fa-solid fa-magnifying-glass mr-2"></i> Explore homes
            </a>
        </div>

        <div x-data="{ tab: 'active' }" class="space-y-6">
            <nav class="flex gap-2 overflow-x-auto rounded-[28px] bg-white p-1.5 shadow-[0_8px_30px_rgba(47,62,42,0.06)] ring-1 ring-black/5">
                @if($activeBookings->count() > 0)
                    <button @click="tab = 'active'"
                        :class="tab === 'active' ? 'bg-green-600 text-white shadow-lg' : 'text-gray-600 hover:bg-gray-50'"
                        class="flex items-center gap-2 rounded-full px-5 py-3 text-sm font-semibold transition shrink-0">
                        <i class="fa-solid fa-map-pin"></i> Active
                        <span class="inline-flex items-center justify-center rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">{{ $activeBookings->count() }}</span>
                    </button>
                @endif
                @if($upcomingBookings->count() > 0)
                    <button @click="tab = 'upcoming'"
                        :class="tab === 'upcoming' ? 'bg-green-600 text-white shadow-lg' : 'text-gray-600 hover:bg-gray-50'"
                        class="flex items-center gap-2 rounded-full px-5 py-3 text-sm font-semibold transition shrink-0">
                        <i class="fa-solid fa-calendar"></i> Upcoming
                        <span class="inline-flex items-center justify-center rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">{{ $upcomingBookings->count() }}</span>
                    </button>
                @endif
                @if($pastBookings->count() > 0)
                    <button @click="tab = 'past'"
                        :class="tab === 'past' ? 'bg-green-600 text-white shadow-lg' : 'text-gray-600 hover:bg-gray-50'"
                        class="flex items-center gap-2 rounded-full px-5 py-3 text-sm font-semibold transition shrink-0">
                        <i class="fa-solid fa-clock-rotate-left"></i> Past
                        <span class="inline-flex items-center justify-center rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">{{ $pastBookings->count() }}</span>
                    </button>
                @endif
                @if($cancelledBookings->count() > 0)
                    <button @click="tab = 'cancelled'"
                        :class="tab === 'cancelled' ? 'bg-green-600 text-white shadow-lg' : 'text-gray-600 hover:bg-gray-50'"
                        class="flex items-center gap-2 rounded-full px-5 py-3 text-sm font-semibold transition shrink-0">
                        <i class="fa-solid fa-ban"></i> Cancelled
                        <span class="inline-flex items-center justify-center rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">{{ $cancelledBookings->count() }}</span>
                    </button>
                @endif
            </nav>

            @if($activeBookings->count() > 0 || $upcomingBookings->count() > 0 || $pastBookings->count() > 0 || $cancelledBookings->count() > 0)
                @if($activeBookings->count() > 0)
                    <div x-show="tab === 'active'" x-transition class="space-y-4">
                        @foreach($activeBookings as $booking)
                            @include('profile.Customers.partials.booking-card', ['booking' => $booking, 'status' => 'active'])
                        @endforeach
                    </div>
                @endif

                @if($upcomingBookings->count() > 0)
                    <div x-show="tab === 'upcoming'" x-transition class="space-y-4">
                        @foreach($upcomingBookings as $booking)
                            @include('profile.Customers.partials.booking-card', ['booking' => $booking, 'status' => 'upcoming'])
                        @endforeach
                    </div>
                @endif

                @if($pastBookings->count() > 0)
                    <div x-show="tab === 'past'" x-transition class="space-y-4">
                        @foreach($pastBookings as $booking)
                            @include('profile.Customers.partials.booking-card', ['booking' => $booking, 'status' => 'past'])
                        @endforeach
                    </div>
                @endif

                @if($cancelledBookings->count() > 0)
                    <div x-show="tab === 'cancelled'" x-transition class="space-y-4">
                        @foreach($cancelledBookings as $booking)
                            @include('profile.Customers.partials.booking-card', ['booking' => $booking, 'status' => 'cancelled'])
                        @endforeach
                    </div>
                @endif
            @else
                <div class="rounded-[30px] bg-white p-12 text-center shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                    <div class="flex justify-center mb-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-50">
                            <i class="fa-solid fa-calendar-plus text-3xl text-green-300"></i>
                        </div>
                    </div>
                    <p class="text-xl font-semibold text-gray-900">No bookings yet</p>
                    <p class="mt-3 text-sm text-gray-500 max-w-md mx-auto">
                        You haven't made any bookings yet. Start by browsing our homestays and find the perfect village stay for your next trip.
                    </p>
                    <a href="{{ route('home-page') }}"
                        class="mt-6 inline-flex items-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                        Browse homestays
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function requestCancellation(bookingId) {
    Swal.fire({
        title: 'Request cancellation?',
        text: 'This will open the cancellation options page where you can review the policy and submit your request.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, continue',
        cancelButtonText: 'Close',
        confirmButtonColor: '#dc2626',
        customClass: { popup: 'rounded-[28px]' },
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '{{ route('user.cancellation-options.index') }}' + '?booking_id=' + bookingId;
        }
    });
}
</script>
@endpush
@endsection