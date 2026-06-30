@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .map-focus-card {
            cursor: pointer;
            transition: background-color 180ms ease, border-color 180ms ease, box-shadow 180ms ease,
                transform 180ms ease, color 180ms ease;
        }

        .map-focus-card:hover {
            background-color: #d8e8db;
            border-color: #7da58b;
            box-shadow: 0 18px 36px rgba(20, 67, 44, 0.14);
            transform: translateY(-2px);
        }

        .map-focus-card-active {
            background-color: #166534 !important;
            border-color: #166534 !important;
            box-shadow: 0 20px 40px rgba(22, 101, 52, 0.28) !important;
        }

        .map-focus-card-active h2,
        .map-focus-card-active h3,
        .map-focus-card-active p,
        .map-focus-card-active span,
        .map-focus-card-active a {
            color: #fff !important;
        }

        .panel-tab {
            border: 1px solid rgba(226, 232, 220, 0.16);
            background: rgba(255, 255, 255, 0.08);
            color: #dce8dd;
            transition: all 180ms ease;
        }

        .panel-tab:hover {
            background: rgba(255, 255, 255, 0.14);
        }

        .panel-tab-active {
            background: #f4e6be !important;
            border-color: #f4e6be !important;
            color: #163826 !important;
            box-shadow: 0 12px 24px rgba(14, 51, 32, 0.2);
        }

        .stack-scroll {
            scrollbar-width: thin;
            scrollbar-color: #c8bea5 transparent;
        }

        .stack-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .stack-scroll::-webkit-scrollbar-thumb {
            background: #c8bea5;
            border-radius: 9999px;
        }

        .soft-stat {
            background: linear-gradient(180deg, rgba(244, 230, 190, 0.18) 0%, rgba(244, 230, 190, 0.08) 100%);
            border: 1px solid rgba(244, 230, 190, 0.2);
            border-radius: 28px;
            backdrop-filter: blur(8px);
        }

        .explorer-shell {
            background:
                radial-gradient(circle at top right, rgba(244, 230, 190, 0.2), transparent 28%),
                radial-gradient(circle at bottom left, rgba(47, 102, 70, 0.22), transparent 32%),
                linear-gradient(145deg, #163c2a 0%, #0f2e20 54%, #174732 100%);
            border: 1px solid rgba(196, 225, 201, 0.12);
            box-shadow: 0 30px 70px rgba(10, 35, 24, 0.18);
        }

        .explorer-copy {
            color: #edf5ee;
        }

        .explorer-muted {
            color: #bed2c2;
        }

        .explorer-pill {
            border-radius: 9999px;
            border: 1px solid rgba(244, 230, 190, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: #edf5ee;
            padding: 0.7rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
        }

        .explorer-panel {
            background: linear-gradient(180deg, #fffdf8 0%, #f6f1e4 100%);
            box-shadow: 0 24px 54px rgba(31, 47, 35, 0.08);
        }
    </style>

    <main class="bg-[#f6f5ee]">
        <section class="relative">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 h-[260px] sm:h-[320px] lg:h-[420px]">
                <img src="assets/images/attractions/kl.jpg" class="object-cover w-full h-full" alt="Kuala Lumpur">
                <img src="assets/images/attractions/batu-caves.jpg" class="object-cover w-full h-full" alt="Batu Caves">
                <img src="assets/images/attractions/food.jpg" class="object-cover w-full h-full hidden sm:block"
                    alt="Malaysian food">
                <img src="assets/images/attractions/food2.jpg" class="object-cover w-full h-full hidden lg:block"
                    alt="Street food">
                <img src="assets/images/attractions/nature.jpg" class="object-cover w-full h-full hidden lg:block"
                    alt="Nature attraction">
            </div>

            <div class="absolute inset-0 bg-black/45 flex flex-col items-center justify-center px-4">
                <p class="text-xs sm:text-sm uppercase tracking-[0.35em] text-white/80 text-center">
                    Explore around {{ $city ?? 'Malaysia' }}
                </p>
                <h1 class="mt-3 text-2xl sm:text-4xl lg:text-5xl font-bold text-white text-center max-w-3xl">
                    Attractions, transport access, and nearby locations in one place
                </h1>
            </div>
        </section>

     

        <section class="max-w-[1400px] mx-auto px-3 py-4 sm:px-4 pb-4">
            <h2 class="text-2xl font-semibold text-gray-900">
                Recommended Tours near {{ $city ?? 'You' }}
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Browse nearby tours and use the map section to compare them with transport access.
            </p>

            <div class="mt-6 flex gap-6 overflow-x-auto pb-4 scroll-smooth snap-x snap-mandatory scrollbar-hide">
                @forelse ($tours as $tour)
                    <div
                        class="map-focus-card min-w-[280px] max-w-[280px] bg-white rounded-xl shadow hover:shadow-lg transition snap-start flex-shrink-0 overflow-hidden border border-[#ebe7d7]"
                        data-map-focus="true"
                        data-lat="{{ $tour['latitude'] }}"
                        data-lon="{{ $tour['longitude'] }}"
                        data-type="Attraction">
                        <div class="h-40 bg-gray-200">
                            <img src="{{ $tour['pictures'][0] ?? asset('assets/images/attractions/kl.jpg') }}"
                                data-fallback-src="{{ asset('assets/images/attractions/kl.jpg') }}"
                                class="tour-image w-full h-full object-cover" loading="lazy" alt="{{ $tour['name'] }}">
                        </div>

                        <div class="p-4 flex flex-col h-[210px]">
                            <h3 class="font-semibold text-sm line-clamp-2 text-gray-900">
                                {{ $tour['name'] }}
                            </h3>

                            <p class="text-xs text-gray-500 mt-1">
                                {{ $tour['categories_display'] ?? 'Tour experience' }}
                            </p>

                            @if (!empty($tour['address']))
                                <p class="mt-3 text-sm text-gray-600 line-clamp-2">
                                    {{ $tour['address'] }}
                                </p>
                            @endif

                            <div class="mt-3 flex items-center gap-3 text-xs text-gray-500">
                                @if (!is_null($tour['distance'] ?? null))
                                    <span>{{ number_format($tour['distance'], 1) }} km away</span>
                                @endif
                                @if (!is_null($tour['rating'] ?? null))
                                    <span>{{ number_format($tour['rating'], 1) }}/5 rating</span>
                                @endif
                            </div>
                            @if (!empty($tour['tour_key']))
                                <a href="{{ route('attractions-and-tours.show', $tour['tour_key']) }}"
                                    class="mt-auto w-full border border-green-600 text-green-600 rounded-full py-1.5 text-sm hover:bg-green-900 hover:text-white transition text-center">
                                    View Tour
                                </a>
                            @else
                                <span
                                    class="mt-auto w-full border border-gray-300 text-gray-400 rounded-full py-1.5 text-sm text-center cursor-not-allowed">
                                    View Tour
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="w-full bg-white rounded-2xl border border-[#ebe7d7] p-6 text-sm text-gray-600">
                        No nearby tours are available right now.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="max-w-[1400px] mx-auto px-3 sm:px-4 py-10">
            <div class="grid gap-8 xl:grid-cols-[minmax(0,1.2fr)_380px] items-start">
                <div class="explorer-shell rounded-[34px] overflow-hidden">
                    <div class="px-4 sm:px-5 lg:px-6 pt-6 pb-5 border-b border-white/10">
                        <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.34em] explorer-muted">Interactive explorer</p>
                                <h2 class="mt-3 text-3xl leading-tight font-semibold explorer-copy max-w-xl">
                                    Map, tours, and transport aligned
                                </h2>
                                <p class="mt-3 text-base explorer-muted max-w-2xl leading-7">
                                    Click any tour or transport card to focus it on the map, inspect the area, then click again to zoom back out.
                                </p>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 xl:min-w-[390px]">
                                <div class="soft-stat min-w-0 px-3 py-4 min-h-[104px] flex flex-col justify-between">
                                    <p class="text-xs uppercase tracking-[0.24em] text-[#f4e6be]">Tours</p>
                                    <p class="mt-2 text-3xl font-semibold explorer-copy">{{ count($tours) }}</p>
                                </div>
                                <div class="soft-stat min-w-0 px-3 py-4 min-h-[104px] flex flex-col justify-between">
                                    <p class="text-xs uppercase tracking-[0.24em] text-[#f4e6be]">Bus stops</p>
                                    <p class="mt-2 text-3xl font-semibold explorer-copy">{{ count($transportInfo['bus_stops'] ?? []) }}</p>
                                </div>
                                <div class="soft-stat min-w-0 px-3 py-4 min-h-[104px] flex flex-col justify-between">
                                    <p class="text-xs uppercase tracking-[0.24em] text-[#f4e6be]">Rail</p>
                                    <p class="mt-2 text-3xl font-semibold explorer-copy">
                                        {{ !empty($transportInfo['nearest_train_station']) ? '1' : '0' }}
                                    </p>
                                </div>
                                <div class="soft-stat min-w-0 px-3 py-4 min-h-[104px] flex flex-col justify-between">
                                    <p class="text-xs uppercase tracking-[0.24em] text-[#f4e6be]">Area</p>
                                    <p class="mt-2 text-sm font-semibold leading-5 break-words explorer-copy">
                                        {{ $city ?? 'Nearby' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3 text-sm">
                            <span class="explorer-pill">
                                <i class="fas fa-camera text-[#d97706]"></i> Attractions
                            </span>
                            <span class="explorer-pill">
                                <i class="fas fa-train text-[#7c3aed]"></i> Train stations
                            </span>
                            <span class="explorer-pill">
                                <i class="fas fa-bus text-[#dc2626]"></i> Bus stops
                            </span>
                        </div>
                    </div>

                    <div id="attours-map" class="h-[560px] w-full"></div>
                </div>

                <aside class="explorer-panel rounded-[34px] p-4 sm:p-5 lg:p-6 xl:sticky xl:top-24">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-gray-500">Explore list</p>
                            <h2 class="mt-2 text-2xl font-semibold text-[#173824]">Places to focus</h2>
                        </div>
                        <div class="inline-flex rounded-full bg-[#17412d] p-1.5 border border-[#27583e] shadow-inner">
                            <button type="button"
                                class="panel-tab panel-tab-active rounded-full px-4 py-2 text-sm font-medium"
                                data-panel-tab="tours">
                                Tours
                            </button>
                            <button type="button"
                                class="panel-tab rounded-full px-4 py-2 text-sm font-medium"
                                data-panel-tab="transport">
                                Transport
                            </button>
                        </div>
                    </div>

                    <p class="mt-3 text-sm text-gray-600 leading-6">
                        Hover to preview, click to zoom the map, and click the same card again to reset the view.
                    </p>

                    <div class="mt-5">
                        <div data-panel="tours" class="space-y-3 max-h-[560px] overflow-y-auto pr-1 stack-scroll">
                            @forelse ($tours as $tour)
                                <div class="map-focus-card rounded-2xl bg-[#fbf8ef] px-4 py-4 border border-transparent"
                                    data-map-focus="true"
                                    data-lat="{{ $tour['latitude'] }}"
                                    data-lon="{{ $tour['longitude'] }}"
                                    data-type="Attraction">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3 class="font-semibold text-gray-900">{{ $tour['name'] }}</h3>
                                            <p class="mt-1 text-sm text-gray-600">{{ $tour['categories_display'] ?? 'Tour experience' }}</p>
                                        </div>
                                        @if (!is_null($tour['distance'] ?? null))
                                            <span class="text-sm text-gray-500 whitespace-nowrap">
                                                {{ number_format($tour['distance'], 1) }} km
                                            </span>
                                        @endif
                                    </div>

                                    @if (!empty($tour['address']))
                                        <p class="mt-3 text-sm text-gray-600 line-clamp-2">
                                            {{ $tour['address'] }}
                                        </p>
                                    @endif

                                    <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-500">
                                        @if (!is_null($tour['rating'] ?? null))
                                            <span>{{ number_format($tour['rating'], 1) }}/5 rating</span>
                                        @endif
                                        @if (!empty($tour['maps_url']))
                                            <a href="{{ $tour['maps_url'] }}" target="_blank" rel="noopener noreferrer"
                                                class="font-medium text-green-700 hover:text-green-800">
                                                Open map
                                            </a>
                                        @endif
                                        @if (!empty($tour['tour_key']))
                                            <a href="{{ route('attractions-and-tours.show', $tour['tour_key']) }}"
                                                class="font-medium text-green-700 hover:text-green-800">
                                                View detail
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-600">No recommended tours were returned for this area.</p>
                            @endforelse
                        </div>

                        <div data-panel="transport" class="hidden space-y-3 max-h-[560px] overflow-y-auto pr-1 stack-scroll">
                            @if (!empty($transportInfo['nearest_train_station']))
                                <div class="map-focus-card rounded-2xl border border-[#ebe7d7] bg-[#f8f3ff] px-4 py-4"
                                    data-map-focus="true"
                                    data-lat="{{ $transportInfo['nearest_train_station']['latitude'] }}"
                                    data-lon="{{ $transportInfo['nearest_train_station']['longitude'] }}"
                                    data-type="Train station">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Train station</p>
                                            <p class="mt-1 font-semibold text-gray-900">{{ $transportInfo['nearest_train_station']['name'] }}</p>
                                        </div>
                                        <i class="fas fa-train text-lg text-[#7c3aed]"></i>
                                    </div>
                                    @if (!empty($transportInfo['nearest_train_station']['address']))
                                        <p class="mt-3 text-sm text-gray-600">
                                            {{ $transportInfo['nearest_train_station']['address'] }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            @forelse ($transportInfo['bus_stops'] ?? [] as $busStop)
                                <div class="map-focus-card rounded-2xl border border-[#ebe7d7] bg-[#fff4f1] px-4 py-4"
                                    data-map-focus="true"
                                    data-lat="{{ $busStop['latitude'] }}"
                                    data-lon="{{ $busStop['longitude'] }}"
                                    data-type="Bus stop">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Bus stop</p>
                                            <p class="mt-1 font-semibold text-gray-900">{{ $busStop['name'] }}</p>
                                        </div>
                                        <i class="fas fa-bus text-lg text-[#dc2626]"></i>
                                    </div>
                                    @if (!empty($busStop['address']))
                                        <p class="mt-3 text-sm text-gray-600">
                                            {{ $busStop['address'] }}
                                        </p>
                                    @endif
                                    <div class="mt-3 flex items-center justify-between gap-3 text-xs text-gray-500">
                                        <span>{{ number_format($busStop['distance'], 1) }} km away</span>
                                        @if (!empty($busStop['maps_url']))
                                            <a href="{{ $busStop['maps_url'] }}" target="_blank" rel="noopener noreferrer"
                                                class="font-medium text-green-700 hover:text-green-800">
                                                Open map
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                @if (empty($transportInfo['nearest_train_station']))
                                    <p class="text-gray-600">No nearby transport data is available from Google Places for this search.</p>
                                @endif
                            @endforelse
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.tour-image').forEach((img) => {
                img.addEventListener('error', function handleImageError() {
                    const fallbackSrc = img.dataset.fallbackSrc;

                    if (fallbackSrc && img.src !== fallbackSrc) {
                        img.src = fallbackSrc;
                    }
                }, {
                    once: true
                });
            });

            const mapPoints = @json($mapPoints ?? []);

            if (!Array.isArray(mapPoints) || !mapPoints.length) {
                return;
            }

            const mapElement = document.getElementById('attours-map');

            if (!mapElement || typeof L === 'undefined') {
                return;
            }

            const defaultPoint = mapPoints[0];
            const map = L.map(mapElement, {
                scrollWheelZoom: false
            }).setView([defaultPoint.latitude, defaultPoint.longitude], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const markerIcon = (pointType) => {
                const variants = {
                    'Your area': {
                        bg: '#1f5c3f',
                        icon: 'fa-location-dot'
                    },
                    'Attraction': {
                        bg: '#d97706',
                        icon: 'fa-camera'
                    },
                    'Train station': {
                        bg: '#7c3aed',
                        icon: 'fa-train'
                    },
                    'Bus stop': {
                        bg: '#dc2626',
                        icon: 'fa-bus'
                    }
                };

                const variant = variants[pointType] || {
                    bg: '#374151',
                    icon: 'fa-location-dot'
                };

                return L.divIcon({
                    className: 'custom-map-marker',
                    html: `<div style="background:${variant.bg};width:34px;height:34px;border-radius:9999px;display:flex;align-items:center;justify-content:center;color:white;border:2px solid white;box-shadow:0 10px 24px rgba(15,23,42,.18);"><i class="fas ${variant.icon}"></i></div>`,
                    iconSize: [34, 34],
                    iconAnchor: [17, 17],
                    popupAnchor: [0, -16]
                });
            };

            const bounds = [];
            const markers = new Map();
            const focusCards = Array.from(document.querySelectorAll('[data-map-focus="true"]'));
            const panelTabs = Array.from(document.querySelectorAll('[data-panel-tab]'));
            const panels = Array.from(document.querySelectorAll('[data-panel]'));
            let activeCard = null;
            let suppressPopupReset = false;

            const setActiveCard = (selectedCard) => {
                activeCard = selectedCard;
                focusCards.forEach((card) => {
                    card.classList.toggle('map-focus-card-active', card === selectedCard);
                });
            };

            const resetMapView = () => {
                if (bounds.length > 1) {
                    map.fitBounds(bounds, {
                        padding: [30, 30]
                    });
                } else if (bounds.length === 1) {
                    map.setView(bounds[0], 12);
                }

                map.closePopup();
                setActiveCard(null);
            };

            const setPanel = (panelName) => {
                panelTabs.forEach((tab) => {
                    tab.classList.toggle('panel-tab-active', tab.dataset.panelTab === panelName);
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.panel !== panelName);
                });
            };

            mapPoints.forEach((point) => {
                if (typeof point.latitude !== 'number' || typeof point.longitude !== 'number') {
                    return;
                }

                const marker = L.marker([point.latitude, point.longitude], {
                    icon: markerIcon(point.type)
                }).addTo(map);

                marker.bindPopup(
                    `<div class="text-sm"><strong>${point.name}</strong><br>${point.type}</div>`
                );

                const markerKey = `${point.type}|${Number(point.latitude).toFixed(5)}|${Number(point.longitude).toFixed(5)}`;
                markers.set(markerKey, marker);
                bounds.push([point.latitude, point.longitude]);
            });

            if (bounds.length > 1) {
                map.fitBounds(bounds, {
                    padding: [30, 30]
                });
            }

            focusCards.forEach((card) => {
                card.addEventListener('click', () => {
                    const lat = Number(card.dataset.lat);
                    const lon = Number(card.dataset.lon);
                    const type = card.dataset.type || '';
                    const markerKey = `${type}|${lat.toFixed(5)}|${lon.toFixed(5)}`;
                    const marker = markers.get(markerKey);

                    if (!Number.isFinite(lat) || !Number.isFinite(lon) || !marker) {
                        return;
                    }

                    if (activeCard === card) {
                        resetMapView();
                        return;
                    }

                    suppressPopupReset = true;
                    map.flyTo([lat, lon], 16, {
                        duration: 0.8
                    });
                    marker.openPopup();
                    setActiveCard(card);
                    mapElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    window.setTimeout(() => {
                        suppressPopupReset = false;
                    }, 250);
                });
            });

            panelTabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    setPanel(tab.dataset.panelTab);
                });
            });

            map.on('popupclose', () => {
                if (suppressPopupReset) {
                    return;
                }

                if (activeCard) {
                    resetMapView();
                }
            });
        });
    </script>
@endsection
