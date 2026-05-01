<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
{
    $totalUsers = DB::table('users')->count();
    $totalHomestays = DB::table('homestays')->count();
    $totalBookings = DB::table('bookings')->count();

    $pendingReports = DB::table('reports')
        ->where('status', 'pending')
        ->count();

    $users = DB::table('users')->get();

    return view('admin_dashboard', compact(
        'totalUsers',
        'totalHomestays',
        'totalBookings',
        'pendingReports',
        'users' // ✅ ADD THIS
    ));
}

}
