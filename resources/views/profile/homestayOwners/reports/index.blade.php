@extends('profile.homestayOwners.app_ownerHomestay')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-10">
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-amber-600/80">Host follow-up</p>
        <h1 class="mt-3 text-3xl font-semibold text-gray-900">Neighbourhood reports</h1>
        <p class="mt-2 text-sm text-gray-600">
            These reports were sent to both you and admin. You can open each case, chat with the guest for more details, message admin, and mark it done when settled.
        </p>
    </div>

    <div class="grid gap-5">
        @forelse ($reports as $report)
            <article class="rounded-[28px] bg-white p-6 shadow">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-semibold text-gray-900">{{ $report->homestay_name }}</h2>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]
                                {{ $report->status === 'settled' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $report->status }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Guest: {{ $report->customer_name }} · Booking #{{ $report->booking_id }}</p>
                        <p class="mt-4 max-w-3xl text-sm leading-7 text-gray-700">{{ \Illuminate\Support\Str::limit($report->description, 180) }}</p>
                    </div>
                    <a href="{{ route('owner.reports.show', $report->id) }}"
                        class="inline-flex items-center rounded-full bg-[#173423] px-5 py-3 text-sm font-semibold text-white hover:bg-[#0f281b]">
                        Open case
                    </a>
                </div>
            </article>
        @empty
            <div class="rounded-[28px] bg-white px-6 py-12 text-center text-gray-500 shadow">
                No neighbourhood reports yet.
            </div>
        @endforelse
    </div>
</div>
@endsection
