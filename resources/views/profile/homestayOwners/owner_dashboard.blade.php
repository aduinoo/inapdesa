@extends('profile.homestayOwners.app_ownerHomestay')

@section('content')

<main class="max-w-7xl mx-auto px-6 py-10">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">
            Welcome, {{ Auth::user()->name }}
        </h1>
        <p class="text-gray-600 mt-1">
            Manage your homestays, bookings, earnings, and 360 room scans here.
        </p>
    </div>

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
                {{ $averageRating ? number_format($averageRating, 1) . ' *' : '-' }}
            </p>
        </div>
    </div>

    @if($homestayPerformance->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Bookings</h3>
            <div class="relative h-64">
                <canvas id="monthlyBookingsChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Revenue</h3>
            <div class="relative h-64">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Booking Status</h3>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="bookingStatusChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Homestay Performance</h3>
            <div class="relative h-64">
                <canvas id="homestayPerformanceChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6 mb-10">
        <h3 class="text-lg font-semibold mb-4">Upcoming Bookings</h3>

        @forelse($upcomingBookings as $booking)
            <div class="flex flex-col gap-3 border-b py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold">
                        Homestay #{{ $booking->homestay_id }}
                    </p>
                    <p class="text-gray-500">
                        {{ $booking->check_in }} to {{ $booking->check_out }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-green-700 font-semibold">
                        RM {{ number_format($booking->total_price, 2) }}
                    </span>
                    <a href="{{ route('owner.messages.show', $booking->booking_id) }}"
                       class="inline-flex items-center rounded-full bg-green-600 px-4 py-2 text-xs font-semibold text-white hover:bg-green-700">
                        Chat guest
                    </a>
                </div>
            </div>
        @empty
            <p class="text-gray-500 text-sm">No upcoming bookings.</p>
        @endforelse
    </div>

    

    

</main>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { size: 12, family: 'system-ui' }, color: '#6b7280' },
            },
            y: {
                beginAtZero: true,
                grid: { color: '#f0ece2' },
                ticks: { font: { size: 12, family: 'system-ui' }, color: '#6b7280' },
            },
        },
    };

    const monthlyBookings = {{ Illuminate\Support\Js::from($monthlyBookings) }};
    const monthlyRevenue = {{ Illuminate\Support\Js::from($monthlyRevenue) }};
    const bookingStatusBreakdown = {{ Illuminate\Support\Js::from($bookingStatusBreakdown) }};
    const homestayPerformance = {{ Illuminate\Support\Js::from($homestayPerformance) }};

    if (Object.keys(monthlyBookings).length > 0) {
        new Chart(document.getElementById('monthlyBookingsChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(monthlyBookings),
                datasets: [{
                    label: 'Bookings',
                    data: Object.values(monthlyBookings),
                    backgroundColor: '#1f5c3c',
                    borderRadius: 8,
                    barThickness: 36,
                }],
            },
            options: {
                ...chartDefaults,
                plugins: {
                    legend: {
                        display: true,
                        labels: { font: { size: 13, family: 'system-ui' }, color: '#374151', padding: 16 },
                    },
                },
                scales: {
                    ...chartDefaults.scales,
                    y: {
                        ...chartDefaults.scales.y,
                        ticks: {
                            ...chartDefaults.scales.y.ticks,
                            stepSize: 1,
                            callback: function (value) { return Number.isInteger(value) ? value : null; },
                        },
                    },
                },
            },
        });
    }

    if (Object.keys(monthlyRevenue).length > 0) {
        new Chart(document.getElementById('monthlyRevenueChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(monthlyRevenue),
                datasets: [{
                    label: 'Revenue (RM)',
                    data: Object.values(monthlyRevenue),
                    backgroundColor: '#8b6914',
                    borderRadius: 8,
                    barThickness: 36,
                }],
            },
            options: {
                ...chartDefaults,
                plugins: {
                    legend: {
                        display: true,
                        labels: { font: { size: 13, family: 'system-ui' }, color: '#374151', padding: 16 },
                    },
                },
                scales: {
                    ...chartDefaults.scales,
                    y: {
                        ...chartDefaults.scales.y,
                        ticks: {
                            ...chartDefaults.scales.y.ticks,
                            callback: function (value) { return 'RM ' + value; },
                        },
                    },
                },
            },
        });
    }

    if (Object.keys(bookingStatusBreakdown).length > 0) {
        const statusLabels = Object.keys(bookingStatusBreakdown);
        const statusColors = {
            confirmed: '#1f5c3c',
            completed: '#3da06e',
            cancelled: '#dc2626',
            pending_payment: '#f59e0b',
        };

        new Chart(document.getElementById('bookingStatusChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels.map(function (s) {
                    return s.replace('_', ' ').replace(/\b\w/g, function (l) { return l.toUpperCase(); });
                }),
                datasets: [{
                    data: Object.values(bookingStatusBreakdown),
                    backgroundColor: statusLabels.map(function (s) { return statusColors[s] || '#6b7280'; }),
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 13, family: 'system-ui' }, color: '#374151', padding: 16 },
                    },
                },
            },
        });
    }

    if (homestayPerformance.length > 0) {
        const perfLabels = homestayPerformance.map(function (h) { return h.homestay_name; });
        const perfValues = homestayPerformance.map(function (h) { return h.count; });

        new Chart(document.getElementById('homestayPerformanceChart'), {
            type: 'bar',
            data: {
                labels: perfLabels,
                datasets: [{
                    data: perfValues,
                    backgroundColor: ['#1f5c3c', '#2a8055', '#3da06e', '#52c088', '#66e0a2'],
                    borderRadius: 8,
                    barThickness: 32,
                }],
            },
            options: {
                ...chartDefaults,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f0ece2' },
                        ticks: {
                            font: { size: 12, family: 'system-ui' },
                            color: '#6b7280',
                            stepSize: 1,
                            callback: function (value) { return Number.isInteger(value) ? value : null; },
                        },
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 13, family: 'system-ui' }, color: '#374151' },
                    },
                },
            },
        });
    }
});
</script>
@endpush

@endsection
