@extends('profile.homestayOwners.app_ownerHomestay')

@section('content')
<div class="mx-auto max-w-5xl px-6 py-10">
    <div class="mb-8">
        <a href="{{ route('owner.cancellation-requests.index') }}" class="text-sm font-medium text-green-700 hover:text-green-800">← Back to cancellation requests</a>
        <h1 class="mt-4 text-3xl font-semibold text-gray-900">Cancellation request #{{ $requestRecord->id }}</h1>
        <p class="mt-2 text-sm text-gray-600">Review the guest reason, refund eligibility, and make the final host decision.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="rounded-[28px] bg-white p-8 shadow">
            <h2 class="text-xl font-semibold text-gray-900">Booking details</h2>
            <div class="mt-6 space-y-4 text-sm text-gray-700">
                <div class="flex items-center justify-between border-b border-[#eee6d9] pb-4">
                    <span>Homestay</span>
                    <span class="font-semibold">{{ $requestRecord->homestay_name }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-[#eee6d9] pb-4">
                    <span>Guest</span>
                    <span class="font-semibold">{{ $requestRecord->customer_name }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-[#eee6d9] pb-4">
                    <span>Booking period</span>
                    <span class="font-semibold">{{ \Carbon\Carbon::parse($requestRecord->check_in)->format('d M Y') }} - {{ \Carbon\Carbon::parse($requestRecord->check_out)->format('d M Y') }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-[#eee6d9] pb-4">
                    <span>Total paid</span>
                    <span class="font-semibold">RM {{ number_format($requestRecord->total_price, 2) }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-[#eee6d9] pb-4">
                    <span>Refund policy result</span>
                    <span class="font-semibold text-green-700">RM {{ number_format($requestRecord->requested_refund_amount, 2) }} ({{ (int) round($requestRecord->refund_rate * 100) }}%)</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Status</span>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]
                        {{ $requestRecord->status === 'pending' ? 'bg-amber-100 text-amber-700' : ($requestRecord->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                        {{ $requestRecord->status }}
                    </span>
                </div>
            </div>

            <div class="mt-8 rounded-[24px] bg-[#fbf7ee] p-5">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Guest reason</p>
                <p class="mt-3 text-sm leading-7 text-gray-700">{{ $requestRecord->reason }}</p>
            </div>

            @if ($requestRecord->host_decision_notes)
                <div class="mt-5 rounded-[24px] border border-[#e7dfcf] bg-white p-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Host note</p>
                    <p class="mt-3 text-sm leading-7 text-gray-700">{{ $requestRecord->host_decision_notes }}</p>
                </div>
            @endif
        </section>

        <section class="space-y-6">
            <div class="rounded-[28px] bg-white p-8 shadow">
                <h2 class="text-xl font-semibold text-gray-900">Guest contact</h2>
                <p class="mt-3 text-sm text-gray-600">
                    Need more context before deciding? Continue the booking chat with this guest.
                </p>
                <a href="{{ route('owner.messages.show', $requestRecord->booking_id) }}"
                    class="mt-5 inline-flex items-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Chat guest
                </a>
            </div>

            @if ($requestRecord->status === 'pending')
                <div class="rounded-[28px] bg-white p-8 shadow">
                    <h2 class="text-xl font-semibold text-gray-900">Approve request</h2>
                    <form method="POST" action="{{ route('owner.cancellation-requests.approve', $requestRecord->id) }}" class="mt-5">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700">Optional host note</label>
                        <textarea name="host_decision_notes" rows="4"
                            class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600"
                            placeholder="Add a short note for the guest if needed...">{{ old('host_decision_notes') }}</textarea>
                        <button type="submit"
                            class="mt-4 inline-flex items-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white hover:bg-green-700">
                            Approve cancellation
                        </button>
                    </form>
                </div>

                <div class="rounded-[28px] bg-white p-8 shadow">
                    <h2 class="text-xl font-semibold text-gray-900">Reject request</h2>
                    <form method="POST" action="{{ route('owner.cancellation-requests.reject', $requestRecord->id) }}" class="mt-5">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700">Reason for rejection</label>
                        <textarea name="host_decision_notes" rows="4"
                            class="mt-2 w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500"
                            placeholder="Explain why this request is being rejected..." required>{{ old('host_decision_notes') }}</textarea>
                        <button type="submit"
                            class="mt-4 inline-flex items-center rounded-full bg-red-600 px-6 py-3 text-sm font-semibold text-white hover:bg-red-700">
                            Reject cancellation
                        </button>
                    </form>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
