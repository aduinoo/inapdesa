<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class OwnerHomestayController extends Controller
{
    public function dashboard()
    {
        $ownerId = Auth::id();

        $homestayIds = DB::table('homestays')
            ->where('owner_id', $ownerId)
            ->pluck('homestay_id');

        $totalHomestays = $homestayIds->count();

        $activeBookings = DB::table('bookings')
            ->whereIn('homestay_id', $homestayIds)
            ->whereDate('check_out', '>=', now())
            ->count();

        $totalEarnings = DB::table('owner_payouts')
            ->where('owner_id', $ownerId)
            ->where('payout_status', 'paid')
            ->sum('amount');

        $averageRating = DB::table('reviews')
            ->whereIn('homestay_id', $homestayIds)
            ->avg('rating');

        $upcomingBookings = DB::table('bookings')
            ->whereIn('homestay_id', $homestayIds)
            ->orderBy('check_in')
            ->limit(5)
            ->get();

        $reports = DB::table('reports')
            ->whereIn('homestay_id', $homestayIds)
            ->where('status', 'pending')
            ->select('reports.*', 'reports.report_id as id')
            ->get();

        $totalConfirmedBookings = DB::table('bookings')
            ->whereIn('homestay_id', $homestayIds)
            ->where('booking_status', 'confirmed')
            ->count();

        $cancelledBookings = DB::table('bookings')
            ->whereIn('homestay_id', $homestayIds)
            ->where('booking_status', 'cancelled')
            ->count();

        $monthlyBookings = collect();
        $monthlyRevenue = collect();

        if ($homestayIds->isNotEmpty()) {
            $sixMonthsAgo = now()->subMonths(6)->startOfMonth();

            $monthlyBookings = DB::table('bookings')
                ->whereIn('homestay_id', $homestayIds)
                ->where('booking_status', 'confirmed')
                ->where('check_in', '>=', $sixMonthsAgo)
                ->selectRaw("DATE_FORMAT(check_in, '%b') as month, COUNT(*) as count")
                ->groupBy('month')
                ->orderByRaw('MIN(check_in)')
                ->pluck('count', 'month');

            $monthlyRevenue = DB::table('bookings')
                ->whereIn('homestay_id', $homestayIds)
                ->where('booking_status', 'confirmed')
                ->where('check_in', '>=', $sixMonthsAgo)
                ->selectRaw("DATE_FORMAT(check_in, '%b') as month, SUM(total_price) as total")
                ->groupBy('month')
                ->orderByRaw('MIN(check_in)')
                ->pluck('total', 'month');

            $bookingStatusBreakdown = DB::table('bookings')
                ->whereIn('homestay_id', $homestayIds)
                ->selectRaw("booking_status, COUNT(*) as count")
                ->groupBy('booking_status')
                ->pluck('count', 'booking_status');

            $homestayPerformance = DB::table('bookings')
                ->whereIn('bookings.homestay_id', $homestayIds)
                ->where('booking_status', 'confirmed')
                ->join('homestays', 'homestays.homestay_id', '=', 'bookings.homestay_id')
                ->selectRaw("homestays.homestay_name, COUNT(*) as count")
                ->groupBy('homestays.homestay_id', 'homestays.homestay_name')
                ->orderByDesc('count')
                ->get();
        } else {
            $bookingStatusBreakdown = collect();
            $homestayPerformance = collect();
        }

        return view('profile.homestayOwners.owner_dashboard', compact(
            'totalHomestays',
            'activeBookings',
            'totalEarnings',
            'averageRating',
            'upcomingBookings',
            'reports',
            'totalConfirmedBookings',
            'cancelledBookings',
            'monthlyBookings',
            'monthlyRevenue',
            'bookingStatusBreakdown',
            'homestayPerformance'
        ));
    }
    public function myHomestay()
    {
        $ownerId = Auth::id();

        // Homestays
        $homestays = DB::table('homestays')
            ->where('owner_id', $ownerId)
            ->get();

        // All amenities (for pills)
        $amenities = DB::table('amenities')->get();

        // Images grouped by homestay
        $homestayImages = DB::table('homestay_images')
            ->whereIn('homestay_id', $homestays->pluck('homestay_id'))
            ->get()
            ->groupBy('homestay_id');

        $homestayAmenities = DB::table('homestay_amenities')
            ->join('amenities', 'amenities.amenity_id', '=', 'homestay_amenities.amenity_id')
            ->whereIn('homestay_amenities.homestay_id', $homestays->pluck('homestay_id'))
            ->select(
                'homestay_amenities.homestay_id',
                'amenities.amenity_id',
                'amenities.amenity_name'
            )
            ->get()
            ->groupBy('homestay_id');


        return view('profile.homestayOwners.homestay_owners.myHomestay', compact(
            'homestays',
            'amenities',
            'homestayImages',
            'homestayAmenities'
        ));
    }

    public function locationSearch(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        return response()->json([
            'results' => $this->googleLocationSearch(
                $validated['q'],
                (int) ($validated['limit'] ?? 5)
            ),
        ]);
    }

    public function locationReverse(Request $request)
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return response()->json([
            'result' => $this->googleReverseLocation(
                (float) $validated['lat'],
                (float) $validated['lng']
            ),
        ]);
    }

    #store new homestay
    public function store(Request $request)
    {
        $request->validate([
            'homestay_name' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'address'      => 'nullable|string|max:255',
            'street'       => 'required|string|max:255',
            'city'         => 'required|string|max:100',
            'zipcode'      => 'required|string|max:20',
            'state'        => 'required|string|max:100',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'base_price'   => 'required|numeric|min:0',
            'max_guest'    => 'required|integer|min:1',
            'status'       => 'required|in:active,inactive',
            'images.*'     => 'image|mimes:jpg,jpeg,png|max:2048',
            'amenities'    => 'nullable|array',
            'amenities.*'  => 'string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $ownerId = Auth::id();

            /* ================= HOMESTAY ================= */
            $homestayId = DB::table('homestays')->insertGetId([
                'owner_id'      => $ownerId,
                'homestay_name' => $request->homestay_name,
                'description'   => $request->description,
                'address'       => trim((string) $request->address),
                'street'        => trim((string) $request->street),
                'city'          => $request->city,
                'zipcode'       => $request->zipcode,
                'state'         => $request->state,
                'latitude'      => $request->latitude,
                'longitude'     => $request->longitude,
                'base_price'    => $request->base_price,
                'max_guest'     => $request->max_guest,
                'status'        => $request->status,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            /* ================= AMENITIES ================= */
            // ===== AMENITIES =====
            if ($request->filled('amenities')) {

                foreach ($request->amenities as $amenityName) {

                    // 1. Find amenity by NAME
                    $amenity = DB::table('amenities')
                        ->where('amenity_name', $amenityName)
                        ->first();

                    // 2. If not exists → create it
                    if (!$amenity) {
                        $amenityId = DB::table('amenities')->insertGetId([
                            'amenity_name' => $amenityName,
                        ]);
                    } else {
                        $amenityId = $amenity->amenity_id;
                    }

                    // 3. Insert into pivot table
                    DB::table('homestay_amenities')->insert([
                        'homestay_id' => $homestayId,
                        'amenity_id'  => $amenityId,
                    ]);
                }
            }


            /* ================= IMAGES ================= */
            if ($request->hasFile('images')) {

                $path = public_path("assets/images/ownerHomestay/{$homestayId}");

                if (!File::exists($path)) {
                    File::makeDirectory($path, 0755, true);
                }

                $counter = 1;

                foreach ($request->file('images') as $image) {

                    $filename = "img-{$counter}." . $image->getClientOriginalExtension();
                    $image->move($path, $filename);

                    DB::table('homestay_images')->insert([
                        'homestay_id' => $homestayId,
                        'image_path'  => "assets/images/ownerHomestay/{$homestayId}/{$filename}",
                        'image_type'  => 'gallery',
                        'created_at'  => now(),
                    ]);

                    $counter++;
                }
            }

            /* ✅ THIS IS WHAT YOU MISSED */
            DB::commit();

            return redirect()
                ->route('owner.myHomestay')
                ->with('success', 'Homestay added successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    # Update homestay
    public function update(Request $request)
    {
        # validation
        $request->validate([
            'homestay_id'   => 'required|integer|exists:homestays,homestay_id',
            'homestay_name' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'address'      => 'nullable|string|max:255',
            'street'       => 'required|string|max:255',
            'city'         => 'required|string|max:100',
            'zipcode'      => 'required|string|max:20',
            'state'        => 'required|string|max:100',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'base_price'   => 'required|numeric|min:0',
            'max_guest'    => 'required|integer|min:1',
            'status'       => 'required|in:active,inactive',
            'images.*'     => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {

            $homestayId = $request->homestay_id;

            # update homestay info
            DB::table('homestays')
                ->where('homestay_id', $homestayId)
                ->update([
                    'homestay_name' => $request->homestay_name,
                    'description'   => $request->description,
                    'address'       => trim((string) $request->address),
                    'street'        => trim((string) $request->street),
                    'city'          => $request->city,
                    'zipcode'       => $request->zipcode,
                    'state'         => $request->state,
                    'latitude'      => $request->latitude,
                    'longitude'     => $request->longitude,
                    'base_price'    => $request->base_price,
                    'max_guest'     => $request->max_guest,
                    'status'        => $request->status,
                    'updated_at'    => now(),
                ]);

           
            DB::table('homestay_amenities')
                ->where('homestay_id', $homestayId)
                ->delete();

            if ($request->filled('amenities')) {

                foreach ($request->amenities as $amenityValue) {

                    // CASE 1: Existing amenity (ID)
                    if (is_numeric($amenityValue)) {
                        $amenityId = $amenityValue;
                    }

                    // CASE 2: New amenity (NAME)
                    else {
                        $amenityName = trim($amenityValue);

                        // find or create
                        $amenityId = DB::table('amenities')
                            ->where('amenity_name', $amenityName)
                            ->value('amenity_id');

                        if (!$amenityId) {
                            $amenityId = DB::table('amenities')->insertGetId([
                                'amenity_name' => $amenityName,
                            ]);
                        }
                    }

                    // insert pivot
                    DB::table('homestay_amenities')->insert([
                        'homestay_id' => $homestayId,
                        'amenity_id'  => $amenityId,
                    ]);
                }
            }



            # ADD NEW IMAGES (CONTINUE img-x)
            if ($request->hasFile('images')) {

                $path = public_path("assets/images/ownerHomestay/{$homestayId}");

                if (!File::exists($path)) {
                    File::makeDirectory($path, 0755, true);
                }

                # get current image count
                $existingCount = DB::table('homestay_images')
                    ->where('homestay_id', $homestayId)
                    ->count();

                $counter = $existingCount + 1;

                foreach ($request->file('images') as $image) {

                    $extension = $image->getClientOriginalExtension();
                    $filename  = "img-{$counter}." . $extension;

                    $image->move($path, $filename);

                    DB::table('homestay_images')->insert([
                        'homestay_id' => $homestayId,
                        'image_path'  => "assets/images/ownerHomestay/{$homestayId}/{$filename}",
                        'image_type'  => 'gallery',
                        'metadata'    => null,
                        'created_at'  => now(),
                    ]);

                    $counter++;
                }
            }

            DB::commit();

            return redirect()
                ->route('owner.myHomestay')
                ->with('success', 'Homestay updated successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update homestay. ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            // 🔹 Get images first
            $images = DB::table('homestay_images')
                ->where('homestay_id', $id)
                ->get();

            // 🔹 Delete image files
            foreach ($images as $img) {
                $path = public_path($img->image_path);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }

            // 🔹 Delete image folder
            $folderPath = public_path("assets/images/ownerHomestay/$id");
            if (File::exists($folderPath)) {
                File::deleteDirectory($folderPath);
            }

            // 🔹 Delete DB records
            DB::table('homestay_images')
                ->where('homestay_id', $id)
                ->delete();

            DB::table('homestay_amenities')
                ->where('homestay_id', $id)
                ->delete();

            DB::table('homestays')
                ->where('homestay_id', $id)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Homestay deleted successfully'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function googleLocationSearch(string $query, int $limit = 5): array
    {
        $apiKey = config('services.google_places.key');

        if (! $apiKey) {
            return [];
        }

        $cacheKey = sprintf('owner.location.search.%s.%d', md5(mb_strtolower($query)), $limit);

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($apiKey, $query, $limit) {
            $results = collect($this->googleGeocodeSearch($query, $limit, $apiKey));

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Goog-Api-Key' => $apiKey,
                'X-Goog-FieldMask' => 'suggestions.placePrediction.placeId,suggestions.placePrediction.text',
            ])->timeout(12)->post('https://places.googleapis.com/v1/places:autocomplete', [
                'input' => $query,
                'includedRegionCodes' => ['my'],
                'regionCode' => 'my',
                'languageCode' => 'en',
            ]);

            if ($response->successful()) {
                $results = $results->merge(
                    collect($response->json('suggestions', []))
                        ->pluck('placePrediction')
                        ->filter()
                        ->take($limit)
                        ->map(function (array $prediction) use ($apiKey) {
                            $placeId = $prediction['placeId'] ?? null;

                            if (! $placeId) {
                                return null;
                            }

                            return $this->googlePlaceLocationDetails($placeId, $apiKey);
                        })
                        ->filter()
                );
            }

            return $results
                ->filter()
                ->unique(function (array $result) {
                    return $result['place_id']
                        ?? sprintf(
                            '%s:%s:%s',
                            $result['display_name'] ?? 'unknown',
                            $result['lat'] ?? '0',
                            $result['lon'] ?? '0'
                        );
                })
                ->values()
                ->take($limit)
                ->all();
        });
    }

    private function googleGeocodeSearch(string $query, int $limit, string $apiKey): array
    {
        $response = Http::timeout(12)->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $query,
            'components' => 'country:MY',
            'key' => $apiKey,
            'language' => 'en',
            'region' => 'my',
        ]);

        if (! $response->successful() || $response->json('status') !== 'OK') {
            return [];
        }

        return collect($response->json('results', []))
            ->take($limit)
            ->map(function (array $result) {
                return $this->normalizeGoogleGeocodeLocation($result);
            })
            ->filter()
            ->values()
            ->all();
    }

    private function googlePlaceLocationDetails(string $placeId, string $apiKey): ?array
    {
        $cacheKey = sprintf('owner.location.place.%s', $placeId);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($placeId, $apiKey) {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Goog-Api-Key' => $apiKey,
                'X-Goog-FieldMask' => 'id,displayName,formattedAddress,location,addressComponents',
            ])->timeout(12)->get(sprintf('https://places.googleapis.com/v1/places/%s', $placeId));

            if (! $response->successful()) {
                return null;
            }

            return $this->normalizeGooglePlaceLocation($response->json());
        });
    }

    private function googleReverseLocation(float $latitude, float $longitude): ?array
    {
        $apiKey = config('services.google_places.key');

        if (! $apiKey) {
            return null;
        }

        $cacheKey = sprintf('owner.location.reverse.%s.%s', round($latitude, 5), round($longitude, 5));

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($apiKey, $latitude, $longitude) {
            $response = Http::timeout(12)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => sprintf('%s,%s', $latitude, $longitude),
                'key' => $apiKey,
                'language' => 'en',
                'region' => 'my',
            ]);

            if (! $response->successful() || $response->json('status') !== 'OK') {
                return null;
            }

            $result = $response->json('results.0');

            return is_array($result)
                ? $this->normalizeGoogleGeocodeLocation($result)
                : null;
        });
    }

    private function normalizeGooglePlaceLocation(array $place): ?array
    {
        $latitude = data_get($place, 'location.latitude');
        $longitude = data_get($place, 'location.longitude');

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        $components = collect($place['addressComponents'] ?? []);
        $address = $this->googleAddressParts($components);

        return [
            'place_id' => $place['id'] ?? null,
            'display_name' => data_get($place, 'formattedAddress') ?: data_get($place, 'displayName.text'),
            'lat' => (float) $latitude,
            'lon' => (float) $longitude,
            'address' => $address,
        ];
    }

    private function normalizeGoogleGeocodeLocation(array $result): ?array
    {
        $location = $result['geometry']['location'] ?? null;

        if (! is_array($location) || ! isset($location['lat'], $location['lng'])) {
            return null;
        }

        $components = collect($result['address_components'] ?? []);
        $address = $this->googleAddressParts($components, true);

        return [
            'place_id' => $result['place_id'] ?? null,
            'display_name' => $result['formatted_address'] ?? null,
            'lat' => (float) $location['lat'],
            'lon' => (float) $location['lng'],
            'address' => $address,
        ];
    }

    private function googleAddressParts($components, bool $legacyShape = false): array
    {
        $find = function (array $types) use ($components, $legacyShape) {
            foreach ($components as $component) {
                $componentTypes = $component['types'] ?? [];

                if (! array_intersect($types, $componentTypes)) {
                    continue;
                }

                return $legacyShape
                    ? ($component['long_name'] ?? null)
                    : ($component['longText'] ?? null);
            }

            return null;
        };

        return [
            'house_number' => $find(['street_number', 'subpremise', 'premise']),
            'road' => $find(['route']),
            'city' => $find(['locality', 'postal_town', 'administrative_area_level_3', 'administrative_area_level_2']),
            'postcode' => $find(['postal_code']),
            'state' => $find(['administrative_area_level_1']),
        ];
    }
}
