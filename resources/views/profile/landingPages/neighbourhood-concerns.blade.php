@extends(auth()->check() && in_array((int) auth()->user()->role, [2, 3], true) ? 'profile.Customers.app_customers' : 'layouts.app')

@section('content')
<div class="bg-[#f5f0de]">
    <section class="relative isolate overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(76,175,80,0.18),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(250,204,21,0.14),_transparent_28%)]"></div>
        <div class="relative mx-auto max-w-7xl px-6 py-16 lg:py-24">
            <p class="text-xs font-semibold uppercase tracking-[0.4em] text-green-700/70">Community Care</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight text-[#173423] sm:text-5xl">
                Report Neighbourhood Concerns
            </h1>
            <p class="mt-6 max-w-3xl text-base leading-7 text-gray-600">
                When a stay affects nearby residents or the surrounding area, both the host and admin should know quickly. Guests can submit a concern here, the host can follow up with the guest, and admin can monitor the case with official messages.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 pb-16 lg:pb-24">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <h2 class="text-2xl font-semibold text-[#173423]">Who receives it</h2>
                <p class="mt-4 text-sm leading-7 text-gray-600">
                    Every neighbourhood concern is delivered to both the host and the admin team so there is visibility from the start.
                </p>
            </div>
            <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <h2 class="text-2xl font-semibold text-[#173423]">Host follow-up</h2>
                <p class="mt-4 text-sm leading-7 text-gray-600">
                    Hosts can contact the guest for more details through booking chat, update admin with follow-up notes, and mark the report done once it is settled.
                </p>
            </div>
            <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <h2 class="text-2xl font-semibold text-[#173423]">What to include</h2>
                <p class="mt-4 text-sm leading-7 text-gray-600">
                    Share the booking, date, what happened, and any impact on neighbours or the local area so the follow-up can be specific.
                </p>
            </div>
        </div>

        @if ($isCustomerContext)
            <div class="mt-8 rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-green-700/70">Your stays</p>
                    <h2 class="mt-2 text-2xl font-semibold text-[#173423]">Send a concern to host and admin</h2>
                </div>

                <div class="space-y-5">
                    @forelse ($bookings as $booking)
                        <div class="rounded-[24px] border border-[#e9e2d5] bg-[#fcfaf4] p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $booking->homestay_name }}</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }} - {{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}
                                        · {{ trim(collect([$booking->city, $booking->state])->filter()->implode(', ')) ?: 'Malaysia' }}
                                    </p>
                                </div>
                                @if ($booking->report_status)
                                    <div class="rounded-full bg-[#eef5eb] px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-green-700">
                                        {{ $booking->report_status }}
                                    </div>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('user.reports.store', $booking->booking_id) }}" class="mt-5">
                                @csrf
                                <label class="block text-sm font-medium text-gray-700">Describe the neighbourhood concern</label>
                                <textarea name="description" rows="4"
                                    class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600"
                                    placeholder="Example: noise at midnight, unsafe parking, repeated disturbance to nearby homes...">{{ old('description') }}</textarea>
                                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-xs text-gray-500">
                                        This report will be sent to both the host and admin.
                                    </p>
                                    <button type="submit"
                                        class="inline-flex items-center justify-center rounded-full bg-[#173423] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#0f281b]">
                                        Submit concern
                                    </button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No bookings found yet, so there is nothing to report right now.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </section>
</div>
@endsection
