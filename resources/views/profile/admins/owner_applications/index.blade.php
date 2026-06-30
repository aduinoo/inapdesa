@extends('profile.admins.app_admin')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-10">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Application management</p>
            <h1 class="mt-3 text-3xl font-semibold text-gray-900">Owner applications</h1>
            <p class="mt-2 text-sm text-gray-600">
                Review and manage applications from users who want to become homestay owners.
            </p>
        </div>
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

    <div class="overflow-hidden rounded-[28px] bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-[#f8f5ee] text-left text-gray-600">
                    <tr>
                        <th class="px-6 py-4">Applicant</th>
                        <th class="px-6 py-4">Phone</th>
                        <th class="px-6 py-4">Location</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Applied</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eee6d9]">
                    @forelse ($applications as $application)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $application->name }}</p>
                                <p class="mt-1 text-gray-500">{{ $application->email }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $application->phone_number }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $application->city }}, {{ $application->state }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]
                                    @switch($application->status)
                                        @case('approved')
                                            bg-green-100 text-green-700
                                            @break
                                        @case('rejected')
                                            bg-red-100 text-red-700
                                            @break
                                        @default
                                            bg-amber-100 text-amber-700
                                    @endswitch
                                ">
                                    {{ $application->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($application->created_at)->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.owner-applications.show', $application->id) }}"
                                    class="inline-flex items-center rounded-full bg-green-600 px-4 py-2 text-xs font-semibold text-white hover:bg-green-700">
                                    Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                No owner applications yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection