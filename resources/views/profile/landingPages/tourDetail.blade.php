@extends('layouts.app')

@section('content')
    <main class="bg-[#f6f5ee]">
        <section class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
            <a href="{{ route('attractions-and-tours') }}" class="text-sm text-green-700 hover:text-green-800">
                Back to Attractions & Tours
            </a>

            <div class="mt-5 grid lg:grid-cols-[1.2fr_0.8fr] gap-8 items-start">
                <div class="bg-white rounded-2xl shadow overflow-hidden">
                    <div class="h-[280px] sm:h-[380px] bg-gray-200">
                        <img src="{{ $tour['pictures'][0] ?? asset('assets/images/attractions/kl.jpg') }}"
                            data-fallback-src="{{ asset('assets/images/attractions/kl.jpg') }}"
                            alt="{{ $tour['name'] }}"
                            class="tour-image w-full h-full object-cover">
                    </div>
                    @if (!empty($tour['photo_attribution']['name']))
                        <div class="px-6 pt-3 text-xs text-gray-500">
                            Photo:
                            @if (!empty($tour['photo_attribution']['uri']))
                                <a href="{{ $tour['photo_attribution']['uri'] }}" target="_blank" rel="noopener noreferrer"
                                    class="hover:text-gray-700">
                                    {{ $tour['photo_attribution']['name'] }}
                                </a>
                            @else
                                {{ $tour['photo_attribution']['name'] }}
                            @endif
                        </div>
                    @endif

                    <div class="p-6 sm:p-8">
                        <p class="text-sm uppercase tracking-[0.2em] text-green-700 font-semibold">
                            {{ $city ?? 'Nearby' }}
                        </p>

                        <h1 class="mt-2 text-2xl sm:text-4xl font-bold text-gray-900">
                            {{ $tour['name'] }}
                        </h1>

                        <p class="mt-3 text-sm text-gray-500">
                            {{ $tour['categories_display'] ?? 'Tour experience' }}
                        </p>

                        <p class="mt-6 text-base leading-7 text-gray-700">
                            {{ $tour['description'] }}
                        </p>
                    </div>
                </div>

                <aside class="bg-white rounded-2xl shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Tour Information</h2>

                    <div class="mt-5 space-y-4 text-sm text-gray-700">
                        <div>
                            <p class="text-gray-500">Location</p>
                            <p class="font-medium">{{ $tour['address'] ?? ($city ?? 'Nearby you') }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Distance</p>
                            <p class="font-medium">
                                {{ !is_null($tour['distance'] ?? null) ? number_format($tour['distance'], 1) . ' km away' : 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">Google rating</p>
                            <p class="font-medium">
                                {{ !is_null($tour['rating'] ?? null) ? number_format($tour['rating'], 1) . '/5' : 'N/A' }}
                            </p>
                        </div>

                        @if (!empty($tour['maps_url']))
                            <a href="{{ $tour['maps_url'] }}" target="_blank" rel="noopener noreferrer"
                                class="block w-full text-center rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700 transition">
                                Open in Google Maps
                            </a>
                        @else
                            <div class="rounded-2xl bg-green-50 border border-green-100 p-4 text-sm text-green-900">
                                Map link is not available for this attraction right now.
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.tour-image').forEach((img) => {
                img.addEventListener('error', function handleImageError() {
                    const fallbackSrc = img.dataset.fallbackSrc;

                    if (fallbackSrc && img.src !== fallbackSrc) {
                        img.src = fallbackSrc;
                    }
                }, {
                    once: true
                });
            });
        });
    </script>
@endsection
