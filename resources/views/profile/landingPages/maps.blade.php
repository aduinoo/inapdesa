@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        #homestay-map {
            min-height: 620px;
        }

        .homestay-marker {
            align-items: center;
            background: linear-gradient(135deg, #14532d, #166534);
            border: 3px solid #ecfdf5;
            border-radius: 9999px;
            box-shadow: 0 18px 35px rgba(6, 78, 59, 0.28);
            color: #f0fdf4;
            display: flex;
            height: 42px;
            justify-content: center;
            width: 42px;
        }

        .homestay-map-card {
            transition: transform 0.18s ease, background-color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .homestay-map-card:hover {
            background: #d8e8db;
            border-color: #7da58b;
            box-shadow: 0 18px 36px rgba(20, 67, 44, 0.14);
            transform: translateY(-2px);
        }

        .homestay-map-card.is-active {
            background: linear-gradient(135deg, #14532d, #166534);
            border-color: #14532d;
            box-shadow: 0 18px 35px rgba(20, 83, 45, 0.22);
            color: #f0fdf4;
        }

        .homestay-map-card.is-active .homestay-card-muted,
        .homestay-map-card.is-active .homestay-card-price,
        .homestay-map-card.is-active .homestay-card-status {
            color: rgba(240, 253, 244, 0.86);
        }

        .homestay-map-card.is-active .homestay-card-pill {
            background: rgba(240, 253, 244, 0.14);
            color: rgba(240, 253, 244, 0.92);
        }

        .homestay-map-card.is-unavailable {
            opacity: 0.72;
        }
    </style>

    <section class="bg-[#f8f5ec] py-10 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="rounded-[2rem] bg-gradient-to-br from-green-950 via-green-900 to-green-800 p-6 text-white shadow-[0_30px_80px_rgba(20,83,45,0.22)] sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.45em] text-green-100/70">Homestay map</p>
                        <h1 class="mt-3 text-3xl font-semibold leading-tight sm:text-4xl">
                            Explore every active homestay in one place
                        </h1>
                        <p class="mt-4 max-w-xl text-sm leading-7 text-green-50/80 sm:text-base">
                            Browse all available stays, click a home card, and the map will focus on that listing with a home icon marker.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div class="rounded-[1.75rem] border border-white/15 bg-white/10 px-5 py-4 backdrop-blur">
                            <p class="text-[0.7rem] uppercase tracking-[0.35em] text-green-100/65">Active homes</p>
                            <p class="mt-3 text-3xl font-semibold">{{ count($mapHomestays) }}</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-white/15 bg-white/10 px-5 py-4 backdrop-blur">
                            <p class="text-[0.7rem] uppercase tracking-[0.35em] text-green-100/65">Marker type</p>
                            <p class="mt-3 text-lg font-semibold">Home icon</p>
                        </div>
                        <div class="rounded-[1.75rem] border border-white/15 bg-white/10 px-5 py-4 backdrop-blur col-span-2 sm:col-span-1">
                            <p class="text-[0.7rem] uppercase tracking-[0.35em] text-green-100/65">Coverage</p>
                            <p class="mt-3 text-lg font-semibold">All listings</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_380px]">
                <div class="overflow-hidden rounded-[2rem] border border-[#d8d2be] bg-white shadow-[0_30px_60px_rgba(15,23,42,0.08)]">
                    <div class="border-b border-[#ece6d6] px-5 py-5 sm:px-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-2xl font-semibold text-gray-900">Homestay location map</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    The map geocodes each saved homestay address and drops a home marker for it.
                                </p>
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-[#f3f8f1] px-4 py-2 text-sm font-medium text-green-800">
                                <i class="fa-solid fa-house text-green-700"></i>
                                Home markers
                            </div>
                        </div>
                    </div>

                    <div id="homestay-map" class="w-full bg-[#eef5ef]"></div>
                </div>

                <aside class="overflow-hidden rounded-[2rem] border border-[#d8d2be] bg-white shadow-[0_30px_60px_rgba(15,23,42,0.08)]">
                    <div class="border-b border-[#ece6d6] px-5 py-5 sm:px-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-green-700/70">Stay list</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">All active homestays</h2>
                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            Select a card to fly to that homestay on the map and open its details.
                        </p>
                    </div>

                    <div id="homestay-card-list" class="max-h-[620px] space-y-4 overflow-y-auto px-4 py-4 sm:px-5">
                        @forelse ($mapHomestays as $homestay)
                            <button type="button"
                                class="homestay-map-card block w-full rounded-[1.5rem] border border-[#ebe6d8] bg-[#fcfbf7] p-3 text-left shadow-sm"
                                data-homestay-card
                                data-homestay-id="{{ $homestay['id'] }}">
                                <div class="flex gap-4">
                                    <img src="{{ $homestay['image'] }}" alt="{{ $homestay['name'] }}"
                                        class="h-24 w-24 rounded-[1.25rem] object-cover">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <h3 class="truncate text-base font-semibold text-gray-900">
                                                    {{ $homestay['name'] }}
                                                </h3>
                                                <p class="homestay-card-muted mt-1 text-sm text-gray-500">
                                                    {{ $homestay['city'] ?: 'Malaysia' }}{{ $homestay['state'] ? ', ' . $homestay['state'] : '' }}
                                                </p>
                                            </div>
                                            <span class="homestay-card-price whitespace-nowrap text-sm font-semibold text-green-700">
                                                RM {{ number_format((float) $homestay['price'], 2) }}
                                            </span>
                                        </div>

                                        <p class="homestay-card-muted mt-3 line-clamp-2 text-sm leading-6 text-gray-600">
                                            {{ $homestay['address'] }}
                                        </p>

                                        <div class="mt-3 flex items-center justify-between gap-3">
                                            <span class="homestay-card-status homestay-card-pill inline-flex items-center gap-2 rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                                                <i class="fa-solid fa-user-group text-[0.7rem]"></i>
                                                Up to {{ (int) $homestay['max_guest'] }} guests
                                            </span>
                                            <span class="homestay-card-status text-xs font-medium text-gray-400"
                                                data-homestay-status>
                                                Locating on map...
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        @empty
                            <div class="rounded-[1.5rem] border border-dashed border-[#d8d2be] bg-[#fcfbf7] p-6 text-center text-sm text-gray-600">
                                No active homestays are available yet.
                            </div>
                        @endforelse
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const homestays = @json($mapHomestays);
            const mapElement = document.getElementById('homestay-map');
            const cards = Array.from(document.querySelectorAll('[data-homestay-card]'));
            const defaultCenter = [3.0738, 101.5183];
            const defaultZoom = 9;

            if (!mapElement) {
                return;
            }

            const map = L.map(mapElement, {
                scrollWheelZoom: false
            }).setView(defaultCenter, defaultZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            const homeIcon = L.divIcon({
                className: '',
                html: '<div class="homestay-marker"><i class="fa-solid fa-house"></i></div>',
                iconSize: [42, 42],
                iconAnchor: [21, 21],
                popupAnchor: [0, -18],
            });

            const markers = new Map();
            let activeHomestayId = null;
            let suppressPopupReset = false;

            const setActiveCard = (homestayId) => {
                activeHomestayId = homestayId;

                cards.forEach((card) => {
                    card.classList.toggle('is-active', Number(card.dataset.homestayId) === homestayId);
                });
            };

            const resetMapView = () => {
                suppressPopupReset = true;
                setActiveCard(null);
                map.closePopup();
                map.flyTo(defaultCenter, defaultZoom, {
                    duration: 0.9,
                });

                window.setTimeout(() => {
                    suppressPopupReset = false;
                }, 250);
            };

            const geocodeHomestay = async (homestay) => {
                if (Number.isFinite(Number(homestay.latitude)) && Number.isFinite(Number(homestay.longitude))) {
                    return {
                        latitude: Number(homestay.latitude),
                        longitude: Number(homestay.longitude),
                    };
                }

                const cacheKey = `homestay-map:${homestay.id}:${homestay.address}`;
                const cached = window.sessionStorage.getItem(cacheKey);

                if (cached) {
                    try {
                        return JSON.parse(cached);
                    } catch (error) {
                        window.sessionStorage.removeItem(cacheKey);
                    }
                }

                const candidates = [
                    homestay.address,
                    `${homestay.city ?? ''}, ${homestay.state ?? ''}, Malaysia`.replace(/^,\s*|,\s*$/g, ''),
                ].filter(Boolean);

                for (const query of candidates) {
                    const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=my&q=${encodeURIComponent(query)}`;

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            continue;
                        }

                        const results = await response.json();
                        const match = results[0];

                        if (match?.lat && match?.lon) {
                            const coordinates = {
                                latitude: Number(match.lat),
                                longitude: Number(match.lon),
                            };

                            window.sessionStorage.setItem(cacheKey, JSON.stringify(coordinates));

                            return coordinates;
                        }
                    } catch (error) {
                        console.warn('Homestay geocoding failed', homestay.name, error);
                    }
                }

                return null;
            };

            const markerPopup = (homestay, coords) => `
                <div style="width: 220px;">
                    <img src="${homestay.image}" alt="${homestay.name}" style="width:100%;height:120px;object-fit:cover;border-radius:16px;margin-bottom:12px;">
                    <div style="font-weight:700;font-size:16px;color:#111827;">${homestay.name}</div>
                    <div style="margin-top:4px;font-size:13px;color:#4b5563;">${homestay.city || 'Malaysia'}${homestay.state ? `, ${homestay.state}` : ''}</div>
                    <div style="margin-top:8px;font-size:13px;line-height:1.5;color:#6b7280;">${homestay.address}</div>
                    <div style="margin-top:10px;display:flex;justify-content:space-between;gap:12px;font-size:13px;color:#166534;font-weight:600;">
                        <span>RM ${Number(homestay.price).toFixed(2)}</span>
                        <span>${Number(homestay.max_guest)} guests</span>
                    </div>
                    <div style="margin-top:12px;display:flex;gap:10px;">
                        <a href="https://www.google.com/maps/search/?api=1&query=${coords.latitude},${coords.longitude}" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border-radius:9999px;background:#ecfdf3;color:#166534;font-size:12px;font-weight:700;text-decoration:none;">
                            <i class="fa-solid fa-map-location-dot"></i>
                            Map
                        </a>
                        <a href="https://waze.com/ul?ll=${coords.latitude},${coords.longitude}&navigate=yes" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border-radius:9999px;background:#ecfeff;color:#0f766e;font-size:12px;font-weight:700;text-decoration:none;">
                            <i class="fa-brands fa-waze"></i>
                            Waze
                        </a>
                    </div>
                </div>
            `;

            for (const homestay of homestays) {
                const card = cards.find((item) => Number(item.dataset.homestayId) === homestay.id);
                const status = card?.querySelector('[data-homestay-status]');
                const coords = await geocodeHomestay(homestay);

                if (!coords) {
                    card?.classList.add('is-unavailable');

                    if (status) {
                        status.textContent = 'Address not found';
                    }

                    continue;
                }

                const marker = L.marker([coords.latitude, coords.longitude], {
                    icon: homeIcon,
                }).addTo(map);

                marker.bindPopup(markerPopup(homestay, coords));
                marker.on('click', () => {
                    setActiveCard(homestay.id);
                    map.flyTo([coords.latitude, coords.longitude], 16, {
                        duration: 0.9,
                    });
                });

                markers.set(homestay.id, {
                    marker,
                    coords,
                });

                if (status) {
                    status.textContent = 'Click to focus';
                }
            }

            cards.forEach((card) => {
                card.addEventListener('click', () => {
                    const homestayId = Number(card.dataset.homestayId);
                    const target = markers.get(homestayId);

                    if (!target) {
                        return;
                    }

                    if (activeHomestayId === homestayId) {
                        resetMapView();
                        return;
                    }

                    suppressPopupReset = true;
                    setActiveCard(homestayId);
                    map.flyTo([target.coords.latitude, target.coords.longitude], 16, {
                        duration: 1.2,
                    });
                    target.marker.openPopup();

                    mapElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    window.setTimeout(() => {
                        suppressPopupReset = false;
                    }, 250);
                });
            });

            map.on('popupclose', () => {
                if (suppressPopupReset) {
                    return;
                }

                if (activeHomestayId !== null) {
                    resetMapView();
                }
            });
        });
    </script>
@endsection
