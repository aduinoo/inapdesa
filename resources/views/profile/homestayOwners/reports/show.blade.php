@extends('profile.homestayOwners.app_ownerHomestay')

@section('content')
<div class="mx-auto max-w-6xl px-6 py-10">
    <div class="mb-8">
        <a href="{{ route('owner.reports.index') }}" class="text-sm font-medium text-green-700 hover:text-green-800">&lt; Back to reports</a>
        <h1 class="mt-4 text-3xl font-semibold text-gray-900">Neighbourhood report #{{ $reportRecord->id }}</h1>
        <p class="mt-2 text-sm text-gray-600">This report is visible to both the host and admin. Use the booking chat with the guest for details, and update admin from this page when needed.</p>
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

                <div class="mt-6 grid gap-4 sm:grid-cols-2 text-sm text-gray-700">
                    <div class="rounded-2xl bg-[#fbf7ee] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Guest</p>
                        <p class="mt-2 font-semibold text-gray-900">{{ $reportRecord->customer_name }}</p>
                        <p class="mt-1 text-gray-500">{{ $reportRecord->customer_email }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#fbf7ee] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Booking</p>
                        <p class="mt-2 font-semibold text-gray-900">#{{ $reportRecord->booking_id }}</p>
                        <p class="mt-1 text-gray-500">Submitted {{ \Carbon\Carbon::parse($reportRecord->created_at)->format('d M Y, h:i A') }}</p>
                    </div>
                </div>

                <div class="mt-6 rounded-[24px] border border-[#ece4d8] bg-white p-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Original report</p>
                    <p class="mt-3 text-sm leading-7 text-gray-700">{{ $reportRecord->description }}</p>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('owner.messages.show', $reportRecord->booking_id) }}"
                        class="inline-flex items-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                        Chat guest for details
                    </a>

                    @if ($reportRecord->status !== 'settled')
                        <form method="POST" action="{{ route('owner.reports.settle', $reportRecord->id) }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700">
                                Mark done
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="rounded-[28px] bg-white p-8 shadow">
                <h2 class="text-xl font-semibold text-gray-900">Admin thread</h2>
                <p class="mt-3 text-sm text-gray-600">Use this section to update admin with your follow-up or resolution notes.</p>

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
                        <p class="text-sm text-gray-500">No admin messages yet.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('owner.reports.messages.store', $reportRecord->id) }}" class="mt-6">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700">Message to admin</label>
                    <textarea name="message" rows="4"
                        class="mt-2 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600"
                        placeholder="Share what you found, what you did, or what support you need from admin...">{{ old('message') }}</textarea>
                    <button type="submit"
                        class="mt-4 inline-flex items-center rounded-full bg-[#173423] px-6 py-3 text-sm font-semibold text-white hover:bg-[#0f281b]">
                        Send update to admin
                    </button>
                </form>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="rounded-[28px] bg-white p-8 shadow">
                <h2 class="text-xl font-semibold text-gray-900">Resolution checklist</h2>
                <ul class="mt-5 space-y-3 text-sm leading-7 text-gray-600">
                    <li>Confirm the guest concern through booking chat if details are incomplete.</li>
                    <li>Take any practical action needed at the property or with local contacts.</li>
                    <li>Update admin if the case needs platform support or an official record.</li>
                    <li>Mark the report done once the matter is settled.</li>
                </ul>
            </div>
        </aside>
    </div>
</div>
@endsection
