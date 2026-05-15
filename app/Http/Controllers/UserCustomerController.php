<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>=', now())
            ->count();

        $upcomingBookings = DB::table('bookings')
            ->leftJoin('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->select(
                'bookings.*',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state'
            )
            ->where('bookings.user_id', $userId)
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

        $wishlistCount = 0;

        $recommendedHomestays = DB::table('homestays')
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get(['homestay_id', 'homestay_name', 'city', 'state', 'base_price']);

        $notifications = DB::table('notifications')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('profile.Customers.customers_dashboard', compact(
            'totalBookings',
            'activeTrips',
            'upcomingBookings',
            'pastBookings',
            'wishlistCount',
            'recommendedHomestays',
            'notifications'
        ));
    }
}
