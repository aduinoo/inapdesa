<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingChatController extends Controller
{
    public function customerIndex()
    {
        $userId = Auth::id();

        $threads = DB::table('bookings')
            ->join('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->join('users as owners', 'homestays.owner_id', '=', 'owners.id')
            ->leftJoin(DB::raw('(SELECT booking_id, MAX(created_at) AS latest_message_at FROM booking_messages GROUP BY booking_id) AS latest_messages'), 'bookings.booking_id', '=', 'latest_messages.booking_id')
            ->leftJoin('booking_messages as last_message', function ($join) {
                $join->on('bookings.booking_id', '=', 'last_message.booking_id')
                    ->on('latest_messages.latest_message_at', '=', 'last_message.created_at');
            })
            ->select(
                'bookings.booking_id',
                'bookings.check_in',
                'bookings.check_out',
                'bookings.booking_status',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'owners.name as other_party_name',
                'last_message.message as latest_message',
                'last_message.created_at as latest_message_at'
            )
            ->where('bookings.user_id', $userId)
            ->orderByRaw('COALESCE(latest_message_at, bookings.created_at) DESC')
            ->get()
            ->map(function ($thread) use ($userId) {
                $thread->unread_count = DB::table('booking_messages')
                    ->where('booking_id', $thread->booking_id)
                    ->where('sender_id', '!=', $userId)
                    ->where('is_read', 0)
                    ->count();

                return $thread;
            });

        return view('profile.shared.chat.index', [
            'layout' => 'profile.Customers.app_customers',
            'title' => 'Your chats',
            'subtitle' => 'Talk to homestay owners about your confirmed and upcoming stays.',
            'threads' => $threads,
            'showRoute' => 'user.messages.show',
            'dashboardRoute' => (int) Auth::user()->role === 3 ? route('owner.dashboard') : route('user.dashboard'),
            'backLabel' => 'Back to dashboard',
        ]);
    }

    public function ownerIndex()
    {
        $ownerId = Auth::id();

        $threads = DB::table('bookings')
            ->join('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->join('users as customers', 'bookings.user_id', '=', 'customers.id')
            ->leftJoin(DB::raw('(SELECT booking_id, MAX(created_at) AS latest_message_at FROM booking_messages GROUP BY booking_id) AS latest_messages'), 'bookings.booking_id', '=', 'latest_messages.booking_id')
            ->leftJoin('booking_messages as last_message', function ($join) {
                $join->on('bookings.booking_id', '=', 'last_message.booking_id')
                    ->on('latest_messages.latest_message_at', '=', 'last_message.created_at');
            })
            ->select(
                'bookings.booking_id',
                'bookings.check_in',
                'bookings.check_out',
                'bookings.booking_status',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'customers.name as other_party_name',
                'last_message.message as latest_message',
                'last_message.created_at as latest_message_at'
            )
            ->where('homestays.owner_id', $ownerId)
            ->orderByRaw('COALESCE(latest_message_at, bookings.created_at) DESC')
            ->get()
            ->map(function ($thread) use ($ownerId) {
                $thread->unread_count = DB::table('booking_messages')
                    ->where('booking_id', $thread->booking_id)
                    ->where('sender_id', '!=', $ownerId)
                    ->where('is_read', 0)
                    ->count();

                return $thread;
            });

        return view('profile.shared.chat.index', [
            'layout' => 'profile.homestayOwners.app_ownerHomestay',
            'title' => 'Guest chats',
            'subtitle' => 'Talk to customers who booked your homestays.',
            'threads' => $threads,
            'showRoute' => 'owner.messages.show',
            'dashboardRoute' => route('owner.dashboard'),
            'backLabel' => 'Back to dashboard',
        ]);
    }

    public function showForCustomer(int $booking)
    {
        return $this->showThread($booking, false);
    }

    public function showForOwner(int $booking)
    {
        return $this->showThread($booking, true);
    }

    public function storeForCustomer(Request $request, int $booking)
    {
        return $this->storeMessage($request, $booking, false);
    }

    public function storeForOwner(Request $request, int $booking)
    {
        return $this->storeMessage($request, $booking, true);
    }

    private function showThread(int $bookingId, bool $ownerView)
    {
        $context = $this->bookingContext($bookingId, $ownerView);

        DB::table('booking_messages')
            ->where('booking_id', $bookingId)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        $messages = DB::table('booking_messages')
            ->join('users', 'booking_messages.sender_id', '=', 'users.id')
            ->select(
                'booking_messages.message_id',
                'booking_messages.message',
                'booking_messages.sender_id',
                'booking_messages.created_at',
                'users.name as sender_name'
            )
            ->where('booking_messages.booking_id', $bookingId)
            ->orderBy('booking_messages.created_at')
            ->orderBy('booking_messages.message_id')
            ->get();

        return view('profile.shared.chat.thread', [
            'layout' => $ownerView ? 'profile.homestayOwners.app_ownerHomestay' : 'profile.Customers.app_customers',
            'booking' => $context,
            'messages' => $messages,
            'currentUserId' => Auth::id(),
            'storeRoute' => $ownerView ? route('owner.messages.store', $bookingId) : route('user.messages.store', $bookingId),
            'indexRoute' => $ownerView ? route('owner.messages.index') : route('user.messages.index'),
            'otherPartyLabel' => $ownerView ? 'Guest' : 'Owner',
        ]);
    }

    private function storeMessage(Request $request, int $bookingId, bool $ownerView)
    {
        $context = $this->bookingContext($bookingId, $ownerView);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        DB::table('booking_messages')->insert([
            'booking_id' => $bookingId,
            'sender_id' => Auth::id(),
            'message' => trim($validated['message']),
            'is_read' => 0,
            'created_at' => now(),
        ]);

        $recipientId = $ownerView ? $context->customer_id : $context->owner_id;

        DB::table('notifications')->insert([
            'user_id' => $recipientId,
            'title' => 'New chat message',
            'message' => Auth::user()->name . ' sent you a message about booking #' . $bookingId . '.',
            'is_read' => 0,
            'created_at' => now(),
        ]);

        return redirect()->back();
    }

    private function bookingContext(int $bookingId, bool $ownerView)
    {
        $booking = DB::table('bookings')
            ->join('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->join('users as customers', 'bookings.user_id', '=', 'customers.id')
            ->join('users as owners', 'homestays.owner_id', '=', 'owners.id')
            ->select(
                'bookings.booking_id',
                'bookings.user_id as customer_id',
                'homestays.owner_id as owner_id',
                'bookings.check_in',
                'bookings.check_out',
                'bookings.booking_status',
                'bookings.total_price',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'customers.name as customer_name',
                'owners.name as owner_name'
            )
            ->where('bookings.booking_id', $bookingId)
            ->first();

        abort_unless($booking, 404);

        if ($ownerView) {
            abort_unless((int) $booking->owner_id === (int) Auth::id(), 403);
        } else {
            abort_unless((int) $booking->customer_id === (int) Auth::id(), 403);
        }

        return $booking;
    }
}
