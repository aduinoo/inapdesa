<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class NeighbourhoodReportController extends Controller
{
    public function publicIndex(): View
    {
        return view('profile.landingPages.neighbourhood-concerns', [
            'bookings' => collect(),
            'isCustomerContext' => false,
        ]);
    }

    public function customerIndex(): View
    {
        $bookings = DB::table('bookings')
            ->join('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->leftJoin('reports', function ($join) {
                $join->on('bookings.booking_id', '=', 'reports.booking_id');

                if ($this->reportsTableHas('customer_id')) {
                    $join->where('reports.customer_id', '=', Auth::id());
                } elseif ($this->reportsTableHas('user_id')) {
                    $join->where('reports.user_id', '=', Auth::id());
                }
            })
            ->select(
                'bookings.booking_id',
                'bookings.check_in',
                'bookings.check_out',
                'homestays.homestay_id',
                'homestays.homestay_name',
                'homestays.city',
                'homestays.state',
                'reports.status as report_status'
            )
            ->where('bookings.user_id', Auth::id())
            ->orderByDesc('bookings.created_at')
            ->get();

        return view('profile.landingPages.neighbourhood-concerns', [
            'bookings' => $bookings,
            'isCustomerContext' => true,
        ]);
    }

    public function store(Request $request, int $booking): RedirectResponse
    {
        $bookingRecord = DB::table('bookings')
            ->join('homestays', 'bookings.homestay_id', '=', 'homestays.homestay_id')
            ->select(
                'bookings.booking_id',
                'bookings.user_id',
                'bookings.homestay_id',
                'homestays.homestay_name',
                'homestays.owner_id'
            )
            ->where('bookings.booking_id', $booking)
            ->where('bookings.user_id', Auth::id())
            ->first();

        abort_unless($bookingRecord, 404);

        $request->validate([
            'description' => ['required', 'string', 'max:3000'],
        ]);

        $payload = [
            'booking_id' => $bookingRecord->booking_id,
            'homestay_id' => $bookingRecord->homestay_id,
            'description' => trim($request->input('description')),
            'status' => 'pending',
        ];

        if ($this->reportsTableHas('customer_id')) {
            $payload['customer_id'] = Auth::id();
        }

        if ($this->reportsTableHas('user_id')) {
            $payload['user_id'] = Auth::id();
        }

        if ($this->reportsTableHas('owner_id')) {
            $payload['owner_id'] = $bookingRecord->owner_id;
        }

        if ($this->reportsTableHas('created_at')) {
            $payload['created_at'] = now();
        }

        if ($this->reportsTableHas('updated_at')) {
            $payload['updated_at'] = now();
        }

        $reportId = DB::table('reports')->insertGetId($payload);

        DB::table('report_messages')->insert([
            'report_id' => $reportId,
            'sender_id' => Auth::id(),
            'recipient_scope' => 'host_admin',
            'message' => trim($request->input('description')),
            'created_at' => now(),
        ]);

        $adminIds = DB::table('users')->where('role', 1)->pluck('id');

        foreach ($adminIds as $adminId) {
            $this->notifyUser(
                (int) $adminId,
                'New neighbourhood report',
                Auth::user()->name . ' submitted a neighbourhood concern for booking #' . $bookingRecord->booking_id . '.'
            );
        }

        $this->notifyUser(
            (int) $bookingRecord->owner_id,
            'New neighbourhood report',
            Auth::user()->name . ' submitted a neighbourhood concern for booking #' . $bookingRecord->booking_id . '.'
        );

        return back()->with('success', 'Your neighbourhood concern has been sent to the host and admin.');
    }

    public function ownerIndex(): View
    {
        $reports = DB::table('reports')
            ->join('homestays', 'reports.homestay_id', '=', 'homestays.homestay_id')
            ->join('users as customers', 'reports.' . $this->reportsCustomerColumn(), '=', 'customers.id')
            ->select(
                'reports.*',
                'reports.' . $this->reportsPrimaryKey() . ' as id',
                'reports.' . $this->reportsCustomerColumn() . ' as customer_id',
                'homestays.homestay_name',
                'homestays.owner_id as resolved_owner_id',
                'customers.name as customer_name'
            )
            ->where('homestays.owner_id', Auth::id())
            ->orderByRaw("CASE WHEN reports.status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('reports.created_at')
            ->get();

        return view('profile.homestayOwners.reports.index', compact('reports'));
    }

    public function ownerShow(int $report): View
    {
        $reportRecord = $this->ownerReportContext($report);
        $messages = $this->reportMessages($reportRecord->id);

        return view('profile.homestayOwners.reports.show', compact('reportRecord', 'messages'));
    }

    public function storeOwnerMessage(Request $request, int $report): RedirectResponse
    {
        $reportRecord = $this->ownerReportContext($report);

        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        DB::table('report_messages')->insert([
            'report_id' => $reportRecord->id,
            'sender_id' => Auth::id(),
            'recipient_scope' => 'admin',
            'message' => trim($request->input('message')),
            'created_at' => now(),
        ]);

        foreach (DB::table('users')->where('role', 1)->pluck('id') as $adminId) {
            $this->notifyUser(
                (int) $adminId,
                'Host replied to neighbourhood report',
                Auth::user()->name . ' replied on neighbourhood report #' . $reportRecord->id . '.'
            );
        }

        return back()->with('success', 'Message sent to admin.');
    }

    public function settle(Request $request, int $report): RedirectResponse
    {
        $reportRecord = $this->ownerReportContext($report);

        DB::table('reports')
            ->where($this->reportsPrimaryKey(), $reportRecord->id)
            ->update([
                'status' => 'settled',
                'settled_at' => now(),
                'updated_at' => now(),
            ]);

        $this->notifyUser(
            (int) $reportRecord->customer_id,
            'Neighbourhood report settled',
            'The host marked your neighbourhood report for booking #' . $reportRecord->booking_id . ' as settled.'
        );

        foreach (DB::table('users')->where('role', 1)->pluck('id') as $adminId) {
            $this->notifyUser(
                (int) $adminId,
                'Neighbourhood report settled',
                'The host marked report #' . $reportRecord->id . ' as settled.'
            );
        }

        return back()->with('success', 'Report marked as settled.');
    }

    public function adminIndex(): View
    {
        $reports = DB::table('reports')
            ->join('homestays', 'reports.homestay_id', '=', 'homestays.homestay_id')
            ->join('users as customers', 'reports.' . $this->reportsCustomerColumn(), '=', 'customers.id')
            ->join('users as owners', 'homestays.owner_id', '=', 'owners.id')
            ->select(
                'reports.*',
                'reports.' . $this->reportsPrimaryKey() . ' as id',
                'reports.' . $this->reportsCustomerColumn() . ' as customer_id',
                'homestays.homestay_name',
                'homestays.owner_id as resolved_owner_id',
                'customers.name as customer_name',
                'owners.name as owner_name'
            )
            ->orderByRaw("CASE WHEN reports.status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('reports.created_at')
            ->get();

        return view('profile.admins.reports.index', compact('reports'));
    }

    public function adminShow(int $report): View
    {
        $reportRecord = $this->adminReportContext($report);
        $messages = $this->reportMessages($reportRecord->id);

        return view('profile.admins.reports.show', compact('reportRecord', 'messages'));
    }

    public function storeAdminMessage(Request $request, int $report): RedirectResponse
    {
        $reportRecord = $this->adminReportContext($report);

        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        DB::table('report_messages')->insert([
            'report_id' => $reportRecord->id,
            'sender_id' => Auth::id(),
            'recipient_scope' => 'host',
            'message' => trim($request->input('message')),
            'created_at' => now(),
        ]);

        $this->notifyUser(
            (int) $reportRecord->resolved_owner_id,
            'Official InapDesa message',
            'Admin sent an official message about neighbourhood report #' . $reportRecord->id . '.'
        );

        return back()->with('success', 'Official message sent to host.');
    }

    private function ownerReportContext(int $reportId)
    {
        $report = DB::table('reports')
            ->join('homestays', 'reports.homestay_id', '=', 'homestays.homestay_id')
            ->join('users as customers', 'reports.' . $this->reportsCustomerColumn(), '=', 'customers.id')
            ->select(
                'reports.*',
                'reports.' . $this->reportsPrimaryKey() . ' as id',
                'reports.' . $this->reportsCustomerColumn() . ' as customer_id',
                'homestays.homestay_name',
                'homestays.owner_id as resolved_owner_id',
                'customers.name as customer_name',
                'customers.email as customer_email'
            )
            ->where('reports.' . $this->reportsPrimaryKey(), $reportId)
            ->first();

        abort_unless($report, 404);
        abort_unless((int) $report->resolved_owner_id === (int) Auth::id(), 403);

        return $report;
    }

    private function adminReportContext(int $reportId)
    {
        $report = DB::table('reports')
            ->join('homestays', 'reports.homestay_id', '=', 'homestays.homestay_id')
            ->join('users as customers', 'reports.' . $this->reportsCustomerColumn(), '=', 'customers.id')
            ->join('users as owners', 'homestays.owner_id', '=', 'owners.id')
            ->select(
                'reports.*',
                'reports.' . $this->reportsPrimaryKey() . ' as id',
                'reports.' . $this->reportsCustomerColumn() . ' as customer_id',
                'homestays.homestay_name',
                'homestays.owner_id as resolved_owner_id',
                'customers.name as customer_name',
                'owners.name as owner_name'
            )
            ->where('reports.' . $this->reportsPrimaryKey(), $reportId)
            ->first();

        abort_unless($report, 404);

        return $report;
    }

    private function reportMessages(int $reportId)
    {
        return DB::table('report_messages')
            ->join('users', 'report_messages.sender_id', '=', 'users.id')
            ->select(
                'report_messages.*',
                'users.name as sender_name',
                'users.role as sender_role'
            )
            ->where('report_messages.report_id', $reportId)
            ->orderBy('report_messages.created_at')
            ->orderBy('report_messages.id')
            ->get();
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

    private function reportsTableHas(string $column): bool
    {
        static $columns = [];

        if (! array_key_exists($column, $columns)) {
            $columns[$column] = Schema::hasColumn('reports', $column);
        }

        return $columns[$column];
    }

    private function reportsPrimaryKey(): string
    {
        return $this->reportsTableHas('id') ? 'id' : 'report_id';
    }

    private function reportsCustomerColumn(): string
    {
        return $this->reportsTableHas('customer_id') ? 'customer_id' : 'user_id';
    }
}
