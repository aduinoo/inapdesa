@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col lg:flex-row">
    <div class="relative hidden lg:flex lg:w-1/2 bg-cover bg-center"
        style="background-image: linear-gradient(rgba(14, 42, 28, 0.45), rgba(14, 42, 28, 0.45)), url('{{ asset('assets/images/homepage/login-hero.jpg') }}');">
        <div class="relative z-10 flex flex-col justify-center px-16 text-white">
            <p class="text-sm uppercase tracking-[0.4em] text-green-100/80">Choose A New Password</p>
            <h1 class="mt-4 text-4xl font-bold leading-tight">Create a fresh password for your account.</h1>
            <p class="mt-4 max-w-md text-sm text-green-50/90">
                Use a strong password you have not used before. Once saved, you will be redirected back to login.
            </p>
        </div>
    </div>

    <div class="flex flex-1 items-center justify-center bg-[#f9f7ef] px-4 py-12">
        <div class="w-full max-w-md rounded-[30px] bg-white p-8 shadow-[0_20px_60px_rgba(47,62,42,0.10)] ring-1 ring-black/5">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Reset Password</p>
            <h2 class="mt-3 text-3xl font-semibold text-gray-900">Change your password</h2>

            <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full rounded-full bg-green-600 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                    Save New Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
