@extends(auth()->check() && in_array((int) auth()->user()->role, [2, 3], true) ? 'profile.Customers.app_customers' : 'layouts.app')

@section('content')
<div class="bg-[#f5f0de]">
    <section class="relative isolate overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(76,175,80,0.18),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(250,204,21,0.14),_transparent_28%)]"></div>
        <div class="relative mx-auto max-w-7xl px-6 py-16 lg:py-24">
            <p class="text-xs font-semibold uppercase tracking-[0.4em] text-green-700/70">Booking Policy</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight text-[#173423] sm:text-5xl">
                Cancellation Options
            </h1>
            <p class="mt-6 max-w-3xl text-base leading-7 text-gray-600">
                Cancellation requests are reviewed by the host only. If a guest cancels at least 2 days before the check-in date, the refund is full. If the guest cancels 1 day before check-in, the refund is partial at 50%.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 pb-16 lg:pb-24">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <h2 class="text-2xl font-semibold text-[#173423]">Full refund</h2>
                <p class="mt-4 text-sm leading-7 text-gray-600">
                    If the request is made 2 days or more before the booked check-in date, the guest qualifies for a 100% refund once the host approves the cancellation.
                </p>
            </div>
            <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <h2 class="text-2xl font-semibold text-[#173423]">Partial refund</h2>
                <p class="mt-4 text-sm leading-7 text-gray-600">
                    If the request is made 1 day before the booked check-in date, the guest qualifies for a 50% refund once the host approves the cancellation.
                </p>
            </div>
            <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <h2 class="text-2xl font-semibold text-[#173423]">Host decision</h2>
                <p class="mt-4 text-sm leading-7 text-gray-600">
                    Hosts approve or reject cancellation requests. Guests should include a clear reason so the host can make the decision faster.
                </p>
            </div>
        </div>

        @if ($isCustomerContext)
            <div class="mt-8 rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-green-700/70">Your upcoming bookings</p>
                    <h2 class="mt-2 text-2xl font-semibold text-[#173423]">Request cancellation</h2>
                </div>

                @if (! ($tableReady ?? true))
                    <div class="mb-6 rounded-[24px] border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                        Cancellation request submission is not available yet because the `booking_cancellation_requests` table has not been created in the database.
                    </div>
                @endif

                <div class="space-y-5">
                    @forelse ($bookings as $booking)
                        <div class="rounded-[24px] border border-[#e9e2d5] bg-[#fcfaf4] p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $booking->homestay_name }}</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }} - {{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}
                                        | {{ trim(collect([$booking->city, $booking->state])->filter()->implode(', ')) ?: 'Malaysia' }}
                                    </p>
                                    <p class="mt-3 text-sm text-gray-600">Booking total: RM {{ number_format($booking->total_price, 2) }}</p>
                                    <p class="mt-1 text-sm text-green-700">
                                        Eligible refund preview: RM {{ number_format($booking->refund['refund_amount'], 2) }}
                                        ({{ (int) round($booking->refund['refund_rate'] * 100) }}%)
                                    </p>
                                </div>
                                @if ($booking->cancellation_status)
                                    <div class="rounded-full bg-[#eef5eb] px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-green-700">
                                        {{ $booking->cancellation_status }}
                                    </div>
                                @endif
                            </div>

                            @if (($tableReady ?? true) && (! $booking->cancellation_status || $booking->cancellation_status === 'rejected'))
                                <form method="POST" action="{{ route('user.cancellation-requests.store', $booking->booking_id) }}" class="mt-5">
                                    @csrf
                                    <label class="block text-sm font-medium text-gray-700">Reason for cancellation</label>
                                    <textarea name="reason" rows="4"
                                        class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600"
                                        placeholder="Explain why you need to cancel this stay...">{{ old('reason') }}</textarea>
                                    <button type="submit"
                                        class="mt-4 inline-flex items-center justify-center rounded-full bg-green-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-800">
                                        Request cancellation
                                    </button>
                                </form>
                            @elseif ($booking->host_decision_notes)
                                <div class="mt-4 rounded-2xl border border-[#d9e4d2] bg-[#f7fbf3] px-4 py-3 text-sm text-gray-700">
                                    Host note: {{ $booking->host_decision_notes }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No eligible upcoming bookings right now.</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-8 rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-green-700/70">Your records</p>
                    <h2 class="mt-2 text-2xl font-semibold text-[#173423]">Cancellation history</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Review your past and current cancellation requests, including host decisions and refund amounts.
                    </p>
                </div>

                @if (! ($tableReady ?? true))
                    <div class="rounded-[24px] border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                        Cancellation history is not available yet because the `booking_cancellation_requests` table has not been created in the database.
                    </div>
                @else
                    <div class="space-y-5">
                        @forelse (($history ?? collect()) as $request)
                            <div class="rounded-[24px] border border-[#e9e2d5] bg-[#fcfaf4] p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $request->homestay_name }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Booking #{{ $request->booking_id }}
                                            | {{ \Carbon\Carbon::parse($request->check_in)->format('d M Y') }} - {{ \Carbon\Carbon::parse($request->check_out)->format('d M Y') }}
                                            | {{ trim(collect([$request->city, $request->state])->filter()->implode(', ')) ?: 'Malaysia' }}
                                        </p>
                                        <p class="mt-3 text-sm text-gray-600">
                                            Requested on {{ \Carbon\Carbon::parse($request->created_at)->format('d M Y, h:i A') }}
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="inline-flex rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em]
                                            {{ $request->status === 'pending' ? 'bg-amber-100 text-amber-700' : ($request->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $request->status }}
                                        </span>
                                        <span class="inline-flex rounded-full bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-600 ring-1 ring-[#eadfce]">
                                            {{ $request->booking_status }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-5 grid gap-4 md:grid-cols-3">
                                    <div class="rounded-[20px] bg-white px-4 py-4 ring-1 ring-[#eadfce]">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Booking total</p>
                                        <p class="mt-2 text-base font-semibold text-gray-900">RM {{ number_format($request->total_price, 2) }}</p>
                                    </div>
                                    <div class="rounded-[20px] bg-white px-4 py-4 ring-1 ring-[#eadfce]">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Requested refund</p>
                                        <p class="mt-2 text-base font-semibold text-green-700">RM {{ number_format($request->requested_refund_amount, 2) }}</p>
                                    </div>
                                    <div class="rounded-[20px] bg-white px-4 py-4 ring-1 ring-[#eadfce]">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Refund rate</p>
                                        <p class="mt-2 text-base font-semibold text-gray-900">{{ (int) round($request->refund_rate * 100) }}%</p>
                                    </div>
                                </div>

                                <div class="mt-5 rounded-[20px] bg-white px-4 py-4 ring-1 ring-[#eadfce]">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Your reason</p>
                                    <p class="mt-2 text-sm leading-7 text-gray-700">{{ $request->reason }}</p>
                                </div>

                                @if ($request->host_decision_notes)
                                    <div class="mt-4 rounded-[20px] border border-[#d9e4d2] bg-[#f7fbf3] px-4 py-4 text-sm text-gray-700">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-green-700/70">Host note</p>
                                        <p class="mt-2 leading-7">{{ $request->host_decision_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No cancellation requests have been submitted yet.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection
