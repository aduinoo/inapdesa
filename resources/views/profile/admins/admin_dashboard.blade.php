@extends('layouts.app_admin')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-10">

    <h2 class="text-2xl font-bold mb-6">User Management</h2>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white rounded-xl shadow">
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
                        <td class="px-6 py-4 font-medium">
                            {{ $user->name }}
                        </td>

                        <td class="px-6 py-4 text-gray-600">
                            {{ $user->email }}
                        </td>

                        <td class="px-6 py-4">
                            @switch($user->role)
                                @case(1)
                                    <span class="text-blue-600 font-semibold">Admin</span>
                                    @break
                                @case(2)
                                    <span class="text-gray-600">Customer</span>
                                    @break
                                @case(3)
                                    <span class="text-green-600 font-semibold">Owner</span>
                                    @break
                                @case(4)
                                    <span class="text-yellow-600 font-semibold">Pending Owner</span>
                                    @break
                            @endswitch
                        </td>

                        <td class="px-6 py-4">

                            @if($user->role == 4)
                                <form method="POST"
                                      action="{{ route('admin.approve-owner', $user) }}">
                                    @csrf
                                    <button
                                        class="rounded-lg bg-green-600 px-3 py-1 text-white hover:bg-green-700">
                                        Approve
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif

                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>

</div>

@endsection
