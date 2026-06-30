<?php

namespace App\Http\Controllers;

use App\Mail\GroupPaymentInvitation;
use App\Services\ToyyibPayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GroupPaymentController extends Controller
{
    public function create()
    {
        $pendingBooking = session('pending_booking');

        if (!$pendingBooking || (int) $pendingBooking['user_id'] !== (int) Auth::id()) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Please start your booking from a homestay page first.');
        }

        return view('profile.Customers.group_payment.create', [
            'pendingBooking' => $pendingBooking,
        ]);
    }

    public function store(Request $request)
    {
        $pendingBooking = session('pending_booking');

        if (!$pendingBooking || (int) $pendingBooking['user_id'] !== (int) Auth::id()) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Your booking session expired. Please try booking again.');
        }

        $validated = $request->validate([
            'emails' => ['required', 'string'],
        ]);

        $emails = collect(explode(',', $validated['emails']))
            ->map(fn ($email) => trim(strtolower($email)))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            return back()->with('error', 'Please enter at least one valid email address.');
        }

        if ($emails->count() > 9) {
            return back()->with('error', 'You can invite up to 9 other people (10 total including yourself).');
        }

        $initiatorEmail = strtolower(Auth::user()->email);
        if ($emails->contains($initiatorEmail)) {
            return back()->with('error', 'You cannot invite yourself.');
        }

        $memberCount = $emails->count() + 1;
        $shareAmount = round($pendingBooking['total'] / $memberCount, 2);

        DB::beginTransaction();

        try {
            $token = Str::random(64);
            $deadlineAt = now()->addHour();
            $groupPaymentUrl = route('group-payment.show', $token);

            $groupPaymentId = DB::table('group_payments')->insertGetId([
                'token' => $token,
                'initiator_user_id' => Auth::id(),
                'homestay_id' => $pendingBooking['homestay_id'],
                'homestay_name' => $pendingBooking['homestay_name'],
                'location' => $pendingBooking['location'],
                'image' => $pendingBooking['image'] ?? null,
                'check_in' => $pendingBooking['check_in'],
                'check_out' => $pendingBooking['check_out'],
                'guest_count' => $pendingBooking['guest_count'],
                'total_price' => $pendingBooking['total'],
                'share_amount' => $shareAmount,
                'member_count' => $memberCount,
                'status' => 'pending',
                'deadline_at' => $deadlineAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('group_payment_members')->insert([
                'group_payment_id' => $groupPaymentId,
                'user_id' => Auth::id(),
                'email' => $initiatorEmail,
                'name' => Auth::user()->name,
                'share_amount' => $shareAmount,
                'payment_status' => 'pending',
                'is_initiator' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->createNotification(
                Auth::id(),
                'Group payment share due',
                sprintf(
                    'Pay RM %s for %s before %s to confirm the group booking.',
                    number_format($shareAmount, 2),
                    $pendingBooking['homestay_name'],
                    $deadlineAt->format('d M Y, g:i A')
                ),
                $groupPaymentUrl
            );

            foreach ($emails as $email) {
                $invitedUser = DB::table('users')
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->first();

                DB::table('group_payment_members')->insert([
                    'group_payment_id' => $groupPaymentId,
                    'user_id' => $invitedUser?->id,
                    'email' => $email,
                    'name' => $invitedUser?->name,
                    'share_amount' => $shareAmount,
                    'payment_status' => 'pending',
                    'is_initiator' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($invitedUser) {
                    $this->createNotification(
                        (int) $invitedUser->id,
                        'Group payment request',
                        sprintf(
                            '%s invited you to pay RM %s for %s before %s.',
                            Auth::user()->name,
                            number_format($shareAmount, 2),
                            $pendingBooking['homestay_name'],
                            $deadlineAt->format('d M Y, g:i A')
                        ),
                        $groupPaymentUrl
                    );
                }

                Mail::to($email)->queue(new GroupPaymentInvitation($token, $pendingBooking, $shareAmount, Auth::user()->name));
            }

            DB::commit();

            session()->forget('pending_booking');

            return redirect()->route('group-payment.show', $token)
                ->with('success', 'Group payment created! Invitation emails have been sent.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to create group payment. Please try again.');
        }
    }

    public function show(string $token)
    {
        $groupPayment = DB::table('group_payments')
            ->where('token', $token)
            ->first();

        if (!$groupPayment) {
            abort(404);
        }

        $groupPayment->deadline_at = Carbon::parse($groupPayment->deadline_at);

        $members = DB::table('group_payment_members')
            ->where('group_payment_id', $groupPayment->id)
            ->orderBy('is_initiator', 'desc')
            ->orderBy('created_at')
            ->get();

        $currentMember = null;
        if (Auth::check()) {
            $currentMember = $members->first(fn ($m) => $m->user_id && (int) $m->user_id === (int) Auth::id());

            if (!$currentMember) {
                $currentMember = $members->first(fn ($m) => strtolower($m->email) === strtolower(Auth::user()->email));
            }
        }

        $paidCount = $members->where('payment_status', 'paid')->count();
        $totalCount = $members->count();
        $allPaid = $paidCount === $totalCount;

        return view('profile.Customers.group_payment.show', [
            'groupPayment' => $groupPayment,
            'members' => $members,
            'currentMember' => $currentMember,
            'paidCount' => $paidCount,
            'totalCount' => $totalCount,
            'allPaid' => $allPaid,
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $groupPayment = DB::table('group_payments')
            ->where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$groupPayment) {
            return redirect()->route('user.dashboard')
                ->with('error', 'This group payment is no longer available.');
        }

        if (now()->greaterThan(Carbon::parse($groupPayment->deadline_at))) {
            return redirect()->route('group-payment.show', $token)
                ->with('error', 'This group payment has expired.');
        }

        $member = DB::table('group_payment_members')
            ->where('group_payment_id', $groupPayment->id)
            ->where(function ($q) {
                $q->where('user_id', Auth::id())
                    ->orWhere('email', strtolower(Auth::user()->email));
            })
            ->first();

        if (!$member) {
            return redirect()->route('group-payment.show', $token)
                ->with('error', 'You are not part of this group payment.');
        }

        if ($member->user_id && (int) $member->user_id !== (int) Auth::id()) {
            return redirect()->route('group-payment.show', $token)
                ->with('error', 'This invitation has already been accepted by another user.');
        }

        DB::table('group_payment_members')
            ->where('id', $member->id)
            ->update([
                'user_id' => Auth::id(),
                'name' => Auth::user()->name,
                'updated_at' => now(),
            ]);

        if ($request->boolean('pay_now')) {
            $updatedMember = DB::table('group_payment_members')
                ->where('id', $member->id)
                ->first();

            return $this->startMemberPayment($groupPayment, $updatedMember);
        }

        $this->createNotification(
            Auth::id(),
            'Group payment share due',
            sprintf(
                'You joined the group payment for %s. Pay RM %s before %s.',
                $groupPayment->homestay_name,
                number_format($member->share_amount, 2),
                Carbon::parse($groupPayment->deadline_at)->format('d M Y, g:i A')
            ),
            route('group-payment.show', $groupPayment->token)
        );

        return redirect()->route('group-payment.show', $token)
            ->with('success', 'You have joined the group payment!');
    }

    public function pay(string $token)
    {
        $groupPayment = DB::table('group_payments')
            ->where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$groupPayment) {
            return redirect()->route('user.dashboard')
                ->with('error', 'This group payment is no longer available.');
        }

        if (now()->greaterThan(Carbon::parse($groupPayment->deadline_at))) {
            return redirect()->route('group-payment.show', $token)
                ->with('error', 'This group payment has expired.');
        }

        $member = DB::table('group_payment_members')
            ->where('group_payment_id', $groupPayment->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$member) {
            return redirect()->route('group-payment.show', $token)
                ->with('error', 'You need to accept the invitation first.');
        }

        if ($member->payment_status === 'paid') {
            return redirect()->route('group-payment.show', $token)
                ->with('success', 'You have already paid your share.');
        }

        return $this->startMemberPayment($groupPayment, $member);
    }

    private function startMemberPayment(object $groupPayment, object $member)
    {
        if ($member->payment_status === 'paid') {
            return redirect()->route('group-payment.show', $groupPayment->token)
                ->with('success', 'You have already paid your share.');
        }

        $user = Auth::user();
        $orderId = 'GP-' . strtoupper(Str::random(10));

        DB::table('group_payment_members')
            ->where('id', $member->id)
            ->update([
                'order_id' => $orderId,
                'updated_at' => now(),
            ]);

        try {
            $toyyibPay = new ToyyibPayService();
            $result = $toyyibPay->createBill([
                'billName' => 'Group: ' . $groupPayment->homestay_name,
                'billDescription' => sprintf(
                    'Group payment share for %s (%s to %s)',
                    $groupPayment->homestay_name,
                    Carbon::parse($groupPayment->check_in)->format('d/m/Y'),
                    Carbon::parse($groupPayment->check_out)->format('d/m/Y')
                ),
                'billAmount' => (float) $member->share_amount,
                'billReturnUrl' => route('group-payment.toyyibpay.return'),
                'billCallbackUrl' => route('group-payment.toyyibpay.callback'),
                'billExternalReferenceNo' => $orderId,
                'billTo' => $user->name,
                'billEmail' => $user->email,
                'billPhone' => $user->phone ?? '0123456789',
                'billPaymentChannel' => '0',
            ]);

            DB::table('group_payment_members')
                ->where('id', $member->id)
                ->update([
                    'bill_code' => $result['billCode'],
                    'updated_at' => now(),
                ]);

            return redirect()->away($result['paymentUrl']);
        } catch (\Exception $e) {
            return redirect()->route('group-payment.show', $groupPayment->token)
                ->with('error', 'Payment gateway is temporarily unavailable. Please try again later.');
        }
    }

    public function toyyibpayReturn(Request $request)
    {
        $statusId = $request->query('status_id');
        $billCode = $request->query('billcode');

        if ($statusId === '1') {
            $member = DB::table('group_payment_members')
                ->where('bill_code', $billCode)
                ->where('user_id', Auth::id())
                ->first();

            if ($member) {
                $groupPayment = DB::table('group_payments')
                    ->where('id', $member->group_payment_id)
                    ->first();

                if ($groupPayment && $groupPayment->status === 'pending') {
                    $this->markMemberAsPaid($member);

                    return redirect()->route('group-payment.show', $groupPayment->token)
                        ->with('success', 'Your payment was successful!');
                }
            }

            return redirect()->route('user.dashboard')
                ->with('success', 'Payment successful!');
        }

        if ($statusId === '2') {
            return redirect()->route('user.dashboard')
                ->with('error', 'Your payment is still processing. We will notify you once it is confirmed.');
        }

        return redirect()->route('user.dashboard')
            ->with('error', 'Payment was not successful. Please try again.');
    }

    public function toyyibpayCallback(Request $request)
    {
        $status = $request->input('status');
        $orderId = $request->input('order_id');
        $refno = $request->input('refno');
        $receivedHash = $request->input('hash');

        if (!$status || !$orderId || !$refno || !$receivedHash) {
            return response('Missing parameters', 400);
        }

        $toyyibPay = new ToyyibPayService();

        if (!$toyyibPay->verifyCallbackHash((string) $status, $orderId, $refno, $receivedHash)) {
            return response('Invalid hash', 400);
        }

        if ($status === '1') {
            $member = DB::table('group_payment_members')
                ->where('order_id', $orderId)
                ->first();

            if ($member) {
                $this->markMemberAsPaid($member);
            }
        }

        return response('OK');
    }

    private function markMemberAsPaid(object $member): void
    {
        if ($member->payment_status === 'paid') {
            return;
        }

        DB::table('group_payment_members')
            ->where('id', $member->id)
            ->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'updated_at' => now(),
            ]);

        $groupPayment = DB::table('group_payments')
            ->where('id', $member->group_payment_id)
            ->first();

        if ($groupPayment) {
            if ($member->user_id) {
                $this->createNotification(
                    (int) $member->user_id,
                    'Group payment received',
                    sprintf(
                        'Your RM %s share for %s has been marked as paid.',
                        number_format($member->share_amount, 2),
                        $groupPayment->homestay_name
                    ),
                    route('group-payment.show', $groupPayment->token)
                );
            }

            $remainingMembers = DB::table('group_payment_members')
                ->where('group_payment_id', $member->group_payment_id)
                ->where('payment_status', 'pending')
                ->whereNotNull('user_id')
                ->where('id', '<>', $member->id)
                ->get();

            foreach ($remainingMembers as $remainingMember) {
                $this->createNotification(
                    (int) $remainingMember->user_id,
                    'Group payment still due',
                    sprintf(
                        'Please pay RM %s for %s before %s so the booking can be confirmed.',
                        number_format($remainingMember->share_amount, 2),
                        $groupPayment->homestay_name,
                        Carbon::parse($groupPayment->deadline_at)->format('d M Y, g:i A')
                    ),
                    route('group-payment.show', $groupPayment->token)
                );
            }
        }

        $this->checkAndCompleteGroupPayment($member->group_payment_id);
    }

    private function createNotification(?int $userId, string $title, string $message, ?string $linkUrl = null): void
    {
        if (!$userId) {
            return;
        }

        $notification = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'is_read' => 0,
            'created_at' => now(),
        ];

        if ($linkUrl && Schema::hasColumn('notifications', 'link_url')) {
            $notification['link_url'] = $linkUrl;
        }

        DB::table('notifications')->insert($notification);
    }

    private function checkAndCompleteGroupPayment(int $groupPaymentId): void
    {
        $groupPayment = DB::table('group_payments')
            ->where('id', $groupPaymentId)
            ->where('status', 'pending')
            ->first();

        if (!$groupPayment) {
            return;
        }

        $members = DB::table('group_payment_members')
            ->where('group_payment_id', $groupPaymentId)
            ->get();

        $allPaid = $members->every(fn ($m) => $m->payment_status === 'paid');

        if ($allPaid) {
            $initiatorMember = $members->first(fn ($m) => $m->is_initiator);

            if (!$initiatorMember) {
                return;
            }

            DB::beginTransaction();

            try {
                $bookingId = DB::table('bookings')->insertGetId([
                    'user_id' => $initiatorMember->user_id,
                    'homestay_id' => $groupPayment->homestay_id,
                    'check_in' => $groupPayment->check_in,
                    'check_out' => $groupPayment->check_out,
                    'total_price' => $groupPayment->total_price,
                    'booking_status' => 'confirmed',
                    'created_at' => now(),
                ]);

                DB::table('group_payments')
                    ->where('id', $groupPaymentId)
                    ->update([
                        'status' => 'paid',
                        'booking_id' => $bookingId,
                        'updated_at' => now(),
                    ]);

                foreach ($members as $m) {
                    if ($m->user_id) {
                        DB::table('notifications')->insert([
                            'user_id' => $m->user_id,
                            'title' => 'Group payment completed',
                            'message' => sprintf(
                                'The group payment for %s is complete. Booking confirmed!',
                                $groupPayment->homestay_name
                            ),
                            'is_read' => 0,
                            'created_at' => now(),
                        ]);
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
            }
        }
    }

    public function cancel(string $token)
    {
        $groupPayment = DB::table('group_payments')
            ->where('token', $token)
            ->where('initiator_user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (!$groupPayment) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Cannot cancel this group payment.');
        }

        DB::table('group_payments')
            ->where('id', $groupPayment->id)
            ->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);

        $members = DB::table('group_payment_members')
            ->where('group_payment_id', $groupPayment->id)
            ->whereNotNull('user_id')
            ->get();

        foreach ($members as $m) {
            DB::table('notifications')->insert([
                'user_id' => $m->user_id,
                'title' => 'Group payment cancelled',
                'message' => sprintf(
                    'The group payment for %s has been cancelled by the organizer.',
                    $groupPayment->homestay_name
                ),
                'is_read' => 0,
                'created_at' => now(),
            ]);
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Group payment has been cancelled.');
    }

    public function myGroupPayments()
    {
        $userId = Auth::id();

        $activeGroups = DB::table('group_payments')
            ->join('group_payment_members', 'group_payments.id', '=', 'group_payment_members.group_payment_id')
            ->where('group_payment_members.user_id', $userId)
            ->where('group_payments.status', 'pending')
            ->select('group_payments.*')
            ->orderByDesc('group_payments.created_at')
            ->get();

        $pastGroups = DB::table('group_payments')
            ->join('group_payment_members', 'group_payments.id', '=', 'group_payment_members.group_payment_id')
            ->where('group_payment_members.user_id', $userId)
            ->whereIn('group_payments.status', ['paid', 'cancelled', 'expired'])
            ->select('group_payments.*')
            ->orderByDesc('group_payments.created_at')
            ->get();

        return view('profile.Customers.group_payment.my_groups', [
            'activeGroups' => $activeGroups,
            'pastGroups' => $pastGroups,
        ]);
    }
}
