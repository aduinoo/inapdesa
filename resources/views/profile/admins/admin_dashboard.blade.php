@extends('profile.admins.app_admin')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-10">
    <div class="mb-10 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-6 shadow">
            <p class="text-sm text-gray-500">Total Users</p>
            <p class="mt-2 text-3xl font-semibold text-green-700">{{ $totalUsers }}</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow">
            <p class="text-sm text-gray-500">Total Homestays</p>
            <p class="mt-2 text-3xl font-semibold text-green-700">{{ $totalHomestays }}</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow">
            <p class="text-sm text-gray-500">Total Bookings</p>
            <p class="mt-2 text-3xl font-semibold text-green-700">{{ $totalBookings }}</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow">
            <p class="text-sm text-gray-500">Pending Reports</p>
            <p class="mt-2 text-3xl font-semibold text-red-600">{{ $pendingReports }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-100 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-lg bg-red-100 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-10">
        <h2 class="mb-6 text-2xl font-bold">Pending Owner Applications</h2>

        <div class="overflow-x-auto rounded-xl bg-white shadow">
            <table class="min-w-full text-sm">
                <thead class="bg-yellow-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-6 py-3">Applicant</th>
                        <th class="px-6 py-3">Phone</th>
                        <th class="px-6 py-3">Location</th>
                        <th class="px-6 py-3">Applied</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($pendingOwnerApplications as $application)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-medium">{{ $application->name }}</p>
                                <p class="text-gray-500">{{ $application->email }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $application->phone_number }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $application->city }}, {{ $application->state }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($application->applied_at)->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.owner-applications.show', $application->id) }}"
                                    class="inline-flex items-center rounded-lg bg-green-600 px-3 py-2 text-white hover:bg-green-700">
                                    Review credentials
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-gray-500">
                                No pending owner applications right now.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <h2 class="mb-6 text-2xl font-bold">User Management</h2>

    <div class="overflow-x-auto rounded-xl bg-white shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr class="text-left text-gray-600">
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 font-medium">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @switch($user->role)
                                @case(1)
                                    <span class="font-semibold text-blue-600">Admin</span>
                                    @break
                                @case(2)
                                    <span class="text-gray-600">Customer</span>
                                    @break
                                @case(3)
                                    <span class="font-semibold text-green-600">Owner</span>
                                    @break
                                @default
                                    <span class="text-gray-400">Unknown</span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-gray-400">—</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
