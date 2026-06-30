@extends('profile.admins.app_admin')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-10">
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Platform oversight</p>
        <h1 class="mt-3 text-3xl font-semibold text-gray-900">Neighbourhood reports</h1>
        <p class="mt-2 text-sm text-gray-600">
            Admin can review every neighbourhood concern, monitor the host response, and send official InapDesa messages to the host if platform intervention is needed.
        </p>
    </div>

    <div class="overflow-hidden rounded-[28px] bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-[#f8f5ee] text-left text-gray-600">
                    <tr>
                        <th class="px-6 py-4">Homestay</th>
                        <th class="px-6 py-4">Guest</th>
                        <th class="px-6 py-4">Host</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eee6d9]">
                    @forelse ($reports as $report)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $report->homestay_name }}</p>
                                <p class="mt-1 text-gray-500">Booking #{{ $report->booking_id }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $report->customer_name }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $report->owner_name }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]
                                    {{ $report->status === 'settled' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $report->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.reports.show', $report->id) }}"
                                    class="inline-flex items-center rounded-full bg-green-600 px-4 py-2 text-xs font-semibold text-white hover:bg-green-700">
                                    View report
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                No reports in the system right now.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
