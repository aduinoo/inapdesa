@extends('profile.homestayOwners.app_ownerHomestay')

@section('content')

<main class="max-w-7xl mx-auto px-6 py-10">

    <!-- Welcome -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">
            Welcome, {{ Auth::user()->name }} 👋
        </h1>
        <p class="text-gray-600 mt-1">
            Manage your homestays, bookings, and earnings here.
        </p>
    </div>

    <!-- ================= STATS ================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Total Homestays</p>
            <p class="text-3xl font-bold text-green-700 mt-2">
                {{ $totalHomestays }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Active Bookings</p>
            <p class="text-3xl font-bold text-green-700 mt-2">
                {{ $activeBookings }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Total Earnings</p>
            <p class="text-3xl font-bold text-green-700 mt-2">
                RM {{ number_format($totalEarnings ?? 0, 2) }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500">Average Rating</p>
            <p class="text-3xl font-bold text-green-700 mt-2">
                {{ $averageRating ? number_format($averageRating, 1) . ' ★' : '—' }}
            </p>
        </div>

    </div>

    <!-- ================= UPCOMING BOOKINGS ================= -->
    <div class="bg-white rounded-xl shadow p-6 mb-10">
        <h3 class="text-lg font-semibold mb-4">📅 Upcoming Bookings</h3>

        @forelse($upcomingBookings as $booking)
            <div class="flex justify-between border-b py-3 text-sm">
                <div>
                    <p class="font-semibold">
                        Homestay #{{ $booking->homestay_id }}
                    </p>
                    <p class="text-gray-500">
                        {{ $booking->check_in }} → {{ $booking->check_out }}
                    </p>
                </div>
                <span class="text-green-700 font-semibold">
                    RM {{ number_format($booking->total_price, 2) }}
                </span>
            </div>
        @empty
            <p class="text-gray-500 text-sm">No upcoming bookings.</p>
        @endforelse
    </div>

    <!-- ================= REPORTS ================= -->
    <div class="bg-white rounded-xl shadow p-6 mb-10">
        <h3 class="text-lg font-semibold mb-4 text-red-600">🚨 Guest Reports</h3>

        @forelse($reports as $report)
            <div class="border-b py-3 text-sm">
                <p class="font-semibold">
                    Homestay #{{ $report->homestay_id }}
                </p>
                <p class="text-gray-600">
                    {{ \Illuminate\Support\Str::limit($report->description, 80) }}
                </p>
                <span class="text-xs text-red-500 uppercase">
                    {{ $report->status }}
                </span>
            </div>
        @empty
            <p class="text-gray-500 text-sm">No reports received 👍</p>
        @endforelse
    </div>

    <!-- ================= QUICK ACTIONS ================= -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-2">🏠 Manage Homestays</h3>
            <p class="text-gray-600 text-sm mb-4">
                Add, edit, or remove your homestay listings.
            </p>
            <a href="#"
               class="inline-block rounded-lg bg-green-600 px-4 py-2 text-white font-semibold hover:bg-green-700">
                Add New Homestay
            </a>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-2">📊 Earnings & Availability</h3>
            <p class="text-gray-600 text-sm mb-4">
                Update availability and track earnings.
            </p>
            <a href="#"
               class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold hover:bg-blue-700">
                Manage Calendar
            </a>
        </div>

    </div>

</main>

@endsection
