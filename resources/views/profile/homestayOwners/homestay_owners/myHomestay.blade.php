@extends('profile.homestayOwners.app_ownerHomestay')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <main class="max-w-7xl mx-auto px-6 py-10">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">🏠 My Homestays</h1>

            <button id="openModalBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                + Add Homestay
            </button>
        </div>

        <!-- TABLE -->
        <div class="bg-white rounded-xl shadow overflow-hidden mt-6">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-center w-16">No</th>
                        <th class="px-6 py-4 text-left">Homestay Name</th>
                        <th class="px-6 py-4 text-left">Location</th>
                        <th class="px-6 py-4 text-center w-32">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($homestays as $index => $homestay)
                        <tr class="border-t hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-center font-medium">
                                {{ $index + 1 }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-gray-800">
                                {{ $homestay->homestay_name }}
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $homestay->city }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-4">

                                    <!-- EDIT -->
                                    <button
                                        class="openEditModal flex items-center gap-2 px-3 py-2 rounded-lg
           bg-blue-500 text-white hover:bg-blue-600 transition"
                                        data-homestay='@json($homestay)'
                                        data-amenities='@json($homestayAmenities[$homestay->homestay_id] ?? [])'
                                        data-images='@json($homestayImages[$homestay->homestay_id] ?? [])'>

                                        <!-- Pencil Icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688
                                 a1.875 1.875 0 112.652 2.652
                                 L10.582 16.07
                                 a4.5 4.5 0 01-1.897 1.13
                                 L6 18l.8-2.685
                                 a4.5 4.5 0 011.13-1.897
                                 l8.932-8.931z" />
                                        </svg>

                                        <span class="text-sm font-medium">Edit</span>
                                    </button>





                                    <button type="button"
                                        class="deleteHomestayBtn flex items-center gap-2 px-3 py-2 rounded-lg
           bg-red-500 text-white hover:bg-red-600 transition"
                                        data-id="{{ $homestay->homestay_id }}">
                                        <!-- Trash Icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9M9 6h6m-7 0h8m-9 0a1.5 1.5 0 011.5-1.5h5A1.5 1.5 0 0116.5 6m-10 0l1 14a2 2 0 002 2h6a2 2 0 002-2l1-14" />
                                        </svg>
                                        <span class="text-sm font-medium">Delete</span>
                                    </button>


                                </div>
                            </td>


                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                No homestays found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


    </main>

    {{-- CREATE MODAL --}}
    @include('profile.homestayOwners.homestay_owners.includes.create')
    @include('profile.homestayOwners.homestay_owners.includes.edit')


    <!-- JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const modal = document.getElementById('homestayModal');
            const openBtn = document.getElementById('openModalBtn');
            const closeBtn = document.getElementById('closeModalBtn');
            const cancelBtn = document.getElementById('cancelModalBtn');

            openBtn.addEventListener('click', () => {
                modal.classList.remove('hidden');
                window.refreshHomestayLocationPicker?.('create');
            });

            closeBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });

            cancelBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            document.querySelectorAll('.deleteHomestayBtn').forEach(btn => {

                btn.addEventListener('click', () => {

                    const homestayId = btn.dataset.id;

                    Swal.fire({
                        title: 'Delete homestay?',
                        text: 'This will permanently remove the homestay and all its images.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, delete it'
                    }).then((result) => {

                        if (!result.isConfirmed) return;

                        fetch(`/owner/homestays/${homestayId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document
                                        .querySelector('meta[name="csrf-token"]')
                                        .content
                                }
                            })
                            .then(res => res.json())
                            .then(data => {

                                if (data.success) {

                                    // remove row/card visually
                                    btn.closest('tr')?.remove();
                                    btn.closest('.homestay-card')?.remove();

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted',
                                        text: 'Homestay has been deleted',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire('Error', data.message, 'error');
                                }
                            });
                    });
                });

            });

        });
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const defaultCenter = [3.0738, 101.5183];
            const defaultZoom = 10;
            const stateAliases = {
                'federal territory of kuala lumpur': 'Kuala Lumpur',
                'wilayah persekutuan kuala lumpur': 'Kuala Lumpur',
                'federal territory of putrajaya': 'Putrajaya',
                'wilayah persekutuan putrajaya': 'Putrajaya',
                'federal territory of labuan': 'Labuan',
                'wilayah persekutuan labuan': 'Labuan',
                penang: 'Pulau Pinang',
                'pulau pinang': 'Pulau Pinang',
                malacca: 'Melaka',
            };

            const pickerConfigs = {
                create: {
                    form: document.getElementById('createHomestayForm'),
                    mapId: 'createHomestayMap',
                    addressId: 'create_address',
                    streetId: 'create_street',
                    cityId: 'create_city',
                    zipcodeId: 'create_zipcode',
                    stateId: 'create_state',
                    latitudeId: 'create_latitude',
                    longitudeId: 'create_longitude',
                    suggestionsId: 'create_address_suggestions',
                    statusId: 'createLocationStatus',
                    coordsId: 'createLocationCoords',
                    action: document.querySelector('[data-location-action="create-reverse"]'),
                },
                edit: {
                    form: document.getElementById('editHomestayForm'),
                    mapId: 'editHomestayMap',
                    addressId: 'edit_address',
                    streetId: 'edit_street',
                    cityId: 'edit_city',
                    zipcodeId: 'edit_zipcode',
                    stateId: 'edit_state',
                    latitudeId: 'edit_latitude',
                    longitudeId: 'edit_longitude',
                    suggestionsId: 'edit_address_suggestions',
                    statusId: 'editLocationStatus',
                    coordsId: 'editLocationCoords',
                    action: document.querySelector('[data-location-action="edit-reverse"]'),
                },
            };

            const pickerStates = {};
            const locationSearchUrl = @json(route('owner.homestays.location.search'));
            const locationReverseUrl = @json(route('owner.homestays.location.reverse'));

            const debounce = (callback, wait = 700) => {
                let timeoutId;

                return (...args) => {
                    window.clearTimeout(timeoutId);
                    timeoutId = window.setTimeout(() => callback(...args), wait);
                };
            };

            const normaliseState = (value) => {
                if (!value) {
                    return '';
                }

                const normalized = value.trim();
                return stateAliases[normalized.toLowerCase()] || normalized;
            };

            const escapeRegExp = (value = '') => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

            const extractCity = (address = {}) => (
                address.city ||
                address.town ||
                address.village ||
                address.municipality ||
                address.county ||
                ''
            );

            const extractUnit = (address = {}) => (
                address.house_number ||
                address.house_name ||
                address.building ||
                address.unit ||
                address.shop ||
                ''
            );

            const buildSuggestionTitle = (state, result) => {
                const address = result.address || {};
                const unit = extractUnit(address) || state.addressInput.value.trim();
                const road = address.road || address.pedestrian || address.residential || address.neighbourhood || state.streetInput.value.trim();
                const city = extractCity(address) || state.cityInput.value.trim();

                return [unit, road, city].filter(Boolean).join(', ') || result.display_name || 'Suggested location';
            };

            const buildSelectedStreetLine = (state, result) => {
                const address = result.address || {};
                const unit = extractUnit(address) || state.addressInput.value.trim();
                const city = extractCity(address) || state.cityInput.value.trim();
                const postcode = address.postcode || state.zipcodeInput.value.trim();
                const resolvedState = normaliseState(address.state || address.region || state.stateInput.value);
                const removableValues = [unit, city, postcode, resolvedState, 'Malaysia']
                    .filter(Boolean)
                    .map((value) => value.trim())
                    .filter(Boolean);

                const formattedParts = (result.display_name || '')
                    .split(',')
                    .map((segment) => segment.trim())
                    .map((segment) => {
                        let cleaned = segment;

                        removableValues.forEach((value) => {
                            cleaned = cleaned.replace(new RegExp(escapeRegExp(value), 'ig'), ' ');
                        });

                        return cleaned
                            .replace(/\s{2,}/g, ' ')
                            .replace(/^[,\s]+|[,\s]+$/g, '')
                            .trim();
                    })
                    .filter(Boolean);

                return formattedParts.join(', ') || address.road || state.streetInput.value.trim() || 'Selected street';
            };

            const buildStreetFromAddress = (address = {}) => {
                const estate = address.estate || address.building || address.hamlet || '';
                const road = address.road || address.pedestrian || address.residential || '';
                const locality = address.neighbourhood || address.suburb || address.quarter || '';

                return [estate, road, locality].filter(Boolean).join(', ');
            };

            const formatCoords = (lat, lng) => {
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return 'Coordinates will appear here';
                }

                return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            };

            const setStatus = (state, message) => {
                if (state.statusEl) {
                    state.statusEl.textContent = message;
                }
            };

            const hideSuggestions = (state) => {
                if (!state.suggestionsEl) {
                    return;
                }

                state.suggestionsEl.innerHTML = '';
                state.suggestionsEl.classList.add('hidden');
            };

            const applySuggestionSelection = (state, result) => {
                const lat = Number(result.lat);
                const lng = Number(result.lon);
                const unit = extractUnit(result.address || {});

                if (unit) {
                    state.addressInput.value = unit;
                }

                state.streetInput.value = buildSelectedStreetLine(state, result);
                applyAddressParts(state, result.address || {}, {
                    fillAddress: false,
                    fillStreet: false,
                    fillCity: true,
                    fillZip: true,
                    fillState: true,
                });
                moveToLocation(state, lat, lng);
                setStatus(state, 'Suggested address selected and pin updated.');
                hideSuggestions(state);
            };

            const renderSuggestions = (state, results) => {
                if (!state.suggestionsEl) {
                    return;
                }

                if (!results.length) {
                    hideSuggestions(state);
                    return;
                }

                state.suggestionsEl.innerHTML = results.map((result, index) => `
                    <button type="button"
                        class="block w-full border-b border-[#edf3ed] px-4 py-3 text-left transition hover:bg-[#f4fbf4] ${index === results.length - 1 ? 'border-b-0' : ''}"
                        data-suggestion-index="${index}">
                        <div class="text-sm font-medium text-gray-900">${buildSuggestionTitle(state, result)}</div>
                        <div class="mt-1 text-xs leading-5 text-gray-500">${result.display_name ?? ''}</div>
                    </button>
                `).join('');

                state.suggestionsEl.classList.remove('hidden');

                state.suggestionsEl.querySelectorAll('[data-suggestion-index]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const result = results[Number(button.dataset.suggestionIndex)];
                        if (result) {
                            applySuggestionSelection(state, result);
                        }
                    });
                });
            };

            const setCoords = (state, lat, lng) => {
                state.latInput.value = Number.isFinite(lat) ? lat.toFixed(7) : '';
                state.lngInput.value = Number.isFinite(lng) ? lng.toFixed(7) : '';

                if (state.coordsEl) {
                    state.coordsEl.textContent = formatCoords(lat, lng);
                }
            };

            const applyAddressParts = (state, address = {}, options = {}) => {
                const {
                    fillAddress = false,
                    fillStreet = false,
                    fillCity = true,
                    fillZip = true,
                    fillState = true,
                } = options;

                const road = address.road || address.pedestrian || address.residential || address.neighbourhood || '';
                const unit = extractUnit(address);
                const streetValue = buildStreetFromAddress(address) || road;
                const city = extractCity(address);
                const postcode = address.postcode || '';
                const resolvedState = normaliseState(address.state || address.region || '');

                if (fillAddress && unit) {
                    state.addressInput.value = unit;
                }

                if (fillStreet && state.streetInput) {
                    state.streetInput.value = streetValue || state.streetInput.value;
                }

                if (fillCity && city) {
                    state.cityInput.value = city;
                }

                if (fillZip && postcode) {
                    state.zipcodeInput.value = postcode;
                }

                if (fillState && resolvedState) {
                    state.stateInput.value = resolvedState;
                }
            };

            const ensureMarker = (state) => {
                if (!state.marker) {
                    state.marker = L.marker(defaultCenter, {
                        draggable: true,
                    }).addTo(state.map);

                    state.marker.on('dragend', () => {
                        const position = state.marker.getLatLng();
                        setCoords(state, position.lat, position.lng);
                        reverseLookup(state, position.lat, position.lng, true);
                    });
                }

                return state.marker;
            };

            const reverseLookup = async (state, lat, lng, fromDrag = false) => {
                setStatus(state, fromDrag ? 'Updating address from pin...' : 'Reading pin location...');

                try {
                    const url = new URL(locationReverseUrl, window.location.origin);
                    url.searchParams.set('lat', lat);
                    url.searchParams.set('lng', lng);

                    const response = await fetch(url.toString(), {
                        headers: {
                            Accept: 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Reverse geocoding failed');
                    }

                    const payload = await response.json();
                    const result = payload.result;

                    if (!result) {
                        throw new Error('Reverse geocoding returned no result');
                    }

                    applyAddressParts(state, result.address || {}, {
                        fillAddress: true,
                        fillStreet: true,
                        fillCity: true,
                        fillZip: true,
                        fillState: true,
                    });

                    setStatus(state, 'Pin synced with the address fields.');
                } catch (error) {
                    console.warn('Reverse geocoding failed', error);
                    setStatus(state, 'We saved the pin, but could not refresh the address details.');
                }
            };

            const forwardSearch = async (query) => {
                const results = await searchLocations(query, 1);
                return results[0] || null;
            };

            const searchLocations = async (query, limit = 5) => {
                const url = new URL(locationSearchUrl, window.location.origin);
                url.searchParams.set('q', query);
                url.searchParams.set('limit', limit);

                const response = await fetch(url.toString(), {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Search failed');
                }

                const payload = await response.json();
                return Array.isArray(payload.results) ? payload.results : [];
            };

            const moveToLocation = (state, lat, lng, zoom = 16) => {
                const marker = ensureMarker(state);
                marker.setLatLng([lat, lng]);
                state.map.setView([lat, lng], zoom);
                setCoords(state, lat, lng);
            };

            const candidateQueries = (state) => {
                const unit = state.addressInput.value.trim();
                const street = state.streetInput?.value?.trim() || '';
                const city = state.cityInput.value.trim();
                const postcode = state.zipcodeInput.value.trim();
                const areaState = state.stateInput.value.trim();
                const malaysia = 'Malaysia';
                const locality = [city, postcode, areaState, malaysia].filter(Boolean).join(', ');
                const fullAddress = [unit, street].filter(Boolean).join(', ');
                const streetKeywordMatch = street.match(/\b(jalan|lorong|persiaran|taman|kampung|bandar|lebuh|solok|laluan|lingkaran|dataran|medan|plaza|seksyen)\b.*$/i);
                const streetOnly = (streetKeywordMatch?.[0] || street).trim();

                const strippedStreet = street
                    .replace(/\b(no|no\.|unit|blok|block|lot|pt)\b/gi, '')
                    .replace(/[#,]/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();

                return Array.from(new Set([
                    fullAddress,
                    street,
                    streetOnly,
                    strippedStreet,
                    [fullAddress, locality].filter(Boolean).join(', '),
                    [street, locality].filter(Boolean).join(', '),
                    [streetOnly, locality].filter(Boolean).join(', '),
                    [strippedStreet, locality].filter(Boolean).join(', '),
                    [fullAddress, malaysia].filter(Boolean).join(', '),
                    [street, malaysia].filter(Boolean).join(', '),
                    [streetOnly, malaysia].filter(Boolean).join(', '),
                    [strippedStreet, malaysia].filter(Boolean).join(', '),
                ].filter((query) => query.replace(/[^a-z0-9]/gi, '').length >= 4)));
            };

            const geocodeFromFields = async (state) => {
                const queries = candidateQueries(state);

                if (!queries.length) {
                    hideSuggestions(state);
                    return;
                }

                setStatus(state, 'Looking up the typed address...');

                try {
                    const resultMap = new Map();

                    for (const query of queries) {
                        const queryResults = await searchLocations(query, 5);

                        queryResults.forEach((result) => {
                            const key = `${result.place_id ?? result.display_name}-${result.lat}-${result.lon}`;

                            if (!resultMap.has(key)) {
                                resultMap.set(key, result);
                            }
                        });

                        if (resultMap.size >= 5) {
                            break;
                        }
                    }

                    const results = Array.from(resultMap.values()).slice(0, 5);
                    const result = results[0] || null;

                    renderSuggestions(state, results);

                    if (!result?.lat || !result?.lon) {
                        setStatus(state, 'We could not place that address yet. Try adjusting the postcode or drag the pin.');
                        return;
                    }

                    const lat = Number(result.lat);
                    const lng = Number(result.lon);

                    moveToLocation(state, lat, lng);
                    applyAddressParts(state, result.address || {}, {
                        fillAddress: false,
                        fillStreet: false,
                        fillCity: true,
                        fillZip: true,
                        fillState: true,
                    });
                    setStatus(state, 'Top suggestion applied to the form and pin.');
                } catch (error) {
                    console.warn('Address geocoding failed', error);
                    hideSuggestions(state);
                    setStatus(state, 'Address lookup failed. You can still drag the pin manually.');
                }
            };

            const geocodeFromPostcode = async (state) => {
                const postcode = state.zipcodeInput.value.trim();

                if (postcode.length < 4) {
                    return;
                }

                setStatus(state, 'Checking postcode area...');

                try {
                    const result = await forwardSearch(`${postcode}, Malaysia`);

                    if (!result?.lat || !result?.lon) {
                        setStatus(state, 'Postcode not found. Try a full address instead.');
                        return;
                    }

                    const lat = Number(result.lat);
                    const lng = Number(result.lon);

                    moveToLocation(state, lat, lng, 14);
                    applyAddressParts(state, result.address || {}, {
                        fillAddress: false,
                        fillStreet: false,
                        fillCity: true,
                        fillZip: true,
                        fillState: true,
                    });
                    setStatus(state, 'Postcode matched and the map pin was updated.');
                } catch (error) {
                    console.warn('Postcode lookup failed', error);
                    setStatus(state, 'Postcode lookup failed. Please drag the pin or complete the address.');
                }
            };

            const initialisePicker = (key, config) => {
                if (!config.form || !document.getElementById(config.mapId) || typeof L === 'undefined') {
                    return;
                }

                const state = {
                    key,
                    form: config.form,
                    mapEl: document.getElementById(config.mapId),
                    addressInput: document.getElementById(config.addressId),
                    streetInput: document.getElementById(config.streetId),
                    cityInput: document.getElementById(config.cityId),
                    zipcodeInput: document.getElementById(config.zipcodeId),
                    stateInput: document.getElementById(config.stateId),
                    latInput: document.getElementById(config.latitudeId),
                    lngInput: document.getElementById(config.longitudeId),
                    statusEl: document.getElementById(config.statusId),
                    coordsEl: document.getElementById(config.coordsId),
                    actionBtn: config.action,
                    suggestionsEl: document.getElementById(config.suggestionsId),
                    map: null,
                    marker: null,
                };

                state.map = L.map(state.mapEl, {
                    scrollWheelZoom: false,
                }).setView(defaultCenter, defaultZoom);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(state.map);

                ensureMarker(state);
                setCoords(state, Number.parseFloat(state.latInput.value), Number.parseFloat(state.lngInput.value));

                const debouncedGeocode = debounce(() => geocodeFromFields(state));
                const debouncedPostcode = debounce(() => geocodeFromPostcode(state), 500);

                [state.addressInput, state.streetInput, state.cityInput, state.stateInput].forEach((input) => {
                    input?.addEventListener('input', debouncedGeocode);
                    input?.addEventListener('change', debouncedGeocode);
                });

                state.streetInput?.addEventListener('blur', () => {
                    window.setTimeout(() => hideSuggestions(state), 150);
                });

                state.zipcodeInput?.addEventListener('input', () => {
                    debouncedPostcode();
                    debouncedGeocode();
                });
                state.zipcodeInput?.addEventListener('blur', () => {
                    geocodeFromPostcode(state);
                });

                state.actionBtn?.addEventListener('click', () => {
                    const lat = Number.parseFloat(state.latInput.value);
                    const lng = Number.parseFloat(state.lngInput.value);

                    if (Number.isFinite(lat) && Number.isFinite(lng)) {
                        reverseLookup(state, lat, lng);
                    }
                });

                pickerStates[key] = state;
            };

            Object.entries(pickerConfigs).forEach(([key, config]) => initialisePicker(key, config));

            window.refreshHomestayLocationPicker = (key) => {
                const state = pickerStates[key];

                if (!state) {
                    return;
                }

                window.setTimeout(() => {
                    state.map.invalidateSize();

                    const lat = Number.parseFloat(state.latInput.value);
                    const lng = Number.parseFloat(state.lngInput.value);

                    if (Number.isFinite(lat) && Number.isFinite(lng)) {
                        moveToLocation(state, lat, lng);
                        setStatus(state, 'Stored pin loaded. You can drag it to adjust.');
                        hideSuggestions(state);
                        return;
                    }

                    ensureMarker(state).setLatLng(defaultCenter);
                    state.map.setView(defaultCenter, defaultZoom);
                    setCoords(state, Number.NaN, Number.NaN);
                    hideSuggestions(state);
                    setStatus(state, 'Start typing the address or postcode to place the pin.');
                }, 80);
            };

            window.refreshHomestayLocationPicker('create');

            document.addEventListener('click', (event) => {
                Object.values(pickerStates).forEach((state) => {
                    if (!state?.suggestionsEl || !state.addressInput) {
                        return;
                    }

                    if (!state.suggestionsEl.contains(event.target) && event.target !== state.streetInput) {
                        hideSuggestions(state);
                    }
                });
            });
        });
    </script>
@endsection
