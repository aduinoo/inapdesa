@extends('layouts.app')

@section('content')
    @php
        $totalHomestays = $homestays->count();
        $statesList = $states->implode(', ');
    @endphp

    <style>
        .hs-card-img-wrap {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
        }

        .hs-card-img-wrap img {
            transition: transform 0.4s ease;
        }

        .hs-card:hover .hs-card-img-wrap img {
            transform: scale(1.04);
        }

        .hs-card-carousel-track {
            display: flex;
            transition: transform 0.6s ease;
        }

        .hs-card-carousel-track img {
            min-width: 100%;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hs-card-carousel-dots {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 5px;
            z-index: 5;
        }

        .hs-card-carousel-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.55);
            transition: background 0.2s ease, transform 0.2s ease;
            cursor: pointer;
        }

        .hs-card-carousel-dot.active {
            background: white;
            transform: scale(1.25);
        }

        .hs-card-carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s ease;
            font-size: 14px;
            color: #374151;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .hs-card:hover .hs-card-carousel-nav {
            opacity: 1;
        }

        .hs-card-carousel-nav.prev {
            left: 8px;
        }

        .hs-card-carousel-nav.next {
            right: 8px;
        }

        .hs-card-heart {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 5;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.15s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .hs-card-heart:hover {
            transform: scale(1.12);
        }

        .hs-card-heart i {
            font-size: 14px;
            color: #6b7280;
            transition: color 0.15s ease;
        }

        .hs-card-heart.liked i {
            color: #ef4444;
        }

        .state-filter-btn {
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .state-filter-btn.active {
            background-color: #166534;
            color: white;
            border-color: #166534;
        }
    </style>

    <div class="bg-[#f5efdf]">
        <section class="relative isolate overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center"
                style="background-image:
                    linear-gradient(115deg, rgba(8, 31, 19, 0.82), rgba(8, 31, 19, 0.38) 52%, rgba(210, 161, 64, 0.18)),
                    url('{{ asset('assets/images/homepage/hero.jpg') }}');">
            </div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.18),transparent_32%),radial-gradient(circle_at_bottom_right,rgba(243,208,142,0.22),transparent_28%)]"></div>
            <div class="absolute -right-16 top-20 h-64 w-64 rounded-full bg-[#f3d08e]/20 blur-3xl"></div>
            <div class="absolute left-[-4rem] bottom-[-3rem] h-52 w-52 rounded-full bg-emerald-200/10 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 pb-10 pt-10 sm:px-6 lg:px-8 lg:pb-14 lg:pt-14">
                <div class="max-w-3xl text-white">
                    <p class="text-xs font-semibold uppercase tracking-[0.42em] text-emerald-200/90">InapDesa</p>
                    <h1 class="mt-3 text-3xl font-semibold leading-tight sm:text-4xl">
                        Find your
                        <span class="text-[#f3d08e]">perfect stay</span>
                        across Malaysia
                    </h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-white/82 sm:text-base">
                        {{ $totalHomestays }} stays in {{ $states->count() }} states. From RM {{ number_format($homestays->min('base_price') ?? 0, 0) }}/night.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#stays"
                            class="inline-flex items-center justify-center rounded-full bg-[#f3d08e] px-5 py-2.5 text-sm font-semibold text-[#173423] transition hover:bg-[#f0c566]">
                            <i class="fa-solid fa-magnifying-glass mr-2"></i>
                            Browse stays
                        </a>
                        <a href="{{ route('maps') }}"
                            class="inline-flex items-center justify-center rounded-full border border-white/30 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/18">
                            <i class="fa-solid fa-map-location-dot mr-2"></i>
                            Map view
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="stays" class="mx-auto max-w-screen-2xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
            <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.34em] text-green-700/70">InapDesa Stays</p>
                    <h2 class="mt-3 text-3xl font-semibold text-[#173423] sm:text-4xl">
                        All homestays across Malaysia
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-gray-600">
                        {{ $totalHomestays }} stays in {{ $states->count() }} states. Filter by location or browse all.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('maps') }}"
                        class="inline-flex items-center justify-center rounded-full border border-[#d9d0be] bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-green-700 hover:text-green-700">
                        <i class="fa-solid fa-map-location-dot mr-2"></i>
                        Map view
                    </a>
                </div>
            </div>

            @if($states->count() > 1)
                <div class="mb-8 flex flex-wrap gap-2" x-data="{ activeFilter: 'all' }">
                    <button type="button"
                        class="state-filter-btn rounded-full border border-[#d9d0be] bg-white px-4 py-2 text-sm font-medium text-gray-700"
                        :class="{ 'active': activeFilter === 'all' }"
                        @click="activeFilter = 'all'; document.querySelectorAll('.hs-card').forEach(c => c.style.display = '')">
                        All states
                    </button>
                    @foreach ($states as $state)
                        <button type="button"
                            class="state-filter-btn rounded-full border border-[#d9d0be] bg-white px-4 py-2 text-sm font-medium text-gray-700"
                            :class="{ 'active': activeFilter === '{{ $state }}' }"
                            @click="activeFilter = '{{ $state }}'; document.querySelectorAll('.hs-card').forEach(c => { c.style.display = c.dataset.state === '{{ $state }}' ? '' : 'none' })">
                            {{ $state }}
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                @forelse ($homestays as $homestay)
                    @php
                        $images = $homestayImages[$homestay->homestay_id] ?? [];
                        $primaryImage = $images[0]->image_path ?? 'assets/images/homepage/hero.jpg';
                        $hsAmenities = $homestayAmenities[$homestay->homestay_id] ?? collect([]);
                        $amenityLabels = $hsAmenities->map(fn($a) => $amenityNames[$a->amenity_id] ?? null)->filter()->take(4);
                    @endphp

                    <article class="hs-card group rounded-[22px] bg-white shadow-[0_14px_44px_rgba(47,62,42,0.07)] ring-1 ring-black/5 transition duration-300 hover:-translate-y-1 hover:shadow-[0_22px_60px_rgba(21,43,29,0.14)]"
                        data-state="{{ $homestay->state ?? '' }}">

                        <div class="hs-card-img-wrap aspect-[4/3]" data-carousel>
                            @if(count($images) > 1)
                                <div class="hs-card-carousel-track h-full" data-carousel-track>
                                    @foreach ($images as $img)
                                        <img src="{{ asset($img->image_path) }}" alt="{{ $homestay->homestay_name }}"
                                            loading="lazy">
                                    @endforeach
                                </div>
                                <div class="hs-card-carousel-dots" data-carousel-dots>
                                    @foreach ($images as $i => $img)
                                        <span class="hs-card-carousel-dot {{ $i === 0 ? 'active' : '' }}" data-carousel-dot-index="{{ $i }}"></span>
                                    @endforeach
                                </div>
                                <div class="hs-card-carousel-nav prev" data-carousel-prev>
                                    <i class="fa-solid fa-chevron-left"></i>
                                </div>
                                <div class="hs-card-carousel-nav next" data-carousel-next>
                                    <i class="fa-solid fa-chevron-right"></i>
                                </div>
                            @else
                                <img src="{{ asset($primaryImage) }}" alt="{{ $homestay->homestay_name }}"
                                    class="h-full w-full object-cover" loading="lazy">
                            @endif

                            <div class="absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-black/35 to-transparent"></div>
                            <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/45 to-transparent"></div>

                            @if($homestay->state)
                                <span class="absolute left-3 top-3 z-5 inline-flex items-center gap-1 rounded-full bg-white/88 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-green-700 backdrop-blur shadow-sm">
                                    <i class="fa-solid fa-location-dot text-[10px]"></i>
                                    {{ $homestay->state }}
                                </span>
                            @endif

                            <button class="hs-card-heart" data-heart="{{ $homestay->homestay_id }}" onclick="this.classList.toggle('liked')">
                                <i class="fa-regular fa-heart"></i>
                            </button>

                            <a href="{{ route('user.homestays.show', $homestay->homestay_id) }}"
                                class="absolute inset-x-3 bottom-3 z-5 flex items-end justify-between gap-3 text-white">
                                <div>
                                    <p class="text-lg font-semibold leading-snug drop-shadow-md">{{ $homestay->homestay_name }}</p>
                                    <p class="mt-0.5 text-sm text-white/80 drop-shadow">{{ $homestay->city ?? 'Malaysia' }}</p>
                                </div>
                                <span class="rounded-full bg-white/16 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-[0.15em] text-white backdrop-blur-sm drop-shadow">
                                    RM {{ number_format($homestay->base_price, 0) }}
                                </span>
                            </a>
                        </div>

                        <div class="p-4">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-xl font-bold text-[#173423]">RM {{ number_format($homestay->base_price, 2) }}</p>
                                    <p class="text-xs text-gray-500">per night</p>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                    <i class="fa-solid fa-user-group text-green-700/70"></i>
                                    <span class="font-medium">{{ $homestay->max_guest }} guests</span>
                                </div>
                            </div>

                            @if($amenityLabels->count())
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($amenityLabels as $label)
                                        <span class="inline-flex items-center rounded-full bg-[#f3f8f1] px-2.5 py-1 text-[11px] font-medium text-green-700">
                                            {{ $label }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-gray-600">
                                {{ Str::limit($homestay->description ?? '', 90) }}
                            </p>

                            <div class="mt-4 flex gap-1.5">
                                <a href="{{ route('user.homestays.show', $homestay->homestay_id) }}"
                                    class="inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-full border border-green-700 px-3 py-2 text-xs font-semibold text-green-700 transition hover:bg-green-50">
                                    View stay
                                </a>
                                <a href="{{ route('user.homestays.show', $homestay->homestay_id) }}#reserve"
                                    class="inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-full bg-green-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-green-700">
                                    Book now
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-[30px] bg-white px-6 py-16 text-center shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                        <i class="fa-solid fa-house-chimney text-3xl text-gray-400"></i>
                        <p class="mt-4 text-lg font-semibold text-gray-900">No active homestays available yet.</p>
                        <p class="mt-2 text-sm text-gray-500">Once hosts publish listings, they'll appear here.</p>
                    </div>
                @endforelse
            </div>
        </section>

      
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-carousel]').forEach((carousel) => {
                const track = carousel.querySelector('[data-carousel-track]');
                if (!track) return;

                const slides = track.children;
                const total = slides.length;
                if (total <= 1) return;

                const dotsContainer = carousel.querySelector('[data-carousel-dots]');
                const dots = dotsContainer ? dotsContainer.querySelectorAll('[data-carousel-dot-index]') : [];
                const prevBtn = carousel.querySelector('[data-carousel-prev]');
                const nextBtn = carousel.querySelector('[data-carousel-next]');
                let current = 0;

                const goTo = (idx) => {
                    current = ((idx % total) + total) % total;
                    track.style.transform = `translateX(-${current * 100}%)`;
                    dots.forEach((d, i) => d.classList.toggle('active', i === current));
                };

                if (prevBtn) prevBtn.addEventListener('click', (e) => { e.stopPropagation(); goTo(current - 1); });
                if (nextBtn) nextBtn.addEventListener('click', (e) => { e.stopPropagation(); goTo(current + 1); });
                dots.forEach((d) => d.addEventListener('click', (e) => { e.stopPropagation(); goTo(Number(d.dataset.carouselDotIndex)); }));

                let autoInterval = setInterval(() => goTo(current + 1), 3500);

                carousel.addEventListener('mouseenter', () => clearInterval(autoInterval));
                carousel.addEventListener('mouseleave', () => {
                    autoInterval = setInterval(() => goTo(current + 1), 3500);
                });
            });
        });
    </script>
@endpush
