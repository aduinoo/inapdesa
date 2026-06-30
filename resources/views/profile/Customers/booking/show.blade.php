@extends(auth()->check() && in_array((int) auth()->user()->role, [2, 3], true) ? 'profile.Customers.app_customers' : 'layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@php
    $locationLine = trim(collect([$homestay->city, $homestay->state])->filter()->implode(', ')) ?: 'Malaysia';
    $fullAddress = trim(implode(', ', array_filter([$homestay->address, $homestay->street, $homestay->city, $homestay->zipcode, $homestay->state, 'Malaysia'])));
    $photoCount = $images->count();
    $galleryImages = ($hasPanorama ?? false) ? $images->slice(0) : $images->slice(1);
    $tourRecommendations = array_slice($localRecommendations['tours'] ?? [], 0, 10);
    $foodRecommendations = array_slice($localRecommendations['food'] ?? [], 0, 10);
    $nearestTransport = $transportInfo['nearest_point'] ?? null;
    $transportMapPoints = collect([[
        'name' => $homestay->homestay_name,
        'type' => 'Homestay',
        'latitude' => is_numeric($homestay->latitude ?? null) ? (float) $homestay->latitude : null,
        'longitude' => is_numeric($homestay->longitude ?? null) ? (float) $homestay->longitude : null,
        'distance' => 0,
    ]])
        ->merge(!empty($transportInfo['nearest_train_station']) ? [[
            'name' => $transportInfo['nearest_train_station']['name'],
            'type' => 'Train station',
            'latitude' => $transportInfo['nearest_train_station']['latitude'],
            'longitude' => $transportInfo['nearest_train_station']['longitude'],
            'distance' => $transportInfo['nearest_train_station']['distance'],
        ]] : [])
        ->merge(collect($transportInfo['bus_stops'] ?? [])->map(function (array $stop) {
            return [
                'name' => $stop['name'],
                'type' => 'Bus stop',
                'latitude' => $stop['latitude'],
                'longitude' => $stop['longitude'],
                'distance' => $stop['distance'],
            ];
        }))
        ->filter(fn (array $point) => is_numeric($point['latitude'] ?? null) && is_numeric($point['longitude'] ?? null))
        ->values()
        ->all();
@endphp
<style>
    .recommendation-scroll {
        scrollbar-width: auto;
        scrollbar-color: #94a3b8 #f3f4f6;
        scrollbar-gutter: stable;
    }

    .recommendation-scroll::-webkit-scrollbar {
        height: 12px;
    }

    .recommendation-scroll::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 9999px;
    }

    .recommendation-scroll::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border: 2px solid #f3f4f6;
        border-radius: 9999px;
    }

    .transport-map-shell .leaflet-control-attribution {
        font-size: 10px;
    }
</style>
<div class="min-h-screen bg-white py-8">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="mb-8 flex flex-col gap-4 border-b border-gray-200 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-green-700/70">Stay Details</p>
                <h1 class="mt-3 text-[2rem] font-semibold leading-tight text-gray-950">{{ $homestay->homestay_name }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-600">
                    <span>{{ $locationLine }}</span>
                    <span class="hidden h-1 w-1 rounded-full bg-gray-300 sm:inline-flex"></span>
                    <span>Up to {{ (int) $homestay->max_guest }} guests</span>
                    <span class="hidden h-1 w-1 rounded-full bg-gray-300 sm:inline-flex"></span>
                    <span>{{ $hasPanorama ? '360 room view available' : $photoCount . ' stay photos' }}</span>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center rounded-full bg-[#f5f8f2] px-4 py-2 text-sm font-medium text-green-700">
                    From RM {{ number_format($homestay->base_price, 2) }} per night
                </span>
                <a href="{{ route('home-page') }}"
                    class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-gray-900 hover:text-gray-900">
                    Back to homes
                </a>
            </div>
        </div>

        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-10 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="min-w-0 space-y-10">
                <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white">
                    <div class="grid gap-3 p-3 md:grid-cols-[1.45fr_0.85fr]">
                        <div>
                            @if ($hasPanorama ?? false)
                                <div class="rounded-[24px] bg-[#faf8f3] p-3 sm:p-4">
                                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-green-700/70">Immersive View</p>
                                            <p id="panorama-step" class="mt-1 text-sm font-semibold text-gray-900">Loading 360 room view...</p>
                                        </div>
                                        <div class="inline-flex rounded-full bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-700 ring-1 ring-black/5">
                                            360 Room View
                                        </div>
                                    </div>

                                    <div id="panorama-viewer"
                                        class="relative h-[430px] overflow-hidden rounded-[24px] bg-[radial-gradient(circle_at_top,#ffffff_0%,#f7f4ec_58%,#e6d7b7_100%)] ring-1 ring-black/5"
                                        style="touch-action: none;">
                                        <canvas id="panorama-canvas" class="absolute inset-0 h-full w-full"></canvas>
                                        <div class="pointer-events-none absolute inset-0 z-10 bg-[radial-gradient(circle_at_center,transparent_0%,transparent_56%,rgba(15,23,42,0.16)_100%)]"></div>
                                        <div class="pointer-events-none absolute inset-x-5 top-5 z-10 flex justify-center">
                                            <div class="rounded-full bg-white/92 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-700 shadow-sm ring-1 ring-black/5">
                                                Drag Or Use Two Fingers To Look Around
                                            </div>
                                        </div>
                                        <div id="panorama-loading"
                                            class="absolute inset-0 z-20 flex items-center justify-center bg-[#f7f4ec]/85 text-center text-sm font-semibold text-gray-500 backdrop-blur-sm">
                                            Building panorama viewer...
                                        </div>
                                        <div class="pointer-events-none absolute inset-x-0 bottom-0 z-10 bg-gradient-to-t from-black/70 via-black/18 to-transparent p-5 text-white">
                                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/75">Current View</p>
                                            <p id="panorama-name" class="mt-2 truncate text-lg font-semibold">{{ $homestay->homestay_name }} 360 view</p>
                                            <p class="mt-1 text-sm text-white/85">Explore the room before you book.</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <img src="{{ asset($images->first()->image_path ?? 'assets/images/homepage/home.jpg') }}"
                                    alt="{{ $homestay->homestay_name }}"
                                    class="h-full min-h-[320px] w-full rounded-[24px] object-cover">
                            @endif
                        </div>
                        <div class="rounded-[24px] bg-[#faf8f3] p-3">
                            @if ($galleryImages->isNotEmpty())
                                <div class="mb-3 flex items-center justify-between">
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-gray-500">Photo gallery</p>
                                    <div class="flex items-center gap-2">
                                        <button type="button" data-gallery-up
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 bg-white text-lg text-gray-700 transition hover:border-gray-900 hover:text-gray-900">
                                            &#9651;
                                        </button>
                                        <button type="button" data-gallery-down
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 bg-white text-lg text-gray-700 transition hover:border-gray-900 hover:text-gray-900">
                                            &#9661;
                                        </button>
                                    </div>
                                </div>

                                <div class="relative h-[430px] overflow-hidden rounded-[20px]">
                                    <div data-gallery-scroll class="h-full overflow-y-auto pr-1 [scrollbar-width:thin]">
                                        <div data-gallery-track class="flex flex-col gap-3">
                                        @foreach ($galleryImages as $image)
                                            <img src="{{ asset($image->image_path) }}"
                                                alt="{{ $homestay->homestay_name }}"
                                                class="h-[136px] w-full rounded-[20px] object-cover">
                                        @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex h-[153px] items-center justify-center rounded-[20px] bg-[#f7f2e6] text-sm text-gray-500">
                                    More photos coming soon
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="min-w-0 overflow-hidden border-b border-gray-200 pb-10">
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_240px]">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-950">About this stay</h2>
                            <p class="mt-4 text-sm leading-7 text-gray-600">
                                {{ $homestay->description ?: 'This homestay is ready for a comfortable village-style stay with a simple, peaceful atmosphere.' }}
                            </p>
                            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Guests</p>
                                    <p class="mt-2 text-lg font-semibold text-gray-950">{{ (int) $homestay->max_guest }}</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Booking</p>
                                    <p class="mt-2 text-lg font-semibold text-gray-950">Instant</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">View</p>
                                    <p class="mt-2 text-lg font-semibold text-gray-950">{{ $hasPanorama ? '360 ready' : $photoCount . ' photos' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-[24px] border border-gray-200 bg-[#faf8f3] p-5 text-sm text-gray-600">
                            <p class="font-semibold text-gray-950">Stay at a glance</p>
                            <p class="mt-3">{{ $locationLine }}</p>
                            <p class="mt-1">From RM {{ number_format($homestay->base_price, 2) }} per night</p>
                            <p class="mt-1">Instant confirmation after payment</p>
                        </div>
                    </div>
                </section>

                <section class="grid gap-8 border-b border-gray-200 pb-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)]">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-950">Where you’ll be</h3>
                        <p class="mt-3 text-sm leading-7 text-gray-600">
                            {{ $fullAddress }}
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if (is_numeric($homestay->latitude ?? null) && is_numeric($homestay->longitude ?? null))
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $homestay->latitude }},{{ $homestay->longitude }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-900 hover:text-gray-900">
                                    Open in Google Maps
                                </a>
                                <a href="https://waze.com/ul?ll={{ $homestay->latitude }},{{ $homestay->longitude }}&navigate=yes"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-900 hover:text-gray-900">
                                    Open in Waze
                                </a>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-950">What this place offers</h3>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @forelse ($amenities as $amenity)
                                <div class="rounded-2xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700">
                                    {{ $amenity }}
                                </div>
                            @empty
                                <span class="text-sm text-gray-500">Amenities will be added soon.</span>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="border-b border-gray-200 pb-10">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-green-700/70">AI Recommender</p>
                            <h2 class="mt-2 text-2xl font-semibold text-gray-950">Nearby picks from this homestay</h2>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600">
                                {{ $localRecommendations['summary'] ?? 'We are preparing nearby food and attraction suggestions for this stay.' }}
                            </p>
                        </div>
                        <div class="max-w-md rounded-full border border-gray-200 bg-[#faf8f3] px-4 py-2 text-sm text-gray-600">
                            Suggestions are based on the homestay location pin and nearby place data.
                        </div>
                    </div>

                    @if (!empty($localRecommendations['available']))
                        <div class="mt-6 grid gap-6 min-w-0">
                            <div class="min-w-0">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-green-700/70">Nearby tours</p>
                                        <h3 class="mt-1 text-lg font-semibold text-gray-950">Attractions and activities</h3>
                                    </div>
                                    <span class="rounded-full bg-[#f5f8f2] px-3 py-1 text-xs font-semibold text-green-700">
                                        Explore
                                    </span>
                                </div>

                                <div class="w-full overflow-hidden">
                                    <div data-rec-scroll="tours" class="recommendation-scroll w-full max-w-full overflow-x-auto overflow-y-hidden pb-4">
                                        <div class="flex w-max min-w-full gap-4 pr-2">
                                    @foreach ($tourRecommendations as $tour)
                                        <article class="w-[250px] shrink-0 overflow-hidden rounded-[22px] border border-gray-200 bg-white">
                                            <img src="{{ $tour['image'] ?? asset('assets/images/attractions/nature.jpg') }}"
                                                alt="{{ $tour['name'] }}"
                                                class="h-36 w-full object-cover"
                                                loading="lazy">
                                            <div class="p-4">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="line-clamp-2 text-base font-semibold text-gray-950">{{ $tour['name'] }}</p>
                                                        <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-green-700/70">{{ $tour['type'] }}</p>
                                                    </div>
                                                    <span class="rounded-full bg-[#faf8f3] px-3 py-1 text-xs font-medium text-gray-600">
                                                        {{ number_format($tour['distance'], 1) }} km
                                                    </span>
                                                </div>
                                                <p class="mt-3 line-clamp-2 text-sm leading-6 text-gray-600">{{ $tour['reason'] }}</p>
                                                @if (!empty($tour['address']))
                                                    <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ $tour['address'] }}</p>
                                                @endif
                                                <div class="mt-3 flex items-center gap-3 text-xs font-medium text-gray-500">
                                                    @if (!is_null($tour['rating']))
                                                        <span>{{ number_format($tour['rating'], 1) }}/5 rating</span>
                                                    @endif
                                                    @if (!empty($tour['maps_url']))
                                                        <a href="{{ $tour['maps_url'] }}" target="_blank" rel="noopener noreferrer" class="text-green-700 hover:text-green-800">
                                                            View route
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-green-700/70">Best food nearby</p>
                                        <h3 class="mt-1 text-lg font-semibold text-gray-950">Places to eat around the stay</h3>
                                    </div>
                                    <span class="rounded-full bg-[#fff6e4] px-3 py-1 text-xs font-semibold text-[#9a6a00]">
                                        Eat well
                                    </span>
                                </div>

                                <div class="w-full overflow-hidden">
                                    <div data-rec-scroll="food" class="recommendation-scroll w-full max-w-full overflow-x-auto overflow-y-hidden pb-4">
                                        <div class="flex w-max min-w-full gap-4 pr-2">
                                    @foreach ($foodRecommendations as $foodSpot)
                                        <article class="w-[250px] shrink-0 overflow-hidden rounded-[22px] border border-gray-200 bg-white">
                                            <img src="{{ $foodSpot['image'] ?? asset('assets/images/attractions/food.jpg') }}"
                                                alt="{{ $foodSpot['name'] }}"
                                                class="h-36 w-full object-cover"
                                                loading="lazy">
                                            <div class="p-4">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="line-clamp-2 text-base font-semibold text-gray-950">{{ $foodSpot['name'] }}</p>
                                                        <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#9a6a00]/80">{{ $foodSpot['type'] }}</p>
                                                    </div>
                                                    <span class="rounded-full bg-[#faf8f3] px-3 py-1 text-xs font-medium text-gray-600">
                                                        {{ number_format($foodSpot['distance'], 1) }} km
                                                    </span>
                                                </div>
                                                <p class="mt-3 line-clamp-2 text-sm leading-6 text-gray-600">{{ $foodSpot['reason'] }}</p>
                                                @if (!empty($foodSpot['address']))
                                                    <p class="mt-2 line-clamp-2 text-sm text-gray-500">{{ $foodSpot['address'] }}</p>
                                                @endif
                                                <div class="mt-3 flex items-center gap-3 text-xs font-medium text-gray-500">
                                                    @if (!is_null($foodSpot['rating']))
                                                        <span>{{ number_format($foodSpot['rating'], 1) }}/5 rating</span>
                                                    @endif
                                                    @if (!empty($foodSpot['maps_url']))
                                                        <a href="{{ $foodSpot['maps_url'] }}" target="_blank" rel="noopener noreferrer" class="text-green-700 hover:text-green-800">
                                                            View route
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-green-700/70">Public transport nearby</p>
                                        <h3 class="mt-1 text-lg font-semibold text-gray-950">Nearest public transport map</h3>
                                    </div>
                                    <span class="rounded-full bg-[#eef5ff] px-3 py-1 text-xs font-semibold text-[#2f5e9e]">
                                        Transit
                                    </span>
                                </div>

                                @if (!empty($transportInfo['available']))
                                    <div class="grid gap-4 lg:grid-cols-[minmax(0,1.2fr)_280px]">
                                        <div class="transport-map-shell overflow-hidden rounded-[22px] border border-gray-200 bg-white">
                                            <div id="booking-transport-map" class="h-[320px] w-full"></div>
                                        </div>

                                        <div class="space-y-3">
                                            @if (!empty($nearestTransport))
                                                <article class="rounded-[22px] border border-gray-200 bg-[#f7fafc] p-4">
                                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#2f5e9e]">Closest option</p>
                                                    <h4 class="mt-2 text-base font-semibold text-gray-950">{{ $nearestTransport['name'] }}</h4>
                                                    <p class="mt-1 text-sm text-gray-600">{{ $nearestTransport['type'] }}</p>
                                                    <p class="mt-3 text-sm text-gray-600">{{ number_format($nearestTransport['distance'], 1) }} km from the stay</p>
                                                    @if (!empty($nearestTransport['maps_url']))
                                                        <a href="{{ $nearestTransport['maps_url'] }}" target="_blank" rel="noopener noreferrer"
                                                            class="mt-3 inline-flex text-sm font-semibold text-green-700 hover:text-green-800">
                                                            Open route
                                                        </a>
                                                    @endif
                                                </article>
                                            @endif

                                            @if (!empty($transportInfo['nearest_train_station']))
                                                <article class="rounded-[22px] border border-gray-200 bg-[#faf5ff] p-4">
                                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#7c3aed]">Train station</p>
                                                    <h4 class="mt-2 text-base font-semibold text-gray-950">{{ $transportInfo['nearest_train_station']['name'] }}</h4>
                                                    <p class="mt-3 text-sm text-gray-600">{{ number_format($transportInfo['nearest_train_station']['distance'], 1) }} km away</p>
                                                </article>
                                            @endif

                                            @if (!empty($transportInfo['bus_stops']))
                                                <article class="rounded-[22px] border border-gray-200 bg-[#fff7f5] p-4">
                                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c2410c]">Bus stops</p>
                                                    <h4 class="mt-2 text-base font-semibold text-gray-950">{{ count($transportInfo['bus_stops']) }} nearby stop{{ count($transportInfo['bus_stops']) > 1 ? 's' : '' }}</h4>
                                                    <p class="mt-3 text-sm text-gray-600">Mapped below from the homestay location.</p>
                                                </article>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="rounded-[22px] border border-dashed border-gray-300 bg-[#fcfaf5] p-5 text-sm leading-7 text-gray-600">
                                        Nearby public transport data is not available for this stay yet.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="mt-6 rounded-[24px] border border-dashed border-gray-300 bg-[#fcfaf5] p-6 text-sm leading-7 text-gray-600">
                            {{ $localRecommendations['summary'] ?? 'Nearby recommendations are not available for this stay yet.' }}
                        </div>
                    @endif

                    @if ($blockedDates->isNotEmpty())
                        <div class="mt-6 rounded-[24px] border border-gray-200 bg-[#faf8f3] p-5">
                            <p class="text-sm font-semibold text-gray-950">Already booked dates</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($blockedDates as $range)
                                    <span class="inline-flex items-center rounded-full bg-white px-4 py-2 text-xs font-medium text-gray-600 ring-1 ring-gray-200">
                                        {{ \Carbon\Carbon::parse($range->check_in)->format('d M Y') }} to {{ \Carbon\Carbon::parse($range->check_out)->format('d M Y') }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            <aside id="reserve" class="xl:sticky xl:top-24 xl:self-start">
                <section class="rounded-[26px] border border-gray-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.08)]">
                    <div class="border-b border-gray-200 pb-5">
                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <p class="text-2xl font-semibold text-gray-950">RM {{ number_format($homestay->base_price, 2) }}</p>
                                <p class="mt-1 text-sm text-gray-500">per night</p>
                            </div>
                            <span class="rounded-full bg-[#f5f8f2] px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-green-700">
                                Book now
                            </span>
                        </div>
                    </div>

                    @php
                        $canBookDirectly = auth()->check() && in_array((int) auth()->user()->role, [2, 3], true);
                    @endphp

                    <form action="{{ $canBookDirectly ? route('user.homestays.book', $homestay->homestay_id) : '#' }}" method="POST" class="mt-5 space-y-4" data-booking-form>
                        @if ($canBookDirectly)
                            @csrf
                        @endif
                        <div class="rounded-[24px] border border-gray-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-green-700/70">Stay calendar</p>
                                    <p class="mt-1 text-sm text-gray-500">Choose your check-in first, then pick your check-out date.</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" data-calendar-prev
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 bg-white text-lg text-gray-700 transition hover:border-gray-900 hover:text-gray-900">
                                        &#8249;
                                    </button>
                                    <button type="button" data-calendar-next
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 bg-white text-lg text-gray-700 transition hover:border-gray-900 hover:text-gray-900">
                                        &#8250;
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                                <button type="button" data-check-in-display
                                    class="rounded-[20px] border border-gray-300 bg-white px-4 py-3 text-left transition hover:border-gray-900">
                                    <span class="block text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Check-in</span>
                                    <span class="mt-1 block text-sm font-semibold text-gray-950">Select date</span>
                                </button>
                                <button type="button" data-check-out-display
                                    class="rounded-[20px] border border-gray-300 bg-white px-4 py-3 text-left transition hover:border-gray-900">
                                    <span class="block text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Check-out</span>
                                    <span class="mt-1 block text-sm font-semibold text-gray-950">Select date</span>
                                </button>
                            </div>

                            <div class="mt-4 overflow-hidden rounded-[22px] border border-gray-200 bg-white">
                                <div class="border-b border-gray-200 px-4 py-3">
                                    <p data-calendar-month class="text-sm font-semibold text-gray-950"></p>
                                </div>
                                <div class="grid grid-cols-7 gap-px bg-gray-200 text-center text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">
                                    <div class="bg-white px-2 py-3">Sun</div>
                                    <div class="bg-white px-2 py-3">Mon</div>
                                    <div class="bg-white px-2 py-3">Tue</div>
                                    <div class="bg-white px-2 py-3">Wed</div>
                                    <div class="bg-white px-2 py-3">Thu</div>
                                    <div class="bg-white px-2 py-3">Fri</div>
                                    <div class="bg-white px-2 py-3">Sat</div>
                                </div>
                                <div data-calendar-grid class="grid grid-cols-7 gap-px bg-gray-200"></div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2 text-xs text-gray-500">
                                <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 ring-1 ring-gray-200">
                                    <span class="h-3 w-3 rounded-full bg-green-600"></span> Selected
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 ring-1 ring-gray-200">
                                    <span class="h-3 w-3 rounded-full bg-[#f3d08e]"></span> In stay range
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 ring-1 ring-gray-200">
                                    <span class="h-3 w-3 rounded-full bg-gray-300"></span> Unavailable
                                </span>
                            </div>

                            <input type="hidden" id="check_in" name="check_in" value="{{ old('check_in') }}" data-check-in>
                            <input type="hidden" id="check_out" name="check_out" value="{{ old('check_out') }}" data-check-out>
                        </div>

                        @error('check_in')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        @error('check_out')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div data-date-error class="hidden rounded-[20px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                        <div>
                            <label for="guest_count" class="mb-2 block text-sm font-semibold text-gray-700">Guests</label>
                            <input type="number" id="guest_count" name="guest_count" value="{{ old('guest_count', 1) }}"
                                min="1" max="{{ (int) $homestay->max_guest }}"
                                class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm text-gray-700 focus:border-gray-900 focus:outline-none focus:ring-0">
                            <p class="mt-2 text-xs text-gray-500">Maximum {{ (int) $homestay->max_guest }} guests for this stay.</p>
                            @error('guest_count')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($canBookDirectly)
                            <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-full bg-[#1f7a3f] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#176433]">
                                Continue to payment
                            </button>
                        @else
                            <a href="{{ route('login') }}"
                                class="inline-flex w-full items-center justify-center rounded-full bg-[#1f7a3f] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#176433]">
                                Sign in to book
                            </a>
                        @endif
                    </form>

                    <div class="mt-5 rounded-[22px] border border-gray-200 bg-[#faf8f3] p-4 text-sm text-gray-600">
                        <p class="font-semibold text-gray-950">How it works</p>
                        <p class="mt-2">1. Pick your stay dates.</p>
                        <p class="mt-1">2. Review the price summary on the payment page.</p>
                        <p class="mt-1">3. Complete your payment securely on ToyyibPay.</p>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-booking-form]');
        const checkInInput = document.querySelector('[data-check-in]');
        const checkOutInput = document.querySelector('[data-check-out]');
        const errorBox = document.querySelector('[data-date-error]');
        const calendarGrid = document.querySelector('[data-calendar-grid]');
        const calendarMonth = document.querySelector('[data-calendar-month]');
        const calendarPrev = document.querySelector('[data-calendar-prev]');
        const calendarNext = document.querySelector('[data-calendar-next]');
        const checkInDisplay = document.querySelector('[data-check-in-display]');
        const checkOutDisplay = document.querySelector('[data-check-out-display]');
        const blockedRanges = @json($blockedDates->map(fn ($range) => [
            'check_in' => $range->check_in,
            'check_out' => $range->check_out,
        ])->values());
        const todayString = @json(now()->toDateString());
        const normalizedBlockedRanges = blockedRanges.map((range) => ({
            check_in: String(range.check_in).split('T')[0].split(' ')[0],
            check_out: String(range.check_out).split('T')[0].split(' ')[0],
        }));
        const state = {
            monthCursor: null,
            selecting: checkInInput?.value ? (checkOutInput?.value ? 'check_in' : 'check_out') : 'check_in',
        };

        if (!form || !checkInInput || !checkOutInput) {
            return;
        }

        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const humanDate = (dateString) => {
            if (!dateString) {
                return 'Select date';
            }

            const date = new Date(`${dateString}T00:00:00`);

            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        };

        const startOfMonth = (dateString) => {
            const date = new Date(`${dateString}T00:00:00`);
            date.setDate(1);
            return date;
        };

        const toDateString = (date) => {
            const year = date.getFullYear();
            const month = `${date.getMonth() + 1}`.padStart(2, '0');
            const day = `${date.getDate()}`.padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const addMonths = (date, amount) => {
            const next = new Date(date.getFullYear(), date.getMonth() + amount, 1);
            return next;
        };

        const isPastDay = (dateString) => dateString < todayString;

        const dateIsWithinSelection = (dateString) => {
            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;

            return checkIn && checkOut && dateString > checkIn && dateString < checkOut;
        };

        const updateDateDisplays = () => {
            if (checkInDisplay) {
                checkInDisplay.querySelector('span:last-child').textContent = humanDate(checkInInput.value);
                checkInDisplay.classList.toggle('border-green-600', state.selecting === 'check_in');
            }

            if (checkOutDisplay) {
                checkOutDisplay.querySelector('span:last-child').textContent = humanDate(checkOutInput.value);
                checkOutDisplay.classList.toggle('border-green-600', state.selecting === 'check_out');
            }
        };

        const renderCalendar = () => {
            if (!calendarGrid || !calendarMonth || !state.monthCursor) {
                return;
            }

            calendarGrid.innerHTML = '';
            calendarMonth.textContent = `${monthNames[state.monthCursor.getMonth()]} ${state.monthCursor.getFullYear()}`;

            const year = state.monthCursor.getFullYear();
            const month = state.monthCursor.getMonth();
            const firstDay = new Date(year, month, 1);
            const startWeekday = firstDay.getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < startWeekday; i++) {
                const filler = document.createElement('div');
                filler.className = 'min-h-[52px] bg-[#fbf9f3]';
                calendarGrid.appendChild(filler);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const button = document.createElement('button');
                const dateString = toDateString(new Date(year, month, day));
                const blocked = isBlockedDay(dateString);
                const isPast = isPastDay(dateString);
                const isSelectedStart = checkInInput.value === dateString;
                const isSelectedEnd = checkOutInput.value === dateString;
                const isInRange = dateIsWithinSelection(dateString);
                const isToday = dateString === todayString;
                const disabledForCheckOut = state.selecting === 'check_out' && checkInInput.value && dateString <= checkInInput.value;
                const unavailable = isPast || blocked || disabledForCheckOut;

                button.type = 'button';
                button.dataset.date = dateString;
                button.className = 'min-h-[52px] px-2 py-3 text-sm font-medium transition';
                button.textContent = String(day);

                if (isSelectedStart || isSelectedEnd) {
                    button.classList.add('bg-green-600', 'text-white', 'shadow-[inset_0_0_0_1px_rgba(22,163,74,0.25)]');
                } else if (isInRange) {
                    button.classList.add('bg-[#f3d08e]', 'text-gray-900');
                } else if (unavailable) {
                    button.classList.add('bg-[#fbf9f3]', 'text-gray-300', 'line-through');
                } else {
                    button.classList.add('bg-[#fbf9f3]', 'text-gray-700', 'hover:bg-green-50', 'hover:text-green-700');
                }

                if (isToday && !isSelectedStart && !isSelectedEnd) {
                    button.classList.add('ring-1', 'ring-inset', 'ring-green-500');
                }

                if (unavailable) {
                    button.disabled = true;
                    button.title = blocked ? 'Unavailable date' : (disabledForCheckOut ? 'Check-out must be after check-in' : 'Past date');
                } else {
                    button.addEventListener('click', () => {
                        if (state.selecting === 'check_in') {
                            checkInInput.value = dateString;
                            checkOutInput.value = '';
                            state.selecting = 'check_out';
                        } else {
                            checkOutInput.value = dateString;
                            state.selecting = 'check_in';
                        }

                        syncCheckOutMin();
                        updateDateDisplays();
                        validateDates({ resetInvalid: true });
                        renderCalendar();
                    });
                }

                calendarGrid.appendChild(button);
            }

            updateDateDisplays();
        };

        const showError = (message) => {
            if (!errorBox) {
                return;
            }

            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
        };

        const hideError = () => {
            if (!errorBox) {
                return;
            }

            errorBox.textContent = '';
            errorBox.classList.add('hidden');
        };

        const nextDay = (dateString) => {
            const date = new Date(`${dateString}T00:00:00`);
            date.setDate(date.getDate() + 1);
            return date.toISOString().split('T')[0];
        };

        const overlapsBlockedDates = (checkIn, checkOut) => {
            return normalizedBlockedRanges.some((range) => checkIn < range.check_out && checkOut > range.check_in);
        };

        const isBlockedDay = (dateString) => {
            return normalizedBlockedRanges.some((range) => dateString >= range.check_in && dateString <= range.check_out);
        };

        const syncCheckOutMin = () => {
            if (checkInInput.value) {
                checkOutInput.min = nextDay(checkInInput.value);

                if (checkOutInput.value && checkOutInput.value <= checkInInput.value) {
                    checkOutInput.value = '';
                }
            }
        };

        const validateDates = ({ resetInvalid = false } = {}) => {
            hideError();

            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;

            if (checkIn && isBlockedDay(checkIn)) {
                showError('That check-in day is already booked. Please choose another date.');

                if (resetInvalid) {
                    checkInInput.value = '';
                    checkOutInput.value = '';
                }

                return false;
            }

            if (checkOut && isBlockedDay(checkOut)) {
                showError('That check-out day falls on an unavailable stay period. Please choose another date.');

                if (resetInvalid) {
                    checkOutInput.value = '';
                }

                return false;
            }

            if (checkIn && checkOut && overlapsBlockedDates(checkIn, checkOut)) {
                showError('Those dates overlap an existing booking, so they cannot be selected.');

                if (resetInvalid) {
                    checkOutInput.value = '';
                }

                return false;
            }

            return true;
        };

        checkInInput.addEventListener('change', () => {
            syncCheckOutMin();
            validateDates({ resetInvalid: true });
            updateDateDisplays();
            renderCalendar();
        });

        checkOutInput.addEventListener('change', () => {
            validateDates({ resetInvalid: true });
            updateDateDisplays();
            renderCalendar();
        });

        checkInDisplay?.addEventListener('click', () => {
            state.selecting = 'check_in';
            updateDateDisplays();
            renderCalendar();
        });

        checkOutDisplay?.addEventListener('click', () => {
            state.selecting = 'check_out';
            if (!checkInInput.value) {
                state.selecting = 'check_in';
                showError('Please choose a check-in date first.');
            } else {
                hideError();
            }
            updateDateDisplays();
            renderCalendar();
        });

        calendarPrev?.addEventListener('click', () => {
            state.monthCursor = addMonths(state.monthCursor, -1);
            renderCalendar();
        });

        calendarNext?.addEventListener('click', () => {
            state.monthCursor = addMonths(state.monthCursor, 1);
            renderCalendar();
        });

        form.addEventListener('submit', (event) => {
            syncCheckOutMin();

            if (!validateDates()) {
                event.preventDefault();
            }
        });

        state.monthCursor = startOfMonth(checkInInput.value || todayString);
        syncCheckOutMin();
        validateDates();
        updateDateDisplays();
        renderCalendar();

        const galleryTrack = document.querySelector('[data-gallery-track]');
        const galleryScroll = document.querySelector('[data-gallery-scroll]');
        const upButton = document.querySelector('[data-gallery-up]');
        const downButton = document.querySelector('[data-gallery-down]');

        if (galleryTrack && galleryScroll && upButton && downButton) {
            const slides = Array.from(galleryTrack.children);

            const updateGallery = () => {
                if (!slides.length) {
                    return;
                }

                const atTop = galleryScroll.scrollTop <= 4;
                const atBottom = galleryScroll.scrollTop + galleryScroll.clientHeight >= galleryScroll.scrollHeight - 4;
                upButton.disabled = atTop;
                downButton.disabled = atBottom;
                upButton.classList.toggle('opacity-40', atTop);
                downButton.classList.toggle('opacity-40', atBottom);
            };

            upButton.addEventListener('click', () => {
                galleryScroll.scrollBy({ top: -180, behavior: 'smooth' });
                window.setTimeout(updateGallery, 220);
            });

            downButton.addEventListener('click', () => {
                galleryScroll.scrollBy({ top: 180, behavior: 'smooth' });
                window.setTimeout(updateGallery, 220);
            });

            galleryScroll.addEventListener('scroll', updateGallery, { passive: true });
            window.addEventListener('resize', updateGallery);
            window.addEventListener('load', updateGallery);
            slides.forEach((slide) => {
                if (slide.complete) {
                    return;
                }

                slide.addEventListener('load', updateGallery, { once: true });
            });

            requestAnimationFrame(() => {
                updateGallery();
            });
        }

    });
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mapElement = document.getElementById('booking-transport-map');
        const transportMapPoints = @json($transportMapPoints);

        if (!mapElement || typeof L === 'undefined' || !Array.isArray(transportMapPoints) || !transportMapPoints.length) {
            return;
        }

        const defaultPoint = transportMapPoints[0];
        const map = L.map(mapElement, {
            scrollWheelZoom: false
        }).setView([defaultPoint.latitude, defaultPoint.longitude], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const iconColors = {
            'Homestay': '#166534',
            'Train station': '#7c3aed',
            'Bus stop': '#ea580c'
        };

        const bounds = [];

        transportMapPoints.forEach((point) => {
            if (typeof point.latitude !== 'number' || typeof point.longitude !== 'number') {
                return;
            }

            const marker = L.marker([point.latitude, point.longitude], {
                icon: L.divIcon({
                    className: 'booking-transport-marker',
                    html: `<div style="background:${iconColors[point.type] || '#334155'};width:34px;height:34px;border-radius:9999px;border:2px solid #fff;box-shadow:0 10px 24px rgba(15,23,42,.18);display:flex;align-items:center;justify-content:center;color:#fff;font:700 12px/1 system-ui,sans-serif;">${point.type === 'Homestay' ? 'H' : point.type === 'Train station' ? 'T' : 'B'}</div>`,
                    iconSize: [34, 34],
                    iconAnchor: [17, 17],
                    popupAnchor: [0, -16]
                })
            }).addTo(map);

            marker.bindPopup(
                `<div class="text-sm"><strong>${point.name}</strong><br>${point.type}${typeof point.distance === 'number' ? `<br>${point.distance.toFixed(1)} km away` : ''}</div>`
            );

            bounds.push([point.latitude, point.longitude]);
        });

        if (bounds.length > 1) {
            map.fitBounds(bounds, {
                padding: [32, 32]
            });
        }

        requestAnimationFrame(() => {
            map.invalidateSize();
        });
    });
</script>

@if ($hasPanorama ?? false)
<script>
    (() => {
        const viewer = document.getElementById('panorama-viewer');
        const canvas = document.getElementById('panorama-canvas');
        const loading = document.getElementById('panorama-loading');
        const stepText = document.getElementById('panorama-step');
        const nameText = document.getElementById('panorama-name');
        const panoramaUrl = @json($panoramaUrl);
        const panoramaName = @json($homestay->homestay_name . ' 360 view');

        if (!viewer || !canvas || !panoramaUrl) {
            return;
        }

        const context = canvas.getContext('2d', { alpha: false });
        const sourceCanvas = document.createElement('canvas');
        const sourceContext = sourceCanvas.getContext('2d', { willReadFrequently: true });
        const state = {
            yaw: 0,
            pitch: 0,
            dragging: false,
            startX: 0,
            startY: 0,
            startYaw: 0,
            startPitch: 0,
            renderFrame: null,
            nextRenderProfile: 'detail',
            settledRenderTimer: null,
            sourceWidth: 0,
            sourceHeight: 0,
            sourcePixels: null,
        };

        const renderProfiles = {
            interactive: { maxPixels: 680000, maxWidth: 1280, minWidth: 420, dprCap: 1.2, useBilinear: false },
            detail: { maxPixels: 2200000, maxWidth: 2200, minWidth: 640, dprCap: 2, useBilinear: true },
        };
        const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
        const normalizeDegrees = (value) => ((value % 360) + 360) % 360;
        const formatPitch = (value) => `${Math.round(value)}${String.fromCharCode(176)}`;
        const formatYaw = (value) => `${Math.round(normalizeDegrees(value))}${String.fromCharCode(176)}`;

        const updateLabels = () => {
            if (stepText) {
                stepText.textContent = `Yaw ${formatYaw(state.yaw)} / Pitch ${formatPitch(state.pitch)}`;
            }

            if (nameText) {
                nameText.textContent = panoramaName;
            }
        };

        const render = () => {
            state.renderFrame = null;

            if (!context || !state.sourcePixels || !state.sourceWidth || !state.sourceHeight) {
                return;
            }

            const profile = renderProfiles[state.nextRenderProfile] ?? renderProfiles.detail;
            const viewerWidth = Math.max(1, viewer.clientWidth);
            const viewerHeight = Math.max(1, viewer.clientHeight);
            const aspect = viewerWidth / viewerHeight;
            const deviceScale = Math.min(window.devicePixelRatio || 1, profile.dprCap);
            const preferredWidth = Math.round(viewerWidth * deviceScale);
            const widthByPixels = Math.round(Math.sqrt(profile.maxPixels * aspect));
            const renderWidth = Math.max(profile.minWidth, Math.min(profile.maxWidth, widthByPixels, preferredWidth));
            const renderHeight = Math.max(180, Math.round(renderWidth / aspect));

            if (canvas.width !== renderWidth || canvas.height !== renderHeight) {
                canvas.width = renderWidth;
                canvas.height = renderHeight;
            }

            const frame = context.createImageData(renderWidth, renderHeight);
            const output = frame.data;
            const source = state.sourcePixels;
            const sourceWidth = state.sourceWidth;
            const sourceHeight = state.sourceHeight;
            const horizontalFov = (95 * Math.PI) / 180;
            const verticalFov = 2 * Math.atan(Math.tan(horizontalFov / 2) / aspect);
            const tanHalfHorizontal = Math.tan(horizontalFov / 2);
            const tanHalfVertical = Math.tan(verticalFov / 2);
            const yaw = (state.yaw * Math.PI) / 180;
            const pitch = (state.pitch * Math.PI) / 180;
            const cosYaw = Math.cos(yaw);
            const sinYaw = Math.sin(yaw);
            const cosPitch = Math.cos(pitch);
            const sinPitch = Math.sin(pitch);
            const twoPi = Math.PI * 2;
            const srcYMax = sourceHeight - 1;

            for (let y = 0; y < renderHeight; y++) {
                const normalizedY = 1 - (((y + 0.5) / renderHeight) * 2);
                const cameraY = normalizedY * tanHalfVertical;

                for (let x = 0; x < renderWidth; x++) {
                    const normalizedX = (((x + 0.5) / renderWidth) * 2) - 1;
                    const cameraX = normalizedX * tanHalfHorizontal;
                    const cameraZ = 1;
                    const length = Math.hypot(cameraX, cameraY, cameraZ) || 1;
                    const directionX = cameraX / length;
                    const directionY = cameraY / length;
                    const directionZ = cameraZ / length;

                    const yawX = (directionX * cosYaw) + (directionZ * sinYaw);
                    const yawZ = (-directionX * sinYaw) + (directionZ * cosYaw);
                    const pitchedY = (directionY * cosPitch) - (yawZ * sinPitch);
                    const pitchedZ = (directionY * sinPitch) + (yawZ * cosPitch);
                    const longitude = Math.atan2(yawX, pitchedZ);
                    const latitude = Math.asin(clamp(pitchedY, -1, 1));
                    const sampleX = ((longitude / twoPi) + 0.5) * sourceWidth;
                    const sampleY = (0.5 - (latitude / Math.PI)) * sourceHeight;
                    const targetIndex = ((y * renderWidth) + x) * 4;
                    const sampleFloorY = clamp(Math.floor(sampleY), 0, srcYMax);

                    if (!profile.useBilinear) {
                        const sampleFloorX = Math.floor(sampleX);
                        const sourceX = ((sampleFloorX % sourceWidth) + sourceWidth) % sourceWidth;
                        const sourceIndex = ((sampleFloorY * sourceWidth) + sourceX) * 4;

                        output[targetIndex] = source[sourceIndex];
                        output[targetIndex + 1] = source[sourceIndex + 1];
                        output[targetIndex + 2] = source[sourceIndex + 2];
                        output[targetIndex + 3] = 255;
                        continue;
                    }

                    const sampleFloorX = Math.floor(sampleX);
                    const nextSampleY = Math.min(srcYMax, sampleFloorY + 1);
                    const sampleWeightX = sampleX - sampleFloorX;
                    const sampleWeightY = clamp(sampleY - sampleFloorY, 0, 1);
                    const x0 = ((sampleFloorX % sourceWidth) + sourceWidth) % sourceWidth;
                    const x1 = (x0 + 1) % sourceWidth;
                    const topLeftIndex = ((sampleFloorY * sourceWidth) + x0) * 4;
                    const topRightIndex = ((sampleFloorY * sourceWidth) + x1) * 4;
                    const bottomLeftIndex = ((nextSampleY * sourceWidth) + x0) * 4;
                    const bottomRightIndex = ((nextSampleY * sourceWidth) + x1) * 4;

                    for (let channel = 0; channel < 3; channel++) {
                        const topBlend = source[topLeftIndex + channel]
                            + ((source[topRightIndex + channel] - source[topLeftIndex + channel]) * sampleWeightX);
                        const bottomBlend = source[bottomLeftIndex + channel]
                            + ((source[bottomRightIndex + channel] - source[bottomLeftIndex + channel]) * sampleWeightX);

                        output[targetIndex + channel] = Math.round(
                            topBlend + ((bottomBlend - topBlend) * sampleWeightY)
                        );
                    }

                    output[targetIndex + 3] = 255;
                }
            }

            context.putImageData(frame, 0, 0);
            updateLabels();
        };

        const scheduleRender = (profile = 'detail') => {
            state.nextRenderProfile = profile;

            if (state.renderFrame !== null) {
                cancelAnimationFrame(state.renderFrame);
            }

            state.renderFrame = requestAnimationFrame(render);
        };

        const scheduleSettledRender = (delay = 120) => {
            if (state.settledRenderTimer !== null) {
                window.clearTimeout(state.settledRenderTimer);
            }

            state.settledRenderTimer = window.setTimeout(() => {
                scheduleRender('detail');
            }, delay);
        };

        const setView = ({ yaw = state.yaw, pitch = state.pitch } = {}, profile = 'interactive') => {
            state.yaw = yaw;
            state.pitch = clamp(pitch, -85, 85);
            scheduleRender(profile);
            scheduleSettledRender();
        };

        const beginDrag = (x, y) => {
            state.dragging = true;
            state.startX = x;
            state.startY = y;
            state.startYaw = state.yaw;
            state.startPitch = state.pitch;
            viewer.classList.add('cursor-grabbing');
        };

        const updateDrag = (x, y) => {
            if (!state.dragging) {
                return;
            }

            const deltaX = x - state.startX;
            const deltaY = y - state.startY;
            setView({
                yaw: state.startYaw + (deltaX * 0.12),
                pitch: state.startPitch - (deltaY * 0.12),
            }, 'interactive');
        };

        const endDrag = () => {
            state.dragging = false;
            viewer.classList.remove('cursor-grabbing');
            scheduleSettledRender(50);
        };

        viewer.classList.add('cursor-grab');
        viewer.addEventListener('pointerdown', (event) => {
            beginDrag(event.clientX, event.clientY);
            viewer.setPointerCapture(event.pointerId);
        });
        viewer.addEventListener('pointermove', (event) => updateDrag(event.clientX, event.clientY));
        viewer.addEventListener('pointerup', endDrag);
        viewer.addEventListener('pointercancel', endDrag);
        viewer.addEventListener('pointerleave', endDrag);
        viewer.addEventListener('wheel', (event) => {
            event.preventDefault();
            setView({
                yaw: state.yaw + (event.deltaX * 0.08),
                pitch: state.pitch + (event.deltaY * 0.05),
            }, 'interactive');
        }, { passive: false });

        window.addEventListener('keydown', (event) => {
            if (!viewer.matches(':hover') && document.activeElement && !viewer.contains(document.activeElement)) {
                return;
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                setView({ yaw: state.yaw - 8 }, 'interactive');
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                setView({ yaw: state.yaw + 8 }, 'interactive');
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setView({ pitch: state.pitch + 6 }, 'interactive');
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                setView({ pitch: state.pitch - 6 }, 'interactive');
            }
        });

        const image = new Image();
        image.decoding = 'async';
        image.onload = () => {
            state.sourceWidth = image.naturalWidth || image.width;
            state.sourceHeight = image.naturalHeight || image.height;
            sourceCanvas.width = state.sourceWidth;
            sourceCanvas.height = state.sourceHeight;
            sourceContext.drawImage(image, 0, 0);
            state.sourcePixels = sourceContext.getImageData(0, 0, state.sourceWidth, state.sourceHeight).data;

            if (loading) {
                loading.classList.add('hidden');
            }

            updateLabels();
            scheduleRender('detail');
        };

        image.onerror = () => {
            if (loading) {
                loading.textContent = 'Unable to load the 360 image right now.';
            }
        };

        image.src = panoramaUrl;
        updateLabels();
    })();
</script>
@endif
@endpush
