@extends($layout)

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-8">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Inbox</p>
                <h1 class="mt-3 text-3xl font-semibold text-gray-900">{{ $title }}</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $subtitle }}</p>
            </div>
            <a href="{{ $dashboardRoute }}"
                class="inline-flex items-center justify-center rounded-full border border-[#d9d0be] bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-green-700 hover:text-green-700">
                {{ $backLabel }}
            </a>
        </div>

        <section class="overflow-hidden rounded-[32px] border border-[#ebe2d3] bg-white shadow-[0_24px_70px_rgba(28,41,33,0.08)]">
            <div class="border-b border-[#efe7da] bg-gradient-to-r from-[#fcfaf5] via-white to-[#f7f3ea] px-6 py-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">All conversations</h2>
                        <p class="mt-1 text-sm text-gray-500">Open any booking chat to continue the conversation.</p>
                    </div>
                    <span class="inline-flex rounded-full bg-[#eef5eb] px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-green-700">
                        {{ $threads->count() }} thread{{ $threads->count() === 1 ? '' : 's' }}
                    </span>
                </div>
            </div>

            <div class="divide-y divide-[#f1eadf]">
                @forelse ($threads as $thread)
                    @php
                        $initials = collect(explode(' ', trim($thread->other_party_name)))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                            ->implode('');
                    @endphp

                    <a href="{{ route($showRoute, $thread->booking_id) }}"
                        class="group block px-6 py-5 transition hover:bg-[#fcfaf5]">
                        <div class="flex items-start gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#173423] text-sm font-semibold text-white shadow-sm">
                                {{ $initials ?: 'RR' }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="truncate text-base font-semibold text-gray-900">
                                                {{ $thread->other_party_name }}
                                            </p>
                                            <span class="inline-flex rounded-full bg-[#f5efe3] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-600">
                                                Booking #{{ $thread->booking_id }}
                                            </span>
                                            @if ($thread->unread_count > 0)
                                                <span class="inline-flex rounded-full bg-green-600 px-3 py-1 text-[11px] font-semibold text-white">
                                                    {{ $thread->unread_count }} new
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-1 truncate text-sm font-medium text-gray-700">
                                            {{ $thread->homestay_name }}
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($thread->check_in)->format('d M Y') }} - {{ \Carbon\Carbon::parse($thread->check_out)->format('d M Y') }}
                                            · {{ trim(collect([$thread->city, $thread->state])->filter()->implode(', ')) ?: 'Malaysia' }}
                                        </p>

                                        <p class="mt-3 line-clamp-2 text-sm leading-6 text-gray-600">
                                            {{ $thread->latest_message ?: 'No messages yet. Start the conversation here.' }}
                                        </p>
                                    </div>

                                    <div class="shrink-0 text-left sm:text-right">
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">
                                            {{ $thread->booking_status }}
                                        </p>
                                        <p class="mt-2 text-sm text-gray-500">
                                            {{ $thread->latest_message_at ? \Carbon\Carbon::parse($thread->latest_message_at)->diffForHumans() : 'New thread' }}
                                        </p>
                                        <span class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-green-700 transition group-hover:translate-x-0.5">
                                            Open chat
                                            <span aria-hidden="true">&rsaquo;</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-14 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-[#eef5eb] text-2xl text-green-700">
                            &#9993;
                        </div>
                        <p class="mt-5 text-lg font-semibold text-gray-900">No chat threads yet</p>
                        <p class="mt-2 text-sm text-gray-500">A conversation will appear here once a booking exists.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
