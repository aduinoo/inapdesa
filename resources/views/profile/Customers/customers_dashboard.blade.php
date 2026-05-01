@extends('profile.Customers.app_customers')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-6">
        <div class="bg-white rounded-xl shadow p-6">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold">Dashboard</h2>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        class="bg-red-500 text-white px-4 py-2 rounded-full hover:bg-red-600">
                        Log Out
                    </button>
                </form>
            </div>

            <!-- Welcome -->
            <p class="text-gray-600 mb-6">
                Welcome back, <span class="font-semibold">{{ Auth::user()->name }}</span> 👋
            </p>

            <!-- ROLE STATUS -->
            @if(Auth::user()->role == 2)
                <div class="rounded-lg bg-blue-50 text-blue-700 px-4 py-3">
                    You are currently registered as a <strong>Customer</strong>.
                </div>

            @elseif(Auth::user()->role == 4)
                <div class="rounded-lg bg-yellow-50 text-yellow-700 px-4 py-3">
                    ⏳ Your request to become a <strong>Homestay Owner</strong> is under review.
                    Please wait for admin approval.
                </div>

            @endif

        </div>
    </div>
</div>
@endsection
