@extends('profile.admins.app_admin')

@section('content')
<div class="mx-auto max-w-5xl px-6 py-10">
    <div class="rounded-[28px] bg-white p-8 shadow">
        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">Owner Applications</p>
        <h1 class="mt-3 text-3xl font-semibold text-gray-900">Use the admin dashboard to review owner applications.</h1>
        <p class="mt-4 text-sm leading-7 text-gray-600">
            Pending host upgrade requests now live in the main admin dashboard, where admins can review IC documents,
            personal information, and approve or reject each application.
        </p>
        <a href="{{ route('admin.dashboard') }}"
            class="mt-8 inline-flex items-center justify-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
            Back to admin dashboard
        </a>
    </div>
</div>
@endsection
