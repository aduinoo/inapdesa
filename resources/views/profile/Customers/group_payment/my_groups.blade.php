@extends('profile.Customers.app_customers')

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-10">
    <div class="mx-auto max-w-5xl px-6">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">My Groups</p>
            <h1 class="mt-3 text-3xl font-semibold text-gray-900">Group Payments</h1>
        </div>

        @if ($activeGroups->isEmpty() && $pastGroups->isEmpty())
            <div class="rounded-[30px] bg-white p-12 text-center shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">No group payments yet</h3>
                <p class="mt-2 text-sm text-gray-500">Start a booking and choose "Split with friends" to create a group payment.</p>
                <a href="{{ route('user.dashboard') }}" class="mt-6 inline-flex items-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                    Browse homestays
                </a>
            </div>
        @else
            @if ($activeGroups->isNotEmpty())
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Active</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($activeGroups as $group)
                        @php
                            $members = \Illuminate\Support\Facades\DB::table('group_payment_members')->where('group_payment_id', $group->id)->get();
                            $paidCount = $members->where('payment_status', 'paid')->count();
                        @endphp
                        <a href="{{ route('group-payment.show', $group->token) }}"
                            class="rounded-[24px] bg-white p-5 shadow-[0_12px_40px_rgba(47,62,42,0.06)] ring-1 ring-black/5 transition hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <p class="text-base font-semibold text-gray-900">{{ $group->homestay_name }}</p>
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Pending</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">{{ $group->location }}</p>
                            <div class="mt-4 flex items-center justify-between text-sm">
                                <span class="text-gray-600">{{ $paidCount }}/{{ $group->member_count }} paid</span>
                                <span class="font-semibold text-green-700">RM {{ number_format($group->share_amount, 2) }}/person</span>
                            </div>
                            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200">
                                <div class="h-full rounded-full bg-green-600" style="width: {{ $group->member_count > 0 ? ($paidCount / $group->member_count * 100) : 0 }}%"></div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($pastGroups->isNotEmpty())
                <h2 class="mb-4 mt-8 text-lg font-semibold text-gray-900">Past</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($pastGroups as $group)
                        <a href="{{ route('group-payment.show', $group->token) }}"
                            class="rounded-[24px] bg-white p-5 shadow-[0_12px_40px_rgba(47,62,42,0.06)] ring-1 ring-black/5 transition hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <p class="text-base font-semibold text-gray-900">{{ $group->homestay_name }}</p>
                                @if ($group->status === 'paid')
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Completed</span>
                                @elseif ($group->status === 'cancelled')
                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">Cancelled</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">Expired</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm text-gray-500">{{ $group->location }}</p>
                            <p class="mt-2 text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($group->check_in)->format('d M Y') }} - {{ \Carbon\Carbon::parse($group->check_out)->format('d M Y') }}
                            </p>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
