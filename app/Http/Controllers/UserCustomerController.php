<?php

namespace App\Http\Controllers;

use App\Models\RoomScan;
use App\Services\ToyyibPayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserCustomerController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();

        $totalBookings = DB::table('bookings')
            ->where('user_id', $userId)
            ->count();

        $activeTrips = DB::table('bookings')
            ->where('user_id', $userId)
            ->where('booking_status', 'confirmed')
            ->whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>=', now())
            ->count();

        $confirmedBookings = DB::table('bookings')
            ->where('user_id', $userId)
            ->where('booking_status', 'confirmed')
            ->count();

        $cancelledBookings = DB::table('bookings')
            ->where('user_id', $userId)
            ->where('booking_status', 'cancelled')
            ->count();

        $totalSpent = DB::table('bookings')
            ->where('user_id', $userId)
            ->where('booking_status', 'confirmed')
            ->sum('total_price');

        $upcomingBookings = DB::table('bookings')
            ->leftJoin('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->select(
                'bookings.*',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state'
            )
            ->where('bookings.user_id', $userId)
            ->where('bookings.booking_status', 'confirmed')
            ->whereDate('bookings.check_in', '>=', now())
            ->orderBy('bookings.check_in')
            ->limit(5)
            ->get();

        $pastBookings = DB::table('bookings')
            ->leftJoin('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->select(
                'bookings.*',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state'
            )
            ->where('bookings.user_id', $userId)
            ->whereDate('bookings.check_out', '<', now())
            ->orderByDesc('bookings.check_out')
            ->limit(5)
            ->get();

        $wishlistCount = DB::table('wishlists')
            ->where('user_id', $userId)
            ->count();

        $monthlyBookings = [];
        $monthlySpending = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $label = $monthStart->format('M');

            $monthlyBookings[$label] = DB::table('bookings')
                ->where('user_id', $userId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $monthlySpending[$label] = (float) DB::table('bookings')
                ->where('user_id', $userId)
                ->where('booking_status', 'confirmed')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_price');
        }

        $topDestinations = DB::table('bookings')
            ->leftJoin('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->select('homestays.city', DB::raw('COUNT(*) as visit_count'))
            ->where('bookings.user_id', $userId)
            ->where('bookings.booking_status', 'confirmed')
            ->groupBy('homestays.city')
            ->orderByDesc('visit_count')
            ->limit(5)
            ->get();

        $recentNotifications = DB::table('notifications')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $unreadNotifications = DB::table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->count();

        $recommendedHomestays = DB::table('homestays')
            ->leftJoin('homestay_images', function ($join) {
                $join->on('homestays.homestay_id', '=', 'homestay_images.homestay_id')
                    ->where('homestay_images.image_type', '=', 'gallery');
            })
            ->leftJoin('reviews', 'homestays.homestay_id', '=', 'reviews.homestay_id')
            ->select(
                'homestays.homestay_id',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'homestays.base_price',
                DB::raw('MIN(homestay_images.image_path) as primary_image'),
                DB::raw('COALESCE(AVG(reviews.rating), 0) as avg_rating'),
                DB::raw('COUNT(reviews.review_id) as review_count')
            )
            ->where('homestays.status', 'active')
            ->groupBy(
                'homestays.homestay_id',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'homestays.base_price'
            )
            ->orderByDesc('avg_rating')
            ->limit(4)
            ->get();

        $aiSummary = $this->generateAiSummary(
            $totalBookings,
            $confirmedBookings,
            $cancelledBookings,
            $activeTrips,
            $totalSpent,
            $monthlyBookings,
            $monthlySpending,
            $topDestinations
        );

        return view('profile.Customers.customers_dashboard', compact(
            'totalBookings',
            'confirmedBookings',
            'cancelledBookings',
            'activeTrips',
            'totalSpent',
            'upcomingBookings',
            'pastBookings',
            'wishlistCount',
            'monthlyBookings',
            'monthlySpending',
            'topDestinations',
            'recentNotifications',
            'unreadNotifications',
            'recommendedHomestays',
            'aiSummary'
        ));
    }

    public function bookings()
    {
        $userId = Auth::id();

        $activeBookings = DB::table('bookings')
            ->leftJoin('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->select(
                'bookings.*',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state'
            )
            ->where('bookings.user_id', $userId)
            ->where('bookings.booking_status', 'confirmed')
            ->whereDate('bookings.check_in', '<=', now())
            ->whereDate('bookings.check_out', '>=', now())
            ->orderBy('bookings.check_out')
            ->get();

        $upcomingBookings = DB::table('bookings')
            ->leftJoin('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->select(
                'bookings.*',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state'
            )
            ->where('bookings.user_id', $userId)
            ->where('bookings.booking_status', 'confirmed')
            ->whereDate('bookings.check_in', '>=', now())
            ->orderBy('bookings.check_in')
            ->get();

        $pastBookings = DB::table('bookings')
            ->leftJoin('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->select(
                'bookings.*',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state'
            )
            ->where('bookings.user_id', $userId)
            ->whereDate('bookings.check_out', '<', now())
            ->orderByDesc('bookings.check_out')
            ->get();

        $cancelledBookings = DB::table('bookings')
            ->leftJoin('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->select(
                'bookings.*',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state'
            )
            ->where('bookings.user_id', $userId)
            ->where('bookings.booking_status', 'cancelled')
            ->orderByDesc('bookings.created_at')
            ->get();

        return view('profile.Customers.bookings', compact(
            'activeBookings',
            'upcomingBookings',
            'pastBookings',
            'cancelledBookings'
        ));
    }

    public function wishlist()
    {
        $userId = Auth::id();

        $wishlisted = DB::table('wishlists')
            ->join('homestays', 'wishlists.homestay_id', '=', 'homestays.homestay_id')
            ->leftJoin('homestay_images', function ($join) {
                $join->on('wishlists.homestay_id', '=', 'homestay_images.homestay_id')
                    ->where('homestay_images.image_type', '=', 'gallery');
            })
            ->leftJoin('reviews', 'wishlists.homestay_id', '=', 'reviews.homestay_id')
            ->select(
                'homestays.homestay_id',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'homestays.base_price',
                'homestays.max_guest',
                'homestays.description',
                'wishlists.created_at as saved_at',
                DB::raw('MIN(homestay_images.image_path) as primary_image'),
                DB::raw('COALESCE(AVG(reviews.rating), 0) as avg_rating'),
                DB::raw('COUNT(reviews.review_id) as review_count')
            )
            ->where('wishlists.user_id', $userId)
            ->where('homestays.status', 'active')
            ->groupBy(
                'homestays.homestay_id',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'homestays.base_price',
                'homestays.max_guest',
                'homestays.description',
                'wishlists.created_at'
            )
            ->orderByDesc('wishlists.created_at')
            ->get();

        $wishlistIds = $wishlisted->pluck('homestay_id')->toArray();

        $recommendedHomestays = DB::table('homestays')
            ->leftJoin('homestay_images', function ($join) {
                $join->on('homestays.homestay_id', '=', 'homestay_images.homestay_id')
                    ->where('homestay_images.image_type', '=', 'gallery');
            })
            ->leftJoin('reviews', 'homestays.homestay_id', '=', 'reviews.homestay_id')
            ->select(
                'homestays.homestay_id',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'homestays.base_price',
                DB::raw('MIN(homestay_images.image_path) as primary_image'),
                DB::raw('COALESCE(AVG(reviews.rating), 0) as avg_rating'),
                DB::raw('COUNT(reviews.review_id) as review_count')
            )
            ->where('homestays.status', 'active')
            ->when(!empty($wishlistIds), function ($q) use ($wishlistIds) {
                $q->whereNotIn('homestays.homestay_id', $wishlistIds);
            })
            ->groupBy(
                'homestays.homestay_id',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'homestays.base_price'
            )
            ->orderByDesc('avg_rating')
            ->limit(6)
            ->get();

        return view('profile.Customers.wishlist', compact(
            'wishlisted',
            'recommendedHomestays'
        ));
    }

    public function toggleWishlist(int $homestayId)
    {
        $userId = Auth::id();
        $exists = DB::table('wishlists')
            ->where('user_id', $userId)
            ->where('homestay_id', $homestayId)
            ->exists();

        if ($exists) {
            DB::table('wishlists')
                ->where('user_id', $userId)
                ->where('homestay_id', $homestayId)
                ->delete();

            return back()->with('success', 'Removed from wishlist.');
        }

        DB::table('wishlists')->insert([
            'user_id' => $userId,
            'homestay_id' => $homestayId,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Added to wishlist.');
    }

    private function generateAiSummary(
        int $totalBookings,
        int $confirmedBookings,
        int $cancelledBookings,
        int $activeTrips,
        float $totalSpent,
        array $monthlyBookings,
        array $monthlySpending,
        $topDestinations
    ): array {
        $insights = [];
        $trends = [];

        if ($totalBookings === 0) {
            return [
                'headline' => 'Welcome to your travel dashboard',
                'summary' => 'You haven\'t made any bookings yet. Start exploring homestays across Malaysia to kick off your first village stay experience.',
                'insights' => [],
                'trends' => [],
                'recommendation' => 'Browse our curated homestays to find your perfect getaway.',
            ];
        }

        $confirmationRate = $totalBookings > 0
            ? round(($confirmedBookings / $totalBookings) * 100, 1)
            : 0;

        $insights[] = [
            'icon' => 'check-circle',
            'label' => 'Booking reliability',
            'value' => $confirmationRate . '%',
            'detail' => $confirmationRate >= 80
                ? 'Excellent — most of your bookings go through as planned.'
                : ($confirmationRate >= 50
                    ? 'Fair — some cancellations, but still a solid track record.'
                    : 'Low — you\'ve had several cancellations. Consider checking cancellation policies before booking.'),
        ];

        $insights[] = [
            'icon' => 'wallet',
            'label' => 'Total investment',
            'value' => 'RM ' . number_format($totalSpent, 0),
            'detail' => $totalSpent > 0
                ? 'Across ' . $confirmedBookings . ' confirmed stays.'
                : 'No completed stays yet.',
        ];

        if ($activeTrips > 0) {
            $insights[] = [
                'icon' => 'map-pin',
                'label' => 'Currently staying',
                'value' => $activeTrips . ' trip(s)',
                'detail' => 'You have an active stay right now. Enjoy your village experience!',
            ];
        }

        $spendingValues = array_values($monthlySpending);
        $bookingValues = array_values($monthlyBookings);
        $hasSpendingTrend = count($spendingValues) >= 3 && max($spendingValues) > 0;

        if ($hasSpendingTrend) {
            $recent3 = array_slice($spendingValues, -3);
            $earlier3 = array_slice($spendingValues, 0, 3);
            $recentAvg = array_sum($recent3) / 3;
            $earlierAvg = array_sum($earlier3) / 3;

            if ($recentAvg > $earlierAvg && $earlierAvg > 0) {
                $percentIncrease = round(((($recentAvg - $earlierAvg) / $earlierAvg) * 100), 0);
                $trends[] = [
                    'direction' => 'up',
                    'label' => 'Spending trend',
                    'detail' => 'Your monthly spend is up ' . $percentIncrease . '% compared to 3 months ago. You\'re booking more or choosing premium stays.',
                ];
            } elseif ($recentAvg < $earlierAvg && $earlierAvg > 0) {
                $trends[] = [
                    'direction' => 'down',
                    'label' => 'Spending trend',
                    'detail' => 'Your spending has decreased recently. Perhaps you\'re finding better deals or traveling less.',
                ];
            } else {
                $trends[] = [
                    'direction' => 'stable',
                    'label' => 'Spending trend',
                    'detail' => 'Your spending has been steady over the last 6 months.',
                ];
            }
        }

        $peakMonth = '';
        $peakBookings = 0;
        foreach ($monthlyBookings as $month => $count) {
            if ($count > $peakBookings) {
                $peakBookings = $count;
                $peakMonth = $month;
            }
        }

        if ($peakBookings > 0) {
            $trends[] = [
                'direction' => 'peak',
                'label' => 'Peak booking month',
                'detail' => $peakMonth . ' was your busiest month with ' . $peakBookings . ' booking(s).',
            ];
        }

        if ($topDestinations && $topDestinations->count() > 0) {
            $topCity = $topDestinations->first()->city ?? 'Unknown';
            $visitCount = $topDestinations->first()->visit_count ?? 0;
            $trends[] = [
                'direction' => 'favorite',
                'label' => 'Favorite destination',
                'detail' => $topCity . ' is your go-to — ' . $visitCount . ' visit(s) so far.',
            ];
        }

        $headline = $activeTrips > 0
            ? 'You\'re on an active trip right now'
            : ($confirmedBookings > 0
                ? 'Your travel patterns look great'
                : 'Your dashboard is ready');

        $summaryParts = [];
        if ($confirmationRate > 0) {
            $summaryParts[] = $confirmationRate . '% of your bookings confirmed successfully';
        }
        if ($totalSpent > 0) {
            $summaryParts[] = 'RM ' . number_format($totalSpent, 0) . ' invested in village stays';
        }
        if ($activeTrips > 0) {
            $summaryParts[] = $activeTrips . ' active trip(s) right now';
        }

        $summary = !empty($summaryParts)
            ? implode('. ', $summaryParts) . '.'
            : 'Start your first booking to see personalized insights.';

        $recommendation = $confirmedBookings >= 3
            ? 'You\'re a regular traveler! Try exploring a new destination for your next stay.'
            : ($confirmedBookings >= 1
                ? 'Nice start! Check our recommendations below for your next adventure.'
                : 'Browse homestays and book your first village stay today.');

        return [
            'headline' => $headline,
            'summary' => $summary,
            'insights' => $insights,
            'trends' => $trends,
            'recommendation' => $recommendation,
        ];
    }

    public function showHomestay(int $homestay)
    {
        [$homestay, $images, $amenities] = $this->loadHomestayDetails($homestay);

        $blockedDates = DB::table('bookings')
            ->where('homestay_id', $homestay->homestay_id)
            ->where('booking_status', '!=', 'cancelled')
            ->whereDate('check_out', '>=', today())
            ->orderBy('check_in')
            ->get(['check_in', 'check_out']);

        $panoramaScan = RoomScan::query()
            ->where('homestay_id', $homestay->homestay_id)
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->get()
            ->first(fn (RoomScan $scan) => ($scan->metadata['scan_type'] ?? null) === 'panorama_360' && $this->scanFirstPhotoPath($scan));

        return view('profile.Customers.booking.show', [
            'homestay' => $homestay,
            'images' => $images,
            'amenities' => $amenities,
            'localRecommendations' => $this->buildLocalRecommendations($homestay),
            'transportInfo' => $this->nearbyTransport((float) $homestay->latitude, (float) $homestay->longitude),
            'blockedDates' => $blockedDates->map(function ($range) {
                return (object) [
                    'check_in' => Carbon::parse($range->check_in)->toDateString(),
                    'check_out' => Carbon::parse($range->check_out)->toDateString(),
                ];
            }),
            'panoramaUrl' => $panoramaScan
                ? route('user.homestays.panorama', ['homestay' => $homestay->homestay_id, 'scan' => $panoramaScan->id])
                : null,
            'hasPanorama' => (bool) $panoramaScan,
        ]);
    }

    public function panorama(int $homestay, int $scan)
    {
        $homestayRecord = DB::table('homestays')
            ->where('homestay_id', $homestay)
            ->where('status', 'active')
            ->first(['homestay_id']);

        abort_unless($homestayRecord, 404);

        $roomScan = RoomScan::query()
            ->where('id', $scan)
            ->where('homestay_id', $homestayRecord->homestay_id)
            ->where('status', 'completed')
            ->first();

        abort_unless($roomScan, 404);
        abort_unless(($roomScan->metadata['scan_type'] ?? null) === 'panorama_360', 404);

        $path = $this->scanFirstPhotoPath($roomScan);

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    public function prepareBooking(Request $request, int $homestay)
    {
        [$homestay] = $this->loadHomestayDetails($homestay);

        if ((int) Auth::user()->role === 3 && (int) $homestay->owner_id === (int) Auth::id()) {
            return back()->with('error', 'You cannot book your own homestay listing.');
        }

        $validated = $request->validate([
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:' . (int) $homestay->max_guest],
        ], [
            'guest_count.max' => 'This homestay only allows up to ' . (int) $homestay->max_guest . ' guests.',
        ]);

        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);

        if ($this->hasBookingConflict($homestay->homestay_id, $checkIn->toDateString(), $checkOut->toDateString())) {
            return back()
                ->withInput()
                ->withErrors([
                    'check_in' => 'Those dates are already booked. Please choose another stay period.',
                ]);
        }

        $pricing = $this->calculatePricing((float) $homestay->base_price, $checkIn, $checkOut);

        session([
            'pending_booking' => [
                'user_id' => Auth::id(),
                'homestay_id' => $homestay->homestay_id,
                'homestay_name' => $homestay->homestay_name,
                'location' => trim(collect([$homestay->city, $homestay->state])->filter()->implode(', ')) ?: 'Malaysia',
                'image' => $homestay->primary_image,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'guest_count' => (int) $validated['guest_count'],
                'price_per_night' => (float) $homestay->base_price,
                'nights' => $pricing['nights'],
                'subtotal' => $pricing['subtotal'],
                'total' => $pricing['total'],
            ],
        ]);

        return redirect()->route('user.bookings.payment');
    }

    public function payment()
    {
        $pendingBooking = session('pending_booking');

        if (! $pendingBooking || (int) $pendingBooking['user_id'] !== (int) Auth::id()) {
            return redirect()
                ->to($this->guestDashboardRoute())
                ->with('error', 'Please start your booking from a homestay page first.');
        }

        return view('profile.Customers.booking.payment', [
            'pendingBooking' => $pendingBooking,
        ]);
    }

    public function processPayment(Request $request)
    {
        $pendingBooking = session('pending_booking');

        if (! $pendingBooking || (int) $pendingBooking['user_id'] !== (int) Auth::id()) {
            return redirect()
                ->to($this->guestDashboardRoute())
                ->with('error', 'Your booking session expired. Please try booking again.');
        }

        $validated = $request->validate([
            'payment_channel' => ['required', 'in:0'],
        ]);

        $user = Auth::user();
        $orderId = 'INAP-' . strtoupper(Str::random(10));

        session(['pending_booking.order_id' => $orderId]);

        try {
            $toyyibPay = new ToyyibPayService();
            $result = $toyyibPay->createBill([
                'billName' => $pendingBooking['homestay_name'],
                'billDescription' => sprintf(
                    'Stay at %s from %s to %s',
                    $pendingBooking['homestay_name'],
                    Carbon::parse($pendingBooking['check_in'])->format('d/m/Y'),
                    Carbon::parse($pendingBooking['check_out'])->format('d/m/Y')
                ),
                'billAmount' => $pendingBooking['total'],
                'billReturnUrl' => route('payment.toyyibpay.return'),
                'billCallbackUrl' => route('payment.toyyibpay.callback'),
                'billExternalReferenceNo' => $orderId,
                'billTo' => $user->name,
                'billEmail' => $user->email,
                'billPhone' => $user->phone ?? '0123456789',
                'billPaymentChannel' => $validated['payment_channel'],
            ]);

            session(['pending_booking.bill_code' => $result['billCode']]);

            return redirect()->away($result['paymentUrl']);
        } catch (\Exception $e) {
            session()->forget('pending_booking');

            return redirect()
                ->to($this->guestDashboardRoute())
                ->with('error', 'Payment gateway is temporarily unavailable. Please try again later.');
        }
    }

    public function toyyibpayReturn(Request $request)
    {
        $statusId = $request->query('status_id');
        $billCode = $request->query('billcode');

        $pendingBooking = session('pending_booking');

        if ($pendingBooking && (int) $pendingBooking['user_id'] !== (int) Auth::id()) {
            session()->forget('pending_booking');

            return redirect()
                ->to($this->guestDashboardRoute())
                ->with('error', 'Payment session mismatch. Please try booking again.');
        }

        if ($pendingBooking && $billCode && $pendingBooking['bill_code'] !== $billCode) {
            session()->forget('pending_booking');

            return redirect()
                ->to($this->guestDashboardRoute())
                ->with('error', 'Payment verification failed. Please try booking again.');
        }

        if ($statusId === '1') {
            if ($pendingBooking) {
                try {
                    $bookingId = $this->createBooking($pendingBooking);
                    session()->forget('pending_booking');

                    return redirect()->route('user.bookings.confirmed', $bookingId);
                } catch (\Exception $e) {
                    session()->forget('pending_booking');

                    return redirect()
                        ->to($this->guestDashboardRoute())
                        ->with('error', 'Booking could not be completed. Please contact support.');
                }
            }

            $existingBooking = DB::table('bookings')
                ->where('user_id', Auth::id())
                ->where('booking_status', 'confirmed')
                ->whereDate('created_at', '>=', now()->subMinutes(30))
                ->orderByDesc('created_at')
                ->first();

            if ($existingBooking) {
                return redirect()->route('user.bookings.confirmed', $existingBooking->booking_id);
            }

            return redirect()
                ->to($this->guestDashboardRoute())
                ->with('error', 'Payment successful but we could not retrieve your booking details. Please contact support.');
        }

        if ($statusId === '2') {
            return redirect()
                ->to($this->guestDashboardRoute())
                ->with('error', 'Your payment is still processing. We will notify you once it is confirmed.');
        }

        session()->forget('pending_booking');

        return redirect()
            ->route('home-page')
            ->with('error', 'Payment was not successful. Please try booking again.');
    }

    public function toyyibpayCallback(Request $request)
    {
        $status = $request->input('status');
        $orderId = $request->input('order_id');
        $refno = $request->input('refno');
        $receivedHash = $request->input('hash');

        if (! $status || ! $orderId || ! $refno || ! $receivedHash) {
            return response('Missing parameters', 400);
        }

        $toyyibPay = new ToyyibPayService();

        if (! $toyyibPay->verifyCallbackHash((string) $status, $orderId, $refno, $receivedHash)) {
            return response('Invalid hash', 400);
        }

        if ($status === '1') {
            DB::table('bookings')
                ->where('booking_status', 'pending_payment')
                ->where('created_at', '>=', now()->subMinutes(30))
                ->orderByDesc('created_at')
                ->limit(1)
                ->update(['booking_status' => 'confirmed']);
        }

        return response('OK');
    }

    private function createBooking(array $pendingBooking): int
    {
        [$homestay] = $this->loadHomestayDetails((int) $pendingBooking['homestay_id']);

        if ((int) Auth::user()->role === 3 && (int) $homestay->owner_id === (int) Auth::id()) {
            throw new \Exception('Cannot book your own homestay.');
        }

        if ($this->hasBookingConflict(
            $homestay->homestay_id,
            $pendingBooking['check_in'],
            $pendingBooking['check_out']
        )) {
            throw new \Exception('Dates are no longer available.');
        }

        DB::beginTransaction();

        try {
            $bookingId = DB::table('bookings')->insertGetId([
                'user_id' => Auth::id(),
                'homestay_id' => $homestay->homestay_id,
                'check_in' => $pendingBooking['check_in'],
                'check_out' => $pendingBooking['check_out'],
                'total_price' => $pendingBooking['total'],
                'booking_status' => 'confirmed',
                'created_at' => now(),
            ]);

            DB::table('notifications')->insert([
                'user_id' => Auth::id(),
                'title' => 'Booking confirmed',
                'message' => sprintf(
                    'Your stay at %s from %s to %s has been confirmed.',
                    $homestay->homestay_name,
                    Carbon::parse($pendingBooking['check_in'])->format('d M Y'),
                    Carbon::parse($pendingBooking['check_out'])->format('d M Y')
                ),
                'is_read' => 0,
                'created_at' => now(),
            ]);

            DB::commit();

            return $bookingId;
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function confirmed(int $booking)
    {
        $booking = DB::table('bookings')
            ->join('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->leftJoin('homestay_images', function ($join) {
                $join->on('bookings.homestay_id', '=', 'homestay_images.homestay_id')
                    ->where('homestay_images.image_type', '=', 'gallery');
            })
            ->select(
                'bookings.*',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'homestays.base_price',
                DB::raw('MIN(homestay_images.image_path) as image_path')
            )
            ->where('bookings.booking_id', $booking)
            ->where('bookings.user_id', Auth::id())
            ->groupBy(
                'bookings.booking_id',
                'bookings.user_id',
                'bookings.homestay_id',
                'bookings.check_in',
                'bookings.check_out',
                'bookings.total_price',
                'bookings.booking_status',
                'bookings.created_at',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'homestays.base_price'
            )
            ->first();

        abort_unless($booking, 404);

        $checkIn = Carbon::parse($booking->check_in);
        $checkOut = Carbon::parse($booking->check_out);

        return view('profile.Customers.booking.confirmed', [
            'booking' => $booking,
            'nights' => $checkIn->diffInDays($checkOut),
            'dashboardRoute' => $this->guestDashboardRoute(),
        ]);
    }

    private function loadHomestayDetails(int $homestayId): array
    {
        $homestay = DB::table('homestays')
            ->where('homestay_id', $homestayId)
            ->where('status', 'active')
            ->first();

        abort_unless($homestay, 404);

        $images = DB::table('homestay_images')
            ->where('homestay_id', $homestayId)
            ->orderBy('created_at')
            ->get();

        $amenities = DB::table('homestay_amenities')
            ->join('amenities', 'homestay_amenities.amenity_id', '=', 'amenities.amenity_id')
            ->where('homestay_amenities.homestay_id', $homestayId)
            ->orderBy('amenities.amenity_name')
            ->pluck('amenities.amenity_name');

        $homestay->primary_image = $images->first()->image_path ?? 'assets/images/homepage/home.jpg';

        return [$homestay, $images, $amenities];
    }

    private function buildLocalRecommendations(object $homestay): array
    {
        if (! is_numeric($homestay->latitude ?? null) || ! is_numeric($homestay->longitude ?? null)) {
            return [
                'available' => false,
                'summary' => 'Nearby recommendations will appear once this homestay location pin is added.',
                'tours' => [],
                'food' => [],
            ];
        }

        $latitude = (float) $homestay->latitude;
        $longitude = (float) $homestay->longitude;
        $cityName = trim((string) ($homestay->city ?? ''));
        $placeName = trim((string) ($homestay->homestay_name ?? 'this stay'));
        $tours = $this->nearbyRecommendedTours($latitude, $longitude);
        $food = $this->nearbyFoodSpots($latitude, $longitude);
        $tourLead = $tours[0]['name'] ?? null;
        $foodLead = $food[0]['name'] ?? null;

        $summaryParts = array_values(array_filter([
            $tourLead ? 'Best first stop: ' . $tourLead : null,
            $foodLead ? 'Good food pick: ' . $foodLead : null,
        ]));

        return [
            'available' => ! empty($tours) || ! empty($food),
            'summary' => ! empty($summaryParts)
                ? implode('  ', $summaryParts)
                : 'AI suggestions for ' . ($cityName !== '' ? $cityName : $placeName) . ' are still being gathered.',
            'tours' => array_slice($tours, 0, 10),
            'food' => array_slice($food, 0, 10),
        ];
    }

    private function nearbyRecommendedTours(float $latitude, float $longitude): array
    {
        $apiKey = config('services.google_places.key');
        if (! $apiKey) {
            return [];
        }

        $cacheKey = sprintf('stay.recommendations.tours.v3.%s.%s', round($latitude, 3), round($longitude, 3));

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($apiKey, $latitude, $longitude) {
            return collect($this->searchNearbyGooglePlaces(
                $apiKey,
                $latitude,
                $longitude,
                ['tourist_attraction', 'museum', 'historical_landmark', 'art_gallery', 'amusement_park'],
                18000,
                12,
                'places.id,places.displayName,places.formattedAddress,places.location,places.googleMapsUri,places.primaryType,places.rating,places.photos'
            ))
                ->map(fn (array $place) => $this->normalizeRecommendationPlace($place, $latitude, $longitude, 'tour', $apiKey))
                ->filter()
                ->sortBy([
                    ['distance', 'asc'],
                    ['rating', 'desc'],
                ])
                ->values()
                ->all();
        });
    }

    private function nearbyFoodSpots(float $latitude, float $longitude): array
    {
        $apiKey = config('services.google_places.key');
        if (! $apiKey) {
            return [];
        }

        $cacheKey = sprintf('stay.recommendations.food.v3.%s.%s', round($latitude, 3), round($longitude, 3));

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($apiKey, $latitude, $longitude) {
            return collect($this->searchNearbyGooglePlaces(
                $apiKey,
                $latitude,
                $longitude,
                ['restaurant', 'cafe', 'meal_takeaway'],
                6000,
                12,
                'places.id,places.displayName,places.formattedAddress,places.location,places.googleMapsUri,places.primaryType,places.rating,places.photos'
            ))
                ->map(fn (array $place) => $this->normalizeRecommendationPlace($place, $latitude, $longitude, 'food', $apiKey))
                ->filter()
                ->sortBy([
                    ['distance', 'asc'],
                    ['rating', 'desc'],
                ])
                ->values()
                ->all();
        });
    }

    private function nearbyTransport(float $latitude, float $longitude): array
    {
        $apiKey = config('services.google_places.key');
        if (! $apiKey) {
            return $this->emptyTransportInfo();
        }

        $cacheKey = sprintf('stay.recommendations.transport.v1.%s.%s', round($latitude, 3), round($longitude, 3));

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($apiKey, $latitude, $longitude) {
            $trainPlaces = $this->searchNearbyGooglePlaces(
                $apiKey,
                $latitude,
                $longitude,
                ['train_station', 'light_rail_station', 'subway_station', 'transit_station'],
                5000,
                1,
                'places.id,places.displayName,places.formattedAddress,places.location,places.googleMapsUri,places.primaryType'
            );

            $busPlaces = $this->searchNearbyGooglePlaces(
                $apiKey,
                $latitude,
                $longitude,
                ['bus_station', 'bus_stop', 'transit_station'],
                3000,
                4,
                'places.id,places.displayName,places.formattedAddress,places.location,places.googleMapsUri,places.primaryType'
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

            $nearestPoint = collect(array_filter(array_merge(
                $nearestTrainStation ? [$nearestTrainStation] : [],
                $busStops
            )))->sortBy('distance')->first();

            return [
                'nearest_train_station' => $nearestTrainStation,
                'bus_stops' => $busStops,
                'nearest_point' => $nearestPoint,
                'available' => (bool) ($nearestTrainStation || ! empty($busStops)),
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
        string $fieldMask
    ): array {
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $apiKey,
            'X-Goog-FieldMask' => $fieldMask,
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

    private function normalizeRecommendationPlace(array $place, float $originLat, float $originLon, string $kind, string $apiKey): ?array
    {
        $lat = data_get($place, 'location.latitude');
        $lon = data_get($place, 'location.longitude');

        if (! is_numeric($lat) || ! is_numeric($lon)) {
            return null;
        }

        $name = trim((string) data_get($place, 'displayName.text', $kind === 'food' ? 'Nearby food spot' : 'Nearby attraction'));
        $address = trim((string) data_get($place, 'formattedAddress', ''));
        $typeLabel = $this->formatPlaceType((string) data_get($place, 'primaryType', $kind === 'food' ? 'restaurant' : 'tourist_attraction'));
        $distance = round($this->distanceInKm($originLat, $originLon, (float) $lat, (float) $lon), 1);
        $rating = is_numeric(data_get($place, 'rating')) ? (float) data_get($place, 'rating') : null;
        $photoName = data_get(collect(data_get($place, 'photos', []))->first(), 'name');

        return [
            'name' => $name !== '' ? $name : ($kind === 'food' ? 'Nearby food spot' : 'Nearby attraction'),
            'type' => $typeLabel,
            'address' => $address,
            'distance' => $distance,
            'rating' => $rating,
            'maps_url' => data_get($place, 'googleMapsUri') ?: $this->googleMapsSearchUrl((float) $lat, (float) $lon),
            'reason' => $this->buildRecommendationReason($kind, $typeLabel, $distance, $rating),
            'image' => $this->googlePlacePhotoUrl($photoName, $apiKey, 960)
                ?: asset($kind === 'food' ? 'assets/images/attractions/food.jpg' : 'assets/images/attractions/nature.jpg'),
        ];
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
            'maps_url' => data_get($place, 'googleMapsUri') ?: $this->googleMapsSearchUrl((float) $lat, (float) $lon),
            'address' => trim((string) data_get($place, 'formattedAddress', '')),
        ];
    }

    private function emptyTransportInfo(): array
    {
        return [
            'nearest_train_station' => null,
            'bus_stops' => [],
            'nearest_point' => null,
            'available' => false,
        ];
    }

    private function buildRecommendationReason(string $kind, string $typeLabel, float $distance, ?float $rating): string
    {
        $distanceText = $distance <= 0.3
            ? 'an easy short walk'
            : ($distance <= 2 ? 'a quick ride away' : 'worth the short trip');

        $ratingText = $rating ? ' with a ' . number_format($rating, 1) . '/5 rating' : '';
        $prefix = $kind === 'food' ? 'Great for a nearby bite' : 'Nice pick for exploring the area';

        return sprintf('%s, this %s is %s%s.', $prefix, strtolower($typeLabel), $distanceText, $ratingText);
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

    private function formatPlaceType(string $type): string
    {
        return Str::of($type)
            ->replace('_', ' ')
            ->title()
            ->value();
    }

    private function googleMapsSearchUrl(float $latitude, float $longitude): string
    {
        return sprintf('https://www.google.com/maps/search/?api=1&query=%s,%s', $latitude, $longitude);
    }

    private function googlePlacePhotoUrl(?string $photoName, string $apiKey, int $maxWidth = 960): ?string
    {
        if (! is_string($photoName) || trim($photoName) === '') {
            return null;
        }

        $cacheKey = 'stay.place.photo.' . sha1($photoName . '|' . $maxWidth);
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

    private function hasBookingConflict(int $homestayId, string $checkIn, string $checkOut): bool
    {
        return DB::table('bookings')
            ->where('homestay_id', $homestayId)
            ->where('booking_status', '!=', 'cancelled')
            ->whereDate('check_in', '<', $checkOut)
            ->whereDate('check_out', '>', $checkIn)
            ->exists();
    }

    private function calculatePricing(float $pricePerNight, Carbon $checkIn, Carbon $checkOut): array
    {
        $nights = max(1, $checkIn->diffInDays($checkOut));
        $subtotal = round($pricePerNight * $nights, 2);
        $total = $subtotal;

        return [
            'nights' => $nights,
            'subtotal' => $subtotal,
            'total' => $total,
        ];
    }

    private function scanFirstPhotoPath(RoomScan $scan): ?string
    {
        $files = collect(Storage::disk('local')->files("scans/{$scan->folder}/images"))
            ->filter(fn (string $path) => File::extension($path) !== '')
            ->sort()
            ->values();

        return $files->first();
    }

    private function guestDashboardRoute(): string
    {
        return (int) Auth::user()->role === 3
            ? route('owner.dashboard')
            : route('user.dashboard');
    }
}
