@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-12">
    <div class="mx-auto max-w-3xl px-6">
        <div class="rounded-[32px] bg-white p-8 shadow-[0_20px_60px_rgba(47,62,42,0.10)] ring-1 ring-black/5 sm:p-10">
            <div class="mb-8 border-b border-[#ece5d6] pb-6">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Account Settings</p>
                <h1 class="mt-3 text-3xl font-semibold text-gray-900">Edit your profile</h1>
                <p class="mt-3 text-sm leading-6 text-gray-600">
                    Update your basic account information here. If you change your email address, email verification will be required again.
                </p>
            </div>

            @if (session('status') === 'profile-updated')
                <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    Your profile has been updated successfully.
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name"
                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ url()->previous() }}"
                        class="inline-flex items-center justify-center rounded-full border border-[#d5dece] px-6 py-3 text-sm font-semibold text-gray-700 transition hover:border-green-600 hover:text-green-700">
                        Back
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                        Save Changes
                    </button>
                </div>
            </form>

            <div class="mt-10 rounded-[28px] border border-red-100 bg-red-50/60 p-6">
                <h2 class="text-xl font-semibold text-red-700">Delete account</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600">
                    This will permanently remove your account. Enter your current password to continue.
                </p>

                <form method="POST" action="{{ route('profile.destroy') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('DELETE')

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            class="mt-1 w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500">
                        @if ($errors->userDeletion->has('password'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->userDeletion->first('password') }}</p>
                        @endif
                    </div>

                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-full bg-red-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-red-700">
                        Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
