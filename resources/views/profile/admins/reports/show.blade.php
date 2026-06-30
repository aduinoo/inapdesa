@extends('profile.admins.app_admin')

@section('content')
<div class="mx-auto max-w-6xl px-6 py-10">
    <div class="mb-8">
        <a href="{{ route('admin.reports.index') }}" class="text-sm font-medium text-green-700 hover:text-green-800">&lt; Back to reports</a>
        <h1 class="mt-4 text-3xl font-semibold text-gray-900">Neighbourhood report #{{ $reportRecord->id }}</h1>
        <p class="mt-2 text-sm text-gray-600">Admin view is read-only for the report itself. You can monitor the host follow-up and send official InapDesa guidance to the host when required.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <section class="space-y-6">
            <div class="rounded-[28px] bg-white p-8 shadow">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-xl font-semibold text-gray-900">{{ $reportRecord->homestay_name }}</h2>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]
                        {{ $reportRecord->status === 'settled' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $reportRecord->status }}
                    </span>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-3 text-sm text-gray-700">
                    <div class="rounded-2xl bg-[#fbf7ee] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Guest</p>
                        <p class="mt-2 font-semibold text-gray-900">{{ $reportRecord->customer_name }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#fbf7ee] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Host</p>
                        <p class="mt-2 font-semibold text-gray-900">{{ $reportRecord->owner_name }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#fbf7ee] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Booking</p>
                        <p class="mt-2 font-semibold text-gray-900">#{{ $reportRecord->booking_id }}</p>
                    </div>
                </div>

                <div class="mt-6 rounded-[24px] border border-[#ece4d8] bg-white p-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Reported issue</p>
                    <p class="mt-3 text-sm leading-7 text-gray-700">{{ $reportRecord->description }}</p>
                </div>
            </div>

            <div class="rounded-[28px] bg-white p-8 shadow">
                <h2 class="text-xl font-semibold text-gray-900">Case timeline</h2>
                <div class="mt-6 space-y-4">
                    @forelse ($messages as $message)
                        <div class="rounded-2xl border border-[#ede5d8] p-4 {{ (int) $message->sender_id === (int) auth()->id() ? 'bg-[#f5fbf3]' : 'bg-[#fcfaf6]' }}">
                            <div class="flex items-center justify-between gap-3 text-xs uppercase tracking-[0.2em] text-gray-500">
                                <span>{{ $message->sender_name }} @if ((int) $message->sender_role === 1) | Admin @elseif ((int) $message->sender_role === 3) | Host @else | Guest @endif</span>
                                <span>{{ \Carbon\Carbon::parse($message->created_at)->format('d M Y h:i A') }}</span>
                            </div>
                            <p class="mt-3 text-sm leading-7 text-gray-700">{{ $message->message }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No messages on this report yet.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="rounded-[28px] bg-white p-8 shadow">
                <h2 class="text-xl font-semibold text-gray-900">Official message to host</h2>
                <p class="mt-3 text-sm text-gray-600">
                    Send a formal InapDesa note if the host needs direction, escalation, or a platform reminder.
                </p>

                <form method="POST" action="{{ route('admin.reports.messages.store', $reportRecord->id) }}" class="mt-6">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea name="message" rows="5"
                        class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600"
                        placeholder="Example: Please investigate this complaint today and update the platform with the resolution steps taken.">{{ old('message') }}</textarea>
                    <button type="submit"
                        class="mt-4 inline-flex items-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white hover:bg-green-700">
                        Send official message
                    </button>
                </form>
            </div>
        </aside>
    </div>
</div>
@endsection
