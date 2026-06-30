@extends('layouts.app')

@section('content')
    <div class="bg-[#f5f0de]">
        <section class="relative isolate overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(76,175,80,0.18),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(250,204,21,0.14),_transparent_28%)]"></div>
            <div class="relative mx-auto max-w-7xl px-6 py-16 lg:py-24">
                <p class="text-xs font-semibold uppercase tracking-[0.4em] text-green-700/70">{{ $eyebrow }}</p>
                <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight text-[#173423] sm:text-5xl">
                    {{ $title }}
                </h1>
                <p class="mt-6 max-w-3xl text-base leading-7 text-gray-600">
                    {{ $lead }}
                </p>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-6 pb-16 lg:pb-24">
            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ($sections as $section)
                    <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                        <h2 class="text-2xl font-semibold text-[#173423]">{{ $section['title'] }}</h2>
                        <p class="mt-4 text-sm leading-7 text-gray-600">
                            {{ $section['body'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-green-700/70">Need another page?</p>
                        <h2 class="mt-2 text-2xl font-semibold text-[#173423]">Keep exploring RumaRehat</h2>
                    </div>
                    <a href="{{ route('contact') }}"
                        class="inline-flex items-center justify-center rounded-full bg-green-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-800">
                        Contact us
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
