@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#f9f7ef] px-4 py-12">
    <div class="w-full max-w-2xl rounded-[34px] bg-white p-8 shadow-[0_24px_80px_rgba(47,62,42,0.10)] ring-1 ring-black/5 sm:p-10">
        <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-[#eef6e7] text-green-700">
            <i class="fa-regular fa-envelope text-2xl"></i>
        </div>

        <p class="mt-6 text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Verify Email</p>
        <h1 class="mt-3 text-3xl font-semibold text-gray-900">Check your inbox to verify your account.</h1>
        <p class="mt-4 text-sm leading-7 text-gray-600">
            Thanks for signing up. Before getting started, please verify your email address by clicking the link we just sent you.
            If you did not receive the email, you can request another verification link below.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                A new verification link has been sent to the email address you provided during registration.
            </div>
        @endif

        <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                    Resend Verification Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="text-sm font-medium text-gray-600 underline-offset-4 transition hover:text-gray-900 hover:underline">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
