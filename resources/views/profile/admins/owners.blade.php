@extends('layouts.app_admin')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-10">

    <h1 class="text-2xl font-bold mb-6">Owners Management</h1>

    {{-- Success / Error --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- ================= PENDING OWNER REQUESTS ================= -->
    <div class="mb-12">
        <h2 class="text-xl font-semibold mb-4 text-yellow-700">
            Pending Homestay Owner Requests
        </h2>

        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-yellow-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">

                    @forelse($pendingOwners as $user)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <form method="POST"
                                      action="{{ route('admin.approve-owner', $user) }}"
                                      class="inline">
                                    @csrf
                                    <button
                                        class="rounded-lg bg-green-600 px-3 py-1 text-white hover:bg-green-700">
                                        Approve
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                No pending requests
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= ALL USERS ================= -->
    <div>
        <h2 class="text-xl font-semibold mb-4">
            Users List
        </h2>

        <div class="bg-white rounded-xl shadow overflow-x-auto">
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
                                @if($user->role == 2)
                                    <span class="text-gray-600">Customer</span>
                                @elseif($user->role == 3)
                                    <span class="text-green-600 font-semibold">Owner</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <button
                                    onclick="confirmDelete({{ $user->id }})"
                                    class="rounded-lg bg-red-500 px-3 py-1 text-white hover:bg-red-600">
                                    Delete
                                </button>

                                <form id="delete-form-{{ $user->id }}"
                                      method="POST"
                                      action="{{ route('admin.users.delete', $user) }}"
                                      class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

</div>

<!-- ================= SWEETALERT ================= -->
<script>
    function confirmDelete(userId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This user will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + userId).submit();
            }
        });
    }
</script>

@endsection
