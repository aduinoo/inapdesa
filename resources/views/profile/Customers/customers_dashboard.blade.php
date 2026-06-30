@extends('profile.Customers.app_customers')

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-10">
    <div class="mx-auto max-w-7xl px-6">

        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Customer Dashboard</p>
                <h1 class="mt-3 text-3xl font-semibold text-gray-900">Welcome back, {{ Auth::user()->name }}</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">
                    {{ $aiSummary['summary'] }}
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('home-page') }}"
                    class="inline-flex items-center justify-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Explore homes
                </a>
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[28px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-700">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <p class="text-sm text-gray-500">Total Bookings</p>
                </div>
                <p class="text-3xl font-semibold text-[#173423]">{{ $totalBookings }}</p>
                <p class="mt-2 text-xs text-gray-400">{{ $confirmedBookings }} confirmed &middot; {{ $cancelledBookings }} cancelled</p>
            </div>
            <div class="rounded-[28px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <i class="fa-solid fa-map-pin"></i>
                    </div>
                    <p class="text-sm text-gray-500">Active Trips</p>
                </div>
                <p class="text-3xl font-semibold text-[#173423]">{{ $activeTrips }}</p>
                <p class="mt-2 text-xs text-gray-400">Currently in progress</p>
            </div>
            <div class="rounded-[28px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <p class="text-sm text-gray-500">Total Spent</p>
                </div>
                <p class="text-3xl font-semibold text-[#173423]">RM {{ number_format($totalSpent, 0) }}</p>
                <p class="mt-2 text-xs text-gray-400">On confirmed stays</p>
            </div>
            <div class="rounded-[28px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5 cursor-pointer hover:ring-pink-300 transition">
                <a href="{{ route('user.wishlist.index') }}" class="block">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-pink-100 text-pink-700">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <p class="text-sm text-gray-500">Wishlist</p>
                </div>
                <p class="text-3xl font-semibold text-[#173423]">{{ $wishlistCount }}</p>
                <p class="mt-2 text-xs text-gray-400">Saved homestays</p>
                </a>
            </div>
        </div>

        <section class="mt-8 rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5 sm:p-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Analytics</p>
                    <h2 class="mt-2 text-2xl font-semibold text-gray-900">Monthly bookings overview</h2>
                </div>
            </div>
            <div class="h-[320px]">
                <canvas id="bookingsChart"></canvas>
            </div>
        </section>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5 sm:p-8">
                <div class="mb-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Spending</p>
                    <h2 class="mt-2 text-2xl font-semibold text-gray-900">Monthly expenditure</h2>
                </div>
                <div class="h-[280px]">
                    <canvas id="spendingChart"></canvas>
                </div>
            </section>

            @if($topDestinations->count() > 0)
                <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5 sm:p-8">
                    <div class="mb-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Destinations</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Top places visited</h2>
                    </div>
                    <div class="h-[280px]">
                        <canvas id="destinationsChart"></canvas>
                    </div>
                </section>
            @else
                <section class="rounded-[30px] bg-gradient-to-br from-[#173423] via-[#1f5c3c] to-[#2a8055] p-6 text-white shadow-[0_20px_60px_rgba(14,43,29,0.22)] ring-1 ring-black/5 sm:p-8">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20">
                            <i class="fa-solid fa-robot text-2xl text-green-100"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-100/70">AI Insight</p>
                            <h2 class="mt-2 text-xl font-semibold">{{ $aiSummary['headline'] }}</h2>

                            @if(!empty($aiSummary['insights']))
                                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                    @foreach($aiSummary['insights'] as $insight)
                                        <div class="rounded-2xl border border-white/10 bg-white/8 px-4 py-3">
                                            <div class="flex items-center gap-2 mb-1">
                                                <i class="fa-solid fa-{{ $insight['icon'] }} text-green-200 text-xs"></i>
                                                <span class="text-xs font-semibold uppercase tracking-wider text-green-200/80">{{ $insight['label'] }}</span>
                                            </div>
                                            <p class="text-lg font-semibold">{{ $insight['value'] }}</p>
                                            <p class="mt-1 text-xs text-green-100/70">{{ $insight['detail'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($aiSummary['trends']))
                                <div class="mt-4 space-y-2">
                                    @foreach($aiSummary['trends'] as $trend)
                                        <div class="flex items-center gap-3 rounded-xl bg-white/8 px-4 py-3 border border-white/10">
                                            @switch($trend['direction'])
                                                @case('up')
                                                    <i class="fa-solid fa-arrow-trend-up text-green-300"></i>
                                                    @break
                                                @case('down')
                                                    <i class="fa-solid fa-arrow-trend-down text-amber-300"></i>
                                                    @break
                                                @case('stable')
                                                    <i class="fa-solid fa-minus text-blue-300"></i>
                                                    @break
                                                @case('peak')
                                                    <i class="fa-solid fa-fire text-orange-300"></i>
                                                    @break
                                                @case('favorite')
                                                    <i class="fa-solid fa-star text-yellow-300"></i>
                                                    @break
                                                @default
                                                    <i class="fa-solid fa-chart-line text-green-300"></i>
                                            @endswitch
                                            <div>
                                                <span class="text-xs font-semibold uppercase tracking-wider text-green-200/80">{{ $trend['label'] }}</span>
                                                <p class="text-sm text-green-50/90">{{ $trend['detail'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-5 rounded-xl bg-white/10 px-4 py-3 border border-white/10">
                                <p class="text-sm font-medium text-green-50">
                                    <i class="fa-solid fa-lightbulb mr-2 text-yellow-300"></i>
                                    {{ $aiSummary['recommendation'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        </div>

        @if($topDestinations->count() > 0)
            <section class="mt-6 rounded-[30px] bg-gradient-to-br from-[#173423] via-[#1f5c3c] to-[#2a8055] p-6 text-white shadow-[0_20px_60px_rgba(14,43,29,0.22)] ring-1 ring-black/5 sm:p-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20">
                        <i class="fa-solid fa-robot text-2xl text-green-100"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-100/70">AI Insight</p>
                        <h2 class="mt-2 text-xl font-semibold">{{ $aiSummary['headline'] }}</h2>

                        @if(!empty($aiSummary['insights']))
                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                @foreach($aiSummary['insights'] as $insight)
                                    <div class="rounded-2xl border border-white/10 bg-white/8 px-4 py-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <i class="fa-solid fa-{{ $insight['icon'] }} text-green-200 text-xs"></i>
                                            <span class="text-xs font-semibold uppercase tracking-wider text-green-200/80">{{ $insight['label'] }}</span>
                                        </div>
                                        <p class="text-lg font-semibold">{{ $insight['value'] }}</p>
                                        <p class="mt-1 text-xs text-green-100/70">{{ $insight['detail'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($aiSummary['trends']))
                            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                @foreach($aiSummary['trends'] as $trend)
                                    <div class="flex items-center gap-3 rounded-xl bg-white/8 px-4 py-3 border border-white/10">
                                        @switch($trend['direction'])
                                            @case('up')
                                                <i class="fa-solid fa-arrow-trend-up text-green-300"></i>
                                                @break
                                            @case('down')
                                                <i class="fa-solid fa-arrow-trend-down text-amber-300"></i>
                                                @break
                                            @case('stable')
                                                <i class="fa-solid fa-minus text-blue-300"></i>
                                                @break
                                            @case('peak')
                                                <i class="fa-solid fa-fire text-orange-300"></i>
                                                @break
                                            @case('favorite')
                                                <i class="fa-solid fa-star text-yellow-300"></i>
                                                @break
                                            @default
                                                <i class="fa-solid fa-chart-line text-green-300"></i>
                                        @endswitch
                                        <div>
                                            <span class="text-xs font-semibold uppercase tracking-wider text-green-200/80">{{ $trend['label'] }}</span>
                                            <p class="text-sm text-green-50/90">{{ $trend['detail'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-5 rounded-xl bg-white/10 px-4 py-3 border border-white/10">
                            <p class="text-sm font-medium text-green-50">
                                <i class="fa-solid fa-lightbulb mr-2 text-yellow-300"></i>
                                {{ $aiSummary['recommendation'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        
    </div>
</div>

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
                ticks: { font: { size: 13, family: 'system-ui' }, color: '#6b7280' },
            },
            y: {
                beginAtZero: true,
                grid: { color: '#f0ece2' },
                ticks: { font: { size: 13, family: 'system-ui' }, color: '#6b7280' },
            },
        },
    };

    const monthlyBookings = {{ Illuminate\Support\Js::from($monthlyBookings) }};
    const monthlySpending = {{ Illuminate\Support\Js::from($monthlySpending) }};
    const topDestinations = {{ Illuminate\Support\Js::from($topDestinations) }};

    new Chart(document.getElementById('bookingsChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(monthlyBookings),
            datasets: [{
                label: 'Bookings',
                data: Object.values(monthlyBookings),
                backgroundColor: '#1f5c3c',
                borderRadius: 10,
                barThickness: 42,
            }],
        },
        options: {
            ...chartDefaults,
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 14, family: 'system-ui' }, color: '#374151', padding: 20 },
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

    new Chart(document.getElementById('spendingChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(monthlySpending),
            datasets: [{
                label: 'Spending (RM)',
                data: Object.values(monthlySpending),
                backgroundColor: '#8b6914',
                borderRadius: 10,
                barThickness: 42,
            }],
        },
        options: {
            ...chartDefaults,
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 14, family: 'system-ui' }, color: '#374151', padding: 20 },
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

    @if($topDestinations->count() > 0)
        const destLabels = topDestinations.map(function (d) { return d.city || 'Unknown'; });
        const destValues = topDestinations.map(function (d) { return d.visit_count; });

        new Chart(document.getElementById('destinationsChart'), {
            type: 'bar',
            data: {
                labels: destLabels,
                datasets: [{
                    data: destValues,
                    backgroundColor: ['#1f5c3c', '#2a8055', '#3da06e', '#52c088', '#66e0a2'],
                    borderRadius: 10,
                    barThickness: 40,
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
                            font: { size: 13, family: 'system-ui' },
                            color: '#6b7280',
                            stepSize: 1,
                            callback: function (value) { return Number.isInteger(value) ? value : null; },
                        },
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 14, family: 'system-ui', weight: '500' }, color: '#374151' },
                    },
                },
            },
        });
    @endif
});
</script>
@endpush
@endsection