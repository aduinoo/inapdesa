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

        $pendingOwnerApplications = DB::table('owner_applications')
            ->join('users', 'owner_applications.user_id', '=', 'users.id')
            ->where('owner_applications.status', 'pending')
            ->orderBy('owner_applications.created_at')
            ->select(
                'owner_applications.id',
                'owner_applications.phone_number',
                'owner_applications.city',
                'owner_applications.state',
                'owner_applications.created_at as applied_at',
                'users.name',
                'users.email'
            )
            ->get();

        return view('profile.admins.admin_dashboard', compact(
            'totalUsers',
            'totalHomestays',
            'totalBookings',
            'pendingReports',
            'users',
            'pendingOwnerApplications'
        ));
    }
}
