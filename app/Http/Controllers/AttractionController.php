<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AttractionController extends Controller
{
    public function index()
    {
        $lat = (float) session('lat', 3.1390);
        $lon = (float) session('lon', 101.6869);
        $hasUserLocation = session()->has('lat') && session()->has('lon');

        $city = $hasUserLocation
            ? $this->detectCity($lat, $lon)
            : 'Kuala Lumpur';
        $tours = $this->googleNearbyTours($lat, $lon);
        $localAttractions = $this->localAttractions($lat, $lon);
        $transportInfo = $this->nearbyTransport($lat, $lon);
        $mapPoints = $this->buildMapPoints($lat, $lon, $city, $tours, $localAttractions, $transportInfo);

        return view('profile.landingPages.attNtours', compact(
            'tours',
            'city',
            'hasUserLocation',
            'transportInfo',
            'mapPoints'
        ));
    }

    public function show(string $tourKey)
    {
        $lat = (float) session('lat', 3.1390);
        $lon = (float) session('lon', 101.6869);
        $hasUserLocation = session()->has('lat') && session()->has('lon');

        $city = $hasUserLocation
            ? $this->detectCity($lat, $lon)
            : 'Kuala Lumpur';

        $tour = collect($this->googleNearbyTours($lat, $lon))
            ->firstWhere('tour_key', $tourKey);

        abort_if(! $tour, 404);

        return view('profile.landingPages.tourDetail', compact('tour', 'city', 'hasUserLocation'));
    }

    public function setLocation(Request $request)
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
        ]);

        session([
            'lat' => (float) $validated['lat'],
            'lon' => (float) $validated['lon'],
        ]);

        return response()->json([
            'status' => 'ok',
            'lat' => $validated['lat'],
            'lon' => $validated['lon'],
        ]);
    }

    private function googleNearbyTours(float $latitude = 3.1390, float $longitude = 101.6869): array
    {
        $apiKey = config('services.google_places.key');
        if (! $apiKey) {
            return [];
        }

        $cacheKey = sprintf('google.tours.v4.%s.%s', round($latitude, 3), round($longitude, 3));

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($apiKey, $latitude, $longitude) {
            $places = $this->searchNearbyGooglePlaces(
                $apiKey,
                $latitude,
                $longitude,
                ['tourist_attraction', 'museum', 'historical_landmark', 'art_gallery', 'amusement_park'],
                20000,
                12,
                'places.id,places.displayName,places.formattedAddress,places.location,places.googleMapsUri,places.primaryType,places.types,places.rating,places.userRatingCount,places.photos'
            );

            return collect($places)
                ->map(function (array $place) use ($apiKey, $latitude, $longitude) {
                    $placeId = data_get($place, 'id');
                    $placeDetails = $this->googlePlaceDetails($placeId, $apiKey);

                    return $this->normalizeGoogleTour(
                        $placeDetails ?: $place,
                        $latitude,
                        $longitude,
                        $apiKey
                    );
                })
                ->filter()
                ->sortBy([
                    ['has_google_photo', 'desc'],
                    ['distance', 'asc'],
                    ['rating', 'desc'],
                ])
                ->take(6)
                ->values()
                ->all();
        });
    }

    private function normalizeGoogleTour(array $place, float $originLat, float $originLon, string $apiKey): ?array
    {
        $lat = data_get($place, 'location.latitude');
        $lon = data_get($place, 'location.longitude');

        if (! is_numeric($lat) || ! is_numeric($lon)) {
            return null;
        }

        $name = trim((string) data_get($place, 'displayName.text', 'Recommended tour'));
        $primaryType = (string) data_get($place, 'primaryType', '');
        $category = $this->formatGooglePlaceType($primaryType !== '' ? $primaryType : 'tourist_attraction');
        $address = trim((string) data_get($place, 'formattedAddress', ''));
        $primaryPhoto = collect(data_get($place, 'photos', []))->first();
        $photoUrl = $this->googlePlacePhotoUrl(data_get($primaryPhoto, 'name'), $apiKey, 800);

        return [
            'tour_key' => (string) data_get($place, 'id'),
            'name' => $name !== '' ? $name : 'Recommended tour',
            'description' => $address !== ''
                ? 'Explore ' . ($name !== '' ? $name : 'this nearby place') . ' near ' . $address . '.'
                : 'Discover this nearby place to visit around your selected area.',
            'categories_display' => $category,
            'latitude' => (float) $lat,
            'longitude' => (float) $lon,
            'maps_url' => data_get($place, 'googleMapsUri') ?: $this->openStreetMapLink($lat, $lon),
            'address' => $address !== '' ? $address : null,
            'distance' => round($this->distanceInKm($originLat, $originLon, (float) $lat, (float) $lon), 1),
            'rating' => is_numeric(data_get($place, 'rating')) ? (float) data_get($place, 'rating') : null,
            'has_google_photo' => $photoUrl !== null,
            'pictures' => [$photoUrl ?? asset('assets/images/attractions/kl.jpg')],
            'photo_attribution' => $this->googlePhotoAttribution($primaryPhoto),
        ];
    }

    private function localAttractions(float $latitude, float $longitude): array
    {
        $apiKey = config('services.opentripmap.key');
        if (! $apiKey) {
            return [];
        }

        $cacheKey = sprintf('opentripmap.attractions.%s.%s', round($latitude, 3), round($longitude, 3));

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($latitude, $longitude, $apiKey) {
            $response = Http::timeout(10)->get(
                'https://api.opentripmap.com/0.1/en/places/radius',
                [
                    'apikey' => $apiKey,
                    'radius' => 20000,
                    'lon' => $longitude,
                    'lat' => $latitude,
                    'limit' => 8,
                    'rate' => 2,
                    'format' => 'json',
                    'kinds' => 'interesting_places,natural,architecture,cultural',
                ]
            );

            if (! $response->successful()) {
                return [];
            }

            $items = $response->json() ?? [];

            return collect($items)
                ->filter(fn ($item) => ! empty($item['name']))
                ->filter(fn ($item) => ! isset($item['dist']) || (float) $item['dist'] <= 20000)
                ->map(function (array $item) {
                    return [
                        'name' => $item['name'],
                        'distance' => isset($item['dist']) ? round((float) $item['dist'] / 1000, 1) : null,
                        'rating' => $item['rate'] ?? null,
                        'kinds' => $this->formatKinds($item['kinds'] ?? ''),
                        'latitude' => $item['point']['lat'] ?? null,
                        'longitude' => $item['point']['lon'] ?? null,
                        'maps_url' => $this->openStreetMapLink(
                            $item['point']['lat'] ?? null,
                            $item['point']['lon'] ?? null
                        ),
                    ];
                })
                ->values()
                ->all();
        });
    }

    private function nearbyTransport(float $latitude, float $longitude): array
    {
        $apiKey = config('services.google_places.key');
        if (! $apiKey) {
            return $this->emptyTransportInfo('Google Places API');
        }

        $cacheKey = sprintf('transport.google.%s.%s', round($latitude, 3), round($longitude, 3));

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($latitude, $longitude, $apiKey) {
            $trainPlaces = $this->searchNearbyGooglePlaces(
                $apiKey,
                $latitude,
                $longitude,
                ['train_station', 'light_rail_station', 'subway_station', 'transit_station'],
                5000,
                1
            );

            $busPlaces = $this->searchNearbyGooglePlaces(
                $apiKey,
                $latitude,
                $longitude,
                ['bus_station', 'bus_stop', 'transit_station'],
                3000,
                4
            );

            $nearestTrainStation = collect($trainPlaces)
                ->map(fn (array $place) => $this->formatGoogleTransportPlace($place, $latitude, $longitude, 'Train station'))
                ->filter()
                ->first();

            $busStops = collect($busPlaces)
                ->map(fn (array $place) => $this->formatGoogleTransportPlace($place, $latitude, $longitude, 'Bus stop'))
                ->filter()
                ->reject(function (array $stop) use ($nearestTrainStation) {
                    return $nearestTrainStation
                        && ($stop['place_id'] ?? null) === ($nearestTrainStation['place_id'] ?? null);
                })
                ->take(4)
                ->values()
                ->all();

            return [
                'source' => 'Google Places API',
                'nearest_train_station' => $nearestTrainStation,
                'bus_stops' => $busStops,
                'bus_available' => ! empty($busStops),
            ];
        });
    }

    private function searchNearbyGooglePlaces(
        string $apiKey,
        float $latitude,
        float $longitude,
        array $types,
        float $radius,
        int $maxResults,
        ?string $fieldMask = null
    ): array {
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $apiKey,
            'X-Goog-FieldMask' => $fieldMask ?: 'places.id,places.displayName,places.formattedAddress,places.location,places.googleMapsUri,places.primaryType,places.types',
        ])->timeout(12)->post('https://places.googleapis.com/v1/places:searchNearby', [
            'includedTypes' => $types,
            'maxResultCount' => $maxResults,
            'rankPreference' => 'DISTANCE',
            'locationRestriction' => [
                'circle' => [
                    'center' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ],
                    'radius' => $radius,
                ],
            ],
        ]);

        if (! $response->successful()) {
            return [];
        }

        return $response->json()['places'] ?? [];
    }

    private function googlePlaceDetails(?string $placeId, string $apiKey): ?array
    {
        if (! is_string($placeId) || trim($placeId) === '') {
            return null;
        }

        $cacheKey = 'google.place.details.v1.' . sha1($placeId);
        $cachedDetails = Cache::get($cacheKey);

        if (is_array($cachedDetails)) {
            return $cachedDetails;
        }

        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $apiKey,
            'X-Goog-FieldMask' => 'id,displayName,formattedAddress,location,googleMapsUri,primaryType,types,rating,userRatingCount,photos',
        ])->timeout(12)->get(sprintf('https://places.googleapis.com/v1/places/%s', $placeId));

        if (! $response->successful()) {
            return null;
        }

        $details = $response->json();

        if (! is_array($details) || empty($details['id'])) {
            return null;
        }

        Cache::put($cacheKey, $details, now()->addHours(6));

        return $details;
    }

    private function formatGoogleTransportPlace(array $place, float $originLat, float $originLon, string $fallbackType): ?array
    {
        $lat = data_get($place, 'location.latitude');
        $lon = data_get($place, 'location.longitude');

        if (! is_numeric($lat) || ! is_numeric($lon)) {
            return null;
        }

        return [
            'place_id' => data_get($place, 'id'),
            'name' => trim((string) data_get($place, 'displayName.text', $fallbackType)) ?: $fallbackType,
            'type' => $fallbackType,
            'distance' => round($this->distanceInKm($originLat, $originLon, (float) $lat, (float) $lon), 1),
            'latitude' => (float) $lat,
            'longitude' => (float) $lon,
            'maps_url' => data_get($place, 'googleMapsUri') ?: $this->openStreetMapLink($lat, $lon),
            'address' => data_get($place, 'formattedAddress'),
        ];
    }

    private function emptyTransportInfo(string $source = 'Google Places API'): array
    {
        return [
            'source' => $source,
            'nearest_train_station' => null,
            'bus_stops' => [],
            'bus_available' => false,
        ];
    }

    private function buildMapPoints(
        float $latitude,
        float $longitude,
        ?string $city,
        array $tours,
        array $localAttractions,
        array $transportInfo
    ): array {
        $points = [[
            'name' => ($city ?: 'Current area') . ' area',
            'type' => 'Your area',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'highlight' => true,
        ]];

        foreach ($localAttractions as $attraction) {
            if (isset($attraction['latitude'], $attraction['longitude'])) {
                $points[] = [
                    'name' => $attraction['name'],
                    'type' => 'Attraction',
                    'latitude' => (float) $attraction['latitude'],
                    'longitude' => (float) $attraction['longitude'],
                ];
            }
        }

        foreach ($tours as $tour) {
            if (isset($tour['latitude'], $tour['longitude']) && is_numeric($tour['latitude']) && is_numeric($tour['longitude'])) {
                $points[] = [
                    'name' => $tour['name'],
                    'type' => 'Attraction',
                    'latitude' => (float) $tour['latitude'],
                    'longitude' => (float) $tour['longitude'],
                ];
            }
        }

        if (! empty($transportInfo['nearest_train_station'])) {
            $points[] = [
                'name' => $transportInfo['nearest_train_station']['name'],
                'type' => 'Train station',
                'latitude' => $transportInfo['nearest_train_station']['latitude'],
                'longitude' => $transportInfo['nearest_train_station']['longitude'],
            ];
        }

        foreach ($transportInfo['bus_stops'] ?? [] as $busStop) {
            $points[] = [
                'name' => $busStop['name'],
                'type' => 'Bus stop',
                'latitude' => $busStop['latitude'],
                'longitude' => $busStop['longitude'],
            ];
        }

        return collect($points)
            ->unique(fn (array $point) => implode('|', [
                $point['type'],
                round((float) $point['latitude'], 5),
                round((float) $point['longitude'], 5),
            ]))
            ->values()
            ->all();
    }

    private function openStreetMapLink($latitude, $longitude): ?string
    {
        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        return sprintf(
            'https://www.openstreetmap.org/?mlat=%s&mlon=%s#map=15/%s/%s',
            $latitude,
            $longitude,
            $latitude,
            $longitude
        );
    }

    private function distanceInKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function formatKinds(string $kinds): string
    {
        $primaryKind = collect(explode(',', $kinds))
            ->map(fn ($kind) => trim($kind))
            ->first(fn ($kind) => $kind !== '');

        if (! $primaryKind) {
            return 'Local attraction';
        }

        return ucwords(str_replace('_', ' ', $primaryKind));
    }

    private function formatGooglePlaceType(string $type): string
    {
        return Str::of($type)
            ->replace('_', ' ')
            ->title()
            ->value();
    }

    private function googlePlacePhotoUrl(?string $photoName, string $apiKey, int $maxWidth = 800): ?string
    {
        if (! is_string($photoName) || trim($photoName) === '') {
            return null;
        }

        $cacheKey = 'google.place.photo.v2.' . sha1($photoName . '|' . $maxWidth);
        $cachedPhotoUri = Cache::get($cacheKey);

        if (is_string($cachedPhotoUri) && trim($cachedPhotoUri) !== '') {
            return $cachedPhotoUri;
        }

        $response = Http::timeout(12)->get(sprintf('https://places.googleapis.com/v1/%s/media', $photoName), [
            'key' => $apiKey,
            'maxWidthPx' => $maxWidth,
            'skipHttpRedirect' => 'true',
        ]);

        if (! $response->successful()) {
            return null;
        }

        $photoUri = data_get($response->json(), 'photoUri');

        if (! is_string($photoUri) || trim($photoUri) === '') {
            return null;
        }

        Cache::put($cacheKey, $photoUri, now()->addHours(6));

        return $photoUri;
    }

    private function googlePhotoAttribution($photo): ?array
    {
        $attribution = collect(data_get($photo, 'authorAttributions', []))->first();

        if (! is_array($attribution)) {
            return null;
        }

        $displayName = trim((string) data_get($attribution, 'displayName', ''));
        $uri = trim((string) data_get($attribution, 'uri', ''));

        if ($displayName === '') {
            return null;
        }

        return [
            'name' => $displayName,
            'uri' => $uri !== '' ? $uri : null,
        ];
    }

    private function detectCity(float $lat, float $lon): ?string
    {
        $cacheKey = sprintf('location.city.%s.%s', round($lat, 3), round($lon, 3));

        return Cache::remember($cacheKey, now()->addDay(), function () use ($lat, $lon) {
            $response = Http::withHeaders([
                'User-Agent' => 'InapDesaStudentProject/1.0',
            ])->timeout(6)->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat,
                'lon' => $lon,
                'format' => 'json',
                'zoom' => 14,
            ]);

            if (! $response->successful()) {
                return 'Kuala Lumpur';
            }

            $address = $response->json()['address'] ?? [];

            return $address['city']
                ?? $address['town']
                ?? $address['municipality']
                ?? $address['suburb']
                ?? 'Kuala Lumpur';
        });
    }
}
