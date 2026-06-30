@extends('profile.Customers.app_customers')

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-10">
    <div class="mx-auto max-w-4xl px-6">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Group Payment</p>
            <h1 class="mt-3 text-3xl font-semibold text-gray-900">Split payment with friends</h1>
            <p class="mt-2 text-sm leading-6 text-gray-600">
                Add your friends by email. The total will be split equally. Everyone must pay within 1 hour or the booking will be cancelled.
            </p>
        </div>

        @if (session('error'))
            <div class="mb-6 rounded-[22px] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-8 lg:grid-cols-[1fr_1fr]">
            <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <div class="flex gap-4">
                    <img src="{{ asset($pendingBooking['image']) }}" alt="{{ $pendingBooking['homestay_name'] }}"
                        class="h-28 w-28 rounded-[24px] object-cover">
                    <div>
                        <p class="text-xl font-semibold text-gray-900">{{ $pendingBooking['homestay_name'] }}</p>
                        <p class="mt-2 text-sm text-gray-500">{{ $pendingBooking['location'] }}</p>
                        <p class="mt-3 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($pendingBooking['check_in'])->format('d M Y') }}
                            to
                            {{ \Carbon\Carbon::parse($pendingBooking['check_out'])->format('d M Y') }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">{{ $pendingBooking['guest_count'] }} guest(s)</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-[#eee5d7] pt-6">
                    <div class="flex items-center justify-between text-base font-semibold text-gray-900">
                        <span>Total</span>
                        <span>RM {{ number_format($pendingBooking['total'], 2) }}</span>
                    </div>
                </div>
            </section>

            <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <h2 class="text-xl font-semibold text-gray-900">Invite friends</h2>
                <p class="mt-2 text-sm text-gray-500">Enter email addresses separated by commas.</p>

                <form action="{{ route('group-payment.store') }}" method="POST" class="mt-6 space-y-4" x-data="{
                    emails: '',
                    get count() {
                        if (!this.emails.trim()) return 1;
                        return this.emails.split(',').filter(e => e.trim()).length + 1;
                    },
                    get sharePerPerson() {
                        return ({{ $pendingBooking['total'] }} / this.count).toFixed(2);
                    }
                }">
                    @csrf

                    <label class="block text-sm font-medium text-gray-700">Friend email(s)</label>
                    <textarea name="emails" x-model="emails" rows="4"
                        placeholder="friend1@email.com, friend2@email.com"
                        class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none"></textarea>

                    @error('emails')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="rounded-[22px] bg-[#fbf7ee] p-4 text-sm text-gray-600">
                        <div class="flex items-center justify-between">
                            <span>People in group</span>
                            <span class="font-semibold text-gray-900" x-text="count + ' (including you)'"></span>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span>Each person pays</span>
                            <span class="font-semibold text-green-700" x-text="'RM ' + sharePerPerson"></span>
                        </div>
                        <p class="mt-3 text-xs text-gray-500">All members must pay within 1 hour. Otherwise the booking is automatically cancelled.</p>
                    </div>

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                        Create group payment
                    </button>

                    <a href="{{ route('user.bookings.payment') }}"
                        class="inline-flex w-full items-center justify-center rounded-full border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        Pay full amount instead
                    </a>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
