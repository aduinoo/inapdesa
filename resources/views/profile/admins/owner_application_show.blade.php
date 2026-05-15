@extends('profile.admins.app_admin')

@section('content')
<div class="mx-auto max-w-6xl px-6 py-10">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Owner Application Review</p>
            <h1 class="mt-2 text-3xl font-semibold text-gray-900">{{ $application->full_name }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ $application->email }} · Submitted {{ \Carbon\Carbon::parse($application->created_at)->format('d M Y, h:i A') }}</p>
        </div>
        <a href="{{ route('admin.dashboard') }}"
            class="inline-flex items-center justify-center rounded-full border border-[#d7e0d1] px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-green-600 hover:text-green-700">
            Back to dashboard
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
            <h2 class="text-2xl font-semibold text-gray-900">Applicant details</h2>
            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Full name</p><p class="mt-2 text-gray-900">{{ $application->full_name }}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Phone</p><p class="mt-2 text-gray-900">{{ $application->phone_number }}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">IC number</p><p class="mt-2 text-gray-900">{{ $application->ic_number }}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Date of birth</p><p class="mt-2 text-gray-900">{{ $application->date_of_birth ? \Carbon\Carbon::parse($application->date_of_birth)->format('d M Y') : '—' }}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Occupation</p><p class="mt-2 text-gray-900">{{ $application->occupation ?: '—' }}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Status</p><p class="mt-2 text-gray-900 capitalize">{{ $application->status }}</p></div>
            </div>

            <div class="mt-6">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Address</p>
                <p class="mt-2 text-gray-900">
                    {{ $application->address_line }}, {{ $application->city }}, {{ $application->state }}, {{ $application->postcode }}
                </p>
            </div>

            <div class="mt-6">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Hosting experience / notes</p>
                <p class="mt-2 whitespace-pre-line text-gray-900">{{ $application->hosting_experience ?: '—' }}</p>
            </div>

            <div class="mt-6">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Admin notes</p>
                <p class="mt-2 whitespace-pre-line text-gray-900">{{ $application->admin_notes ?: '—' }}</p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <h2 class="text-2xl font-semibold text-gray-900">Documents</h2>
                <div class="mt-6 space-y-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">IC document</p>
                        <a href="{{ asset($application->ic_document_path) }}" target="_blank" class="mt-2 inline-flex text-sm font-medium text-green-700 hover:underline">
                            Open uploaded IC
                        </a>
                    </div>
                    @if ($application->supporting_document_path)
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Supporting document</p>
                            <a href="{{ asset($application->supporting_document_path) }}" target="_blank" class="mt-2 inline-flex text-sm font-medium text-green-700 hover:underline">
                                Open supporting document
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <h2 class="text-2xl font-semibold text-gray-900">Review action</h2>

                @if ($application->status === 'pending')
                    <form method="POST" action="{{ route('admin.owner-applications.approve', $application->id) }}" class="mt-6 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Admin notes (optional for approval)</label>
                            <textarea name="admin_notes" rows="4" class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600"></textarea>
                        </div>
                        <button type="submit" class="w-full rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                            Approve application
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.owner-applications.reject', $application->id) }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reason for rejection</label>
                            <textarea name="admin_notes" rows="4" required class="mt-1 w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500"></textarea>
                            @error('admin_notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="w-full rounded-full bg-red-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-red-700">
                            Reject application
                        </button>
                    </form>
                @else
                    <div class="mt-6 rounded-2xl border border-[#e7e2d5] bg-[#fcfaf4] px-4 py-4 text-sm text-gray-700">
                        Reviewed by {{ $application->reviewer_name ?: 'Admin' }} on
                        {{ $application->reviewed_at ? \Carbon\Carbon::parse($application->reviewed_at)->format('d M Y, h:i A') : '—' }}.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
