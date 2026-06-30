@extends('profile.homestayOwners.app_ownerHomestay')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-10">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-red-600/70">Host review</p>
            <h1 class="mt-3 text-3xl font-semibold text-gray-900">Cancellation requests</h1>
            <p class="mt-2 text-sm text-gray-600">
                Guests get a full refund at 2 days before check-in and a 50% refund at 1 day before check-in. Only the host can approve or reject these requests.
            </p>
        </div>
    </div>

    @if (! ($tableReady ?? true))
        <div class="mb-6 rounded-[24px] border border-amber-200 bg-amber-50 px-6 py-4 text-sm text-amber-800">
            Cancellation requests are not available yet because the `booking_cancellation_requests` table has not been created in the database.
        </div>
    @endif

    <div class="overflow-hidden rounded-[28px] bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-[#f8f5ee] text-left text-gray-600">
                    <tr>
                        <th class="px-6 py-4">Booking</th>
                        <th class="px-6 py-4">Guest</th>
                        <th class="px-6 py-4">Refund preview</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eee6d9]">
                    @forelse ($requests as $request)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $request->homestay_name }}</p>
                                <p class="mt-1 text-gray-500">Booking #{{ $request->booking_id }}</p>
                                <p class="mt-1 text-gray-500">
                                    {{ \Carbon\Carbon::parse($request->check_in)->format('d M Y') }} - {{ \Carbon\Carbon::parse($request->check_out)->format('d M Y') }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $request->customer_name }}</p>
                                <p class="mt-1 text-gray-500">Requested {{ \Carbon\Carbon::parse($request->created_at)->format('d M Y') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-green-700">RM {{ number_format($request->requested_refund_amount, 2) }}</p>
                                <p class="mt-1 text-gray-500">{{ (int) round($request->refund_rate * 100) }}% refund</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]
                                    {{ $request->status === 'pending' ? 'bg-amber-100 text-amber-700' : ($request->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $request->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('owner.cancellation-requests.show', $request->id) }}"
                                    class="inline-flex items-center rounded-full bg-green-600 px-4 py-2 text-xs font-semibold text-white hover:bg-green-700">
                                    Review request
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                No cancellation requests yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
