<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class BookingCancellationController extends Controller
{
    public function publicIndex(): View
    {
        return view('profile.landingPages.cancellation-options', [
            'bookings' => collect(),
            'isCustomerContext' => false,
            'tableReady' => $this->cancellationTableExists(),
        ]);
    }

    public function customerIndex(Request $request): View
    {
        $userId = Auth::id();

        $bookingsQuery = DB::table('bookings')
            ->join('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->select(
                'bookings.booking_id',
                'bookings.check_in',
                'bookings.check_out',
                'bookings.total_price',
                'bookings.booking_status',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state'
            )
            ->where('bookings.user_id', $userId)
            ->where('bookings.booking_status', 'confirmed')
            ->whereDate('bookings.check_in', '>=', now()->toDateString());

        if ($request->filled('booking_id')) {
            $bookingsQuery->where('bookings.booking_id', (int) $request->query('booking_id'));
        }

        $bookingsQuery->orderBy('bookings.check_in');

        if ($this->cancellationTableExists()) {
            $bookingsQuery
                ->leftJoin('booking_cancellation_requests as requests', 'bookings.booking_id', '=', 'requests.booking_id')
                ->addSelect(
                    'requests.status as cancellation_status',
                    'requests.requested_refund_amount',
                    'requests.host_decision_notes'
                );
        }

        $bookings = $bookingsQuery
            ->get()
            ->map(function ($booking) {
                $booking->cancellation_status = $booking->cancellation_status ?? null;
                $booking->requested_refund_amount = $booking->requested_refund_amount ?? null;
                $booking->host_decision_notes = $booking->host_decision_notes ?? null;
                $booking->refund = $this->refundPreview($booking->check_in, (float) $booking->total_price);

                return $booking;
            });

        $history = collect();

        if ($this->cancellationTableExists()) {
            $history = DB::table('booking_cancellation_requests')
                ->join('bookings', 'booking_cancellation_requests.booking_id', '=', 'bookings.booking_id')
                ->join('homestays', 'booking_cancellation_requests.homestay_id', '=', 'homestays.homestay_id')
                ->select(
                    'booking_cancellation_requests.*',
                    'bookings.check_in',
                    'bookings.check_out',
                    'bookings.total_price',
                    'bookings.booking_status',
                    'homestays.homestay_name',
                    'homestays.city',
                    'homestays.state'
                )
                ->where('booking_cancellation_requests.customer_id', $userId)
                ->orderByRaw("CASE WHEN booking_cancellation_requests.status = 'pending' THEN 0 ELSE 1 END")
                ->orderByDesc('booking_cancellation_requests.updated_at')
                ->orderByDesc('booking_cancellation_requests.created_at')
                ->get();
        }

        return view('profile.landingPages.cancellation-options', [
            'bookings' => $bookings,
            'history' => $history,
            'isCustomerContext' => true,
            'tableReady' => $this->cancellationTableExists(),
        ]);
    }

    public function store(Request $request, int $booking): RedirectResponse
    {
        if (! $this->cancellationTableExists()) {
            return back()->with('error', 'Cancellation requests are not available yet because the database table has not been created.');
        }

        $bookingRecord = DB::table('bookings')
            ->join('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->select(
                'bookings.booking_id',
                'bookings.user_id',
                'bookings.homestay_id',
                'bookings.check_in',
                'bookings.check_out',
                'bookings.total_price',
                'bookings.booking_status',
                'homestays.owner_id',
                'homestays.homestay_name'
            )
            ->where('bookings.booking_id', $booking)
            ->where('bookings.user_id', Auth::id())
            ->first();

        abort_unless($bookingRecord, 404);

        $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        abort_if($bookingRecord->booking_status !== 'confirmed', 422, 'Only confirmed bookings can be cancelled.');

        $existingRequest = DB::table('booking_cancellation_requests')
            ->where('booking_id', $bookingRecord->booking_id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'A cancellation request already exists for this booking.');
        }

        $refund = $this->refundPreview($bookingRecord->check_in, (float) $bookingRecord->total_price);

        DB::table('booking_cancellation_requests')->updateOrInsert(
            ['booking_id' => $bookingRecord->booking_id],
            [
                'customer_id' => Auth::id(),
                'owner_id' => $bookingRecord->owner_id,
                'homestay_id' => $bookingRecord->homestay_id,
                'reason' => trim($request->input('reason')),
                'days_before_check_in' => $refund['days_before_check_in'],
                'refund_rate' => $refund['refund_rate'],
                'requested_refund_amount' => $refund['refund_amount'],
                'status' => 'pending',
                'host_decision_notes' => null,
                'processed_by_host_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->notifyUser(
            (int) $bookingRecord->owner_id,
            'Cancellation request received',
            Auth::user()->name . ' requested cancellation for booking #' . $bookingRecord->booking_id . '.'
        );

        return back()->with('success', 'Your cancellation request has been sent to the host for approval.');
    }

    public function ownerIndex(): View
    {
        $requests = collect();

        if ($this->cancellationTableExists()) {
            $requests = DB::table('booking_cancellation_requests')
                ->join('bookings', 'booking_cancellation_requests.booking_id', '=', 'bookings.booking_id')
                ->join('homestays', 'booking_cancellation_requests.homestay_id', '=', 'homestays.homestay_id')
                ->join('users as customers', 'booking_cancellation_requests.customer_id', '=', 'customers.id')
                ->select(
                    'booking_cancellation_requests.*',
                    'bookings.check_in',
                    'bookings.check_out',
                    'bookings.total_price',
                    'homestays.homestay_name',
                    'customers.name as customer_name'
                )
                ->where('booking_cancellation_requests.owner_id', Auth::id())
                ->orderByRaw("CASE WHEN booking_cancellation_requests.status = 'pending' THEN 0 ELSE 1 END")
                ->orderByDesc('booking_cancellation_requests.created_at')
                ->get();
        }

        return view('profile.homestayOwners.cancellation_requests.index', [
            'requests' => $requests,
            'tableReady' => $this->cancellationTableExists(),
        ]);
    }

    public function ownerShow(int $request): View
    {
        abort_unless($this->cancellationTableExists(), 404);

        $requestRecord = $this->ownerRequestContext($request);

        return view('profile.homestayOwners.cancellation_requests.show', [
            'requestRecord' => $requestRecord,
        ]);
    }

    public function approve(Request $request, int $requestId): RedirectResponse
    {
        if (! $this->cancellationTableExists()) {
            return back()->with('error', 'Cancellation requests are not available yet because the database table has not been created.');
        }

        $requestRecord = $this->ownerRequestContext($requestId);

        abort_if($requestRecord->status !== 'pending', 422, 'This cancellation request has already been processed.');

        DB::beginTransaction();

        try {
            DB::table('booking_cancellation_requests')
                ->where('id', $requestRecord->id)
                ->update([
                    'status' => 'approved',
                    'host_decision_notes' => $request->input('host_decision_notes'),
                    'processed_by_host_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('bookings')
                ->where('booking_id', $requestRecord->booking_id)
                ->update([
                    'booking_status' => 'cancelled',
                ]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            return back()->with('error', 'Unable to approve the cancellation request right now.');
        }

        $this->notifyUser(
            (int) $requestRecord->customer_id,
            'Cancellation approved',
            'Your cancellation for booking #' . $requestRecord->booking_id . ' was approved by the host.'
        );

        return back()->with('success', 'Cancellation request approved.');
    }

    public function reject(Request $request, int $requestId): RedirectResponse
    {
        if (! $this->cancellationTableExists()) {
            return back()->with('error', 'Cancellation requests are not available yet because the database table has not been created.');
        }

        $requestRecord = $this->ownerRequestContext($requestId);

        abort_if($requestRecord->status !== 'pending', 422, 'This cancellation request has already been processed.');

        $request->validate([
            'host_decision_notes' => ['required', 'string', 'max:2000'],
        ]);

        DB::table('booking_cancellation_requests')
            ->where('id', $requestRecord->id)
            ->update([
                'status' => 'rejected',
                'host_decision_notes' => trim($request->input('host_decision_notes')),
                'processed_by_host_at' => now(),
                'updated_at' => now(),
            ]);

        $this->notifyUser(
            (int) $requestRecord->customer_id,
            'Cancellation rejected',
            'Your cancellation for booking #' . $requestRecord->booking_id . ' was rejected by the host.'
        );

        return back()->with('success', 'Cancellation request rejected.');
    }

    private function refundPreview(string $checkIn, float $totalPrice): array
    {
        $daysBefore = Carbon::today()->diffInDays(Carbon::parse($checkIn), false);

        if ($daysBefore >= 2) {
            $rate = 1.0;
        } elseif ($daysBefore === 1) {
            $rate = 0.5;
        } else {
            $rate = 0.0;
        }

        return [
            'days_before_check_in' => $daysBefore,
            'refund_rate' => $rate,
            'refund_amount' => round($totalPrice * $rate, 2),
        ];
    }

    private function ownerRequestContext(int $requestId)
    {
        $requestRecord = DB::table('booking_cancellation_requests')
            ->join('bookings', 'booking_cancellation_requests.booking_id', '=', 'bookings.booking_id')
            ->join('homestays', 'booking_cancellation_requests.homestay_id', '=', 'homestays.homestay_id')
            ->join('users as customers', 'booking_cancellation_requests.customer_id', '=', 'customers.id')
            ->select(
                'booking_cancellation_requests.*',
                'bookings.check_in',
                'bookings.check_out',
                'bookings.total_price',
                'homestays.homestay_name',
                'homestays.owner_id',
                'customers.name as customer_name',
                'customers.email as customer_email'
            )
            ->where('booking_cancellation_requests.id', $requestId)
            ->first();

        abort_unless($requestRecord, 404);
        abort_unless((int) $requestRecord->owner_id === (int) Auth::id(), 403);

        return $requestRecord;
    }

    private function notifyUser(int $userId, string $title, string $message): void
    {
        DB::table('notifications')->insert([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'is_read' => 0,
            'created_at' => now(),
        ]);
    }

    private function cancellationTableExists(): bool
    {
        static $exists;

        if ($exists === null) {
            $exists = Schema::hasTable('booking_cancellation_requests');
        }

        return $exists;
    }
}
