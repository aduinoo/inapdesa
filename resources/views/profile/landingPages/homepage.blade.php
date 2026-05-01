@extends('layouts.app')

@section('content')

    <!-- ================= HERO SECTION ================= -->
    <section class="relative">

        <!-- Background image -->
        <div class="h-[360px] sm:h-[420px] bg-cover bg-center"
            style="background-image: url('{{ asset('assets/images/homepage/hero.jpg') }}');">
        </div>

        <!-- Search bar -->
        <div class="absolute inset-x-0 top-24 sm:top-32 flex justify-center px-4">
            <div class="w-full max-w-5xl bg-white rounded-3xl shadow-lg p-4 sm:p-0 sm:flex sm:items-center sm:divide-x">

                <!-- Where -->
                <div class="flex-1 px-4 py-3">
                    <p class="text-xs font-semibold text-gray-700">Where</p>
                    <input type="text" placeholder="Search for destination"
                        class="w-full border-none focus:ring-0 p-0 text-sm text-gray-500">
                </div>

                <!-- When -->
                <div class="flex-1 px-4 py-3">
                    <p class="text-xs font-semibold text-gray-700">When</p>
                    <input type="text" placeholder="Add dates"
                        class="w-full border-none focus:ring-0 p-0 text-sm text-gray-500">
                </div>

                <!-- Who -->
                <div class="flex-1 px-4 py-3">
                    <p class="text-xs font-semibold text-gray-700">Who</p>
                    <input type="text" placeholder="Add guests"
                        class="w-full border-none focus:ring-0 p-0 text-sm text-gray-500">
                </div>

                <!-- Search button -->
                <div class="px-4 py-3 sm:py-0">
                    <button
                        class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-full font-semibold">
                        Search
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= POPULAR HOMES ================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl sm:text-2xl font-semibold">
                Popular homes for your next staycation
            </h2>

            <div class="hidden sm:flex gap-2">
                <button class="w-8 h-8 rounded-full border flex items-center justify-center">‹</button>
                <button class="w-8 h-8 rounded-full border flex items-center justify-center">›</button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            @forelse ($homestays as $homestay)
                <div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden">

                    <!-- Image -->
                    <div class="relative overflow-hidden h-48 w-full" data-carousel>

                        <div class="flex transition-transform duration-700" data-carousel-track>
                            @forelse ($homestayImages[$homestay->homestay_id] ?? [] as $image)
                                <img src="{{ asset($image->image_path) }}" class="h-48 w-full min-w-full object-cover">
                            @empty
                                <img src="{{ asset('assets/images/homepage/home.jpg') }}"
                                    class="h-48 w-full min-w-full object-cover">
                            @endforelse
                        </div>

                        <!-- Heart button -->
                        <button
                            class="absolute top-3 right-3 bg-white/90 backdrop-blur p-2 rounded-full shadow text-sm z-10">
                            ♥
                        </button>
                    </div>



                    <!-- Content -->
                    <div class="p-4">
                        <p class="font-bold text-lg leading-tight mb-1">
                            {{ $homestay->homestay_name }}
                        </p>
                        <p class="font-semibold">

                            RM {{ number_format($homestay->base_price, 2) }}
                        </p>

                        <p class="text-sm text-gray-500 flex items-center gap-1">
                            📍 {{ $homestay->city ?? 'Malaysia' }}
                        </p>

                        <div class="flex text-yellow-400 text-sm mt-1">
                            ★★★★★
                        </div>
                    </div>

                </div>
            @empty
                <p class="text-gray-500 col-span-full">
                    No active homestays available.
                </p>
            @endforelse

        </div>
    </section>



            

@endsection
<script>
    document.addEventListener('DOMContentLoaded', () => {

        document.querySelectorAll('[data-carousel]').forEach(carousel => {

            const track = carousel.querySelector('[data-carousel-track]');
            const slides = track.children;
            const total = slides.length;

            if (total <= 1) return; // no need to slide

            let index = 0;

            setInterval(() => {
                index = (index + 1) % total;
                track.style.transform = `translateX(-${index * 100}%)`;
            }, 2500); // ⏱️ 2.5 seconds
        });

    });
</script>
