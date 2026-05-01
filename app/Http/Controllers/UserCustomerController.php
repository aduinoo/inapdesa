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

        $upcomingBookings = DB::table('bookings')
            ->where('user_id', $userId)
            ->whereDate('check_in', '>=', now())
            ->orderBy('check_in')
            ->get();

        $notifications = DB::table('notifications')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('profile.Customers.customers_dashboard', compact(
            'totalBookings',
            'upcomingBookings',
            'notifications'
        ));
    }
}
