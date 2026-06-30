@extends('profile.Customers.app_customers')

@section('content')
<div class="min-h-screen bg-[#f6f1e5] py-10">
    <div class="mx-auto max-w-7xl px-6">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">My Wishlist</p>
                <h1 class="mt-3 text-3xl font-semibold text-gray-900">Saved homestays</h1>
                <p class="mt-2 text-sm leading-6 text-gray-600">
                    @if($wishlisted->count() > 0)
                        You have saved {{ $wishlisted->count() }} homestay{{ $wishlisted->count() > 1 ? 's' : '' }} for later.
                    @else
                        Save your favourite homestays here so you can book them later.
                    @endif
                </p>
            </div>
            <a href="{{ route('home-page') }}"
                class="inline-flex items-center justify-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                <i class="fa-solid fa-magnifying-glass mr-2"></i> Explore homes
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl bg-green-50 px-5 py-3 text-sm font-medium text-green-700 ring-1 ring-green-200">
                <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        @if($wishlisted->count() > 0)
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($wishlisted as $homestay)
                    <div class="group relative overflow-hidden rounded-[28px] bg-white shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5 transition hover:shadow-xl">
                        <div class="h-48 overflow-hidden bg-gray-100">
                            @if($homestay->primary_image)
                                <img src="{{ asset('storage/' . $homestay->primary_image) }}"
                                    alt="{{ $homestay->homestay_name }}"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    onerror="this.src='{{ asset('assets/images/homepage/home.jpg') }}'">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-green-100 to-emerald-200">
                                    <i class="fa-solid fa-house text-5xl text-green-300"></i>
                                </div>
                            @endif

                            <div class="absolute right-3 top-3">
                                <form action="{{ route('user.wishlist.toggle', $homestay->homestay_id) }}" method="POST"
                                    onsubmit="return confirmRemove(event, '{{ $homestay->homestay_name }}')">
                                    @csrf
                                    <button type="submit"
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-pink-500 shadow-lg backdrop-blur transition hover:bg-white hover:text-red-500"
                                        title="Remove from wishlist">
                                        <i class="fa-solid fa-heart text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="p-5">
                            <a href="{{ route('user.homestays.show', $homestay->homestay_id) }}" class="block">
                                <h3 class="text-lg font-semibold text-gray-900 hover:text-green-700 transition">
                                    {{ $homestay->homestay_name }}
                                </h3>
                            </a>
                            <p class="mt-1 text-sm text-gray-500">
                                <i class="fa-solid fa-location-dot mr-1 text-green-600"></i>
                                {{ trim(collect([$homestay->city, $homestay->state])->filter()->implode(', ')) ?: 'Malaysia' }}
                            </p>

                            <div class="mt-3 flex items-center justify-between">
                                <p class="text-base font-bold text-green-700">
                                    RM {{ number_format($homestay->base_price ?? 0, 0) }}
                                    <span class="text-sm font-normal text-gray-400">/night</span>
                                </p>
                                @if($homestay->avg_rating > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        <i class="fa-solid fa-star text-amber-500"></i>
                                        {{ number_format($homestay->avg_rating, 1) }}
                                        @if($homestay->review_count > 0)
                                            <span class="text-gray-400">({{ $homestay->review_count }})</span>
                                        @endif
                                    </span>
                                @endif
                            </div>

                            <div class="mt-1 text-xs text-gray-400">
                                <i class="fa-solid fa-user-group mr-1"></i> Up to {{ $homestay->max_guest }} guest{{ $homestay->max_guest > 1 ? 's' : '' }}
                            </div>

                            <div class="mt-4 flex gap-2">
                                <a href="{{ route('user.homestays.show', $homestay->homestay_id) }}"
                                    class="flex-1 rounded-full bg-green-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-green-700">
                                    View details
                                </a>
                                <form action="{{ route('user.wishlist.toggle', $homestay->homestay_id) }}" method="POST"
                                    class="shrink-0">
                                    @csrf
                                    <button type="submit"
                                        class="flex h-10 w-10 items-center justify-center rounded-full border border-pink-200 text-pink-500 transition hover:bg-pink-50 hover:text-red-500"
                                        title="Remove from wishlist">
                                        <i class="fa-solid fa-heart-circle-minus"></i>
                                    </button>
                                </form>
                            </div>

                            <p class="mt-2 text-xs text-gray-400">
                                <i class="fa-solid fa-clock mr-1"></i> Saved {{ \Carbon\Carbon::parse($homestay->saved_at)->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($recommendedHomestays->count() > 0)
                <div class="mt-14">
                    <div class="mb-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Discover more</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">You might also like</h2>
                        <p class="mt-2 text-sm text-gray-500">Popular homestays other travellers are saving.</p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($recommendedHomestays as $homestay)
                            <div class="group relative overflow-hidden rounded-[28px] bg-white shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5 transition hover:shadow-xl">
                                <div class="h-48 overflow-hidden bg-gray-100">
                                    @if($homestay->primary_image)
                                        <img src="{{ asset('storage/' . $homestay->primary_image) }}"
                                            alt="{{ $homestay->homestay_name }}"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            onerror="this.src='{{ asset('assets/images/homepage/home.jpg') }}'">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-50 to-teal-100">
                                            <i class="fa-solid fa-house text-5xl text-green-300"></i>
                                        </div>
                                    @endif

                                    <div class="absolute right-3 top-3">
                                        <form action="{{ route('user.wishlist.toggle', $homestay->homestay_id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-gray-300 shadow-lg backdrop-blur transition hover:bg-white hover:text-pink-500"
                                                title="Save to wishlist">
                                                <i class="fa-regular fa-heart text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <a href="{{ route('user.homestays.show', $homestay->homestay_id) }}" class="block">
                                        <h3 class="text-lg font-semibold text-gray-900 hover:text-green-700 transition">
                                            {{ $homestay->homestay_name }}
                                        </h3>
                                    </a>
                                    <p class="mt-1 text-sm text-gray-500">
                                        <i class="fa-solid fa-location-dot mr-1 text-green-600"></i>
                                        {{ trim(collect([$homestay->city, $homestay->state])->filter()->implode(', ')) ?: 'Malaysia' }}
                                    </p>

                                    <div class="mt-3 flex items-center justify-between">
                                        <p class="text-base font-bold text-green-700">
                                            RM {{ number_format($homestay->base_price ?? 0, 0) }}
                                            <span class="text-sm font-normal text-gray-400">/night</span>
                                        </p>
                                        @if($homestay->avg_rating > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                                <i class="fa-solid fa-star text-amber-500"></i>
                                                {{ number_format($homestay->avg_rating, 1) }}
                                                @if($homestay->review_count > 0)
                                                    <span class="text-gray-400">({{ $homestay->review_count }})</span>
                                                @endif
                                            </span>
                                        @endif
                                    </div>

                                    <a href="{{ route('user.homestays.show', $homestay->homestay_id) }}"
                                        class="mt-4 block rounded-full bg-green-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-green-700">
                                        View details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="rounded-[30px] bg-white p-12 text-center shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <div class="flex justify-center mb-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-pink-50">
                        <i class="fa-regular fa-heart text-3xl text-pink-300"></i>
                    </div>
                </div>
                <p class="text-xl font-semibold text-gray-900">Your wishlist is empty</p>
                <p class="mt-3 text-sm text-gray-500 max-w-md mx-auto">
                    Tap the heart icon on any homestay to save it here. Start browsing and find places you love.
                </p>
                <a href="{{ route('home-page') }}"
                    class="mt-6 inline-flex items-center rounded-full bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i> Browse homestays
                </a>
            </div>

            @if($recommendedHomestays->count() > 0)
                <div class="mt-14">
                    <div class="mb-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Discover</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Popular homestays</h2>
                        <p class="mt-2 text-sm text-gray-500">Top-rated places other guests love.</p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($recommendedHomestays as $homestay)
                            <div class="group relative overflow-hidden rounded-[28px] bg-white shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5 transition hover:shadow-xl">
                                <div class="h-48 overflow-hidden bg-gray-100">
                                    @if($homestay->primary_image)
                                        <img src="{{ asset('storage/' . $homestay->primary_image) }}"
                                            alt="{{ $homestay->homestay_name }}"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            onerror="this.src='{{ asset('assets/images/homepage/home.jpg') }}'">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-50 to-teal-100">
                                            <i class="fa-solid fa-house text-5xl text-green-300"></i>
                                        </div>
                                    @endif

                                    <div class="absolute right-3 top-3">
                                        <form action="{{ route('user.wishlist.toggle', $homestay->homestay_id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-gray-300 shadow-lg backdrop-blur transition hover:bg-white hover:text-pink-500"
                                                title="Save to wishlist">
                                                <i class="fa-regular fa-heart text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <a href="{{ route('user.homestays.show', $homestay->homestay_id) }}" class="block">
                                        <h3 class="text-lg font-semibold text-gray-900 hover:text-green-700 transition">
                                            {{ $homestay->homestay_name }}
                                        </h3>
                                    </a>
                                    <p class="mt-1 text-sm text-gray-500">
                                        <i class="fa-solid fa-location-dot mr-1 text-green-600"></i>
                                        {{ trim(collect([$homestay->city, $homestay->state])->filter()->implode(', ')) ?: 'Malaysia' }}
                                    </p>

                                    <div class="mt-3 flex items-center justify-between">
                                        <p class="text-base font-bold text-green-700">
                                            RM {{ number_format($homestay->base_price ?? 0, 0) }}
                                            <span class="text-sm font-normal text-gray-400">/night</span>
                                        </p>
                                        @if($homestay->avg_rating > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                                <i class="fa-solid fa-star text-amber-500"></i>
                                                {{ number_format($homestay->avg_rating, 1) }}
                                                @if($homestay->review_count > 0)
                                                    <span class="text-gray-400">({{ $homestay->review_count }})</span>
                                                @endif
                                            </span>
                                        @endif
                                    </div>

                                    <a href="{{ route('user.homestays.show', $homestay->homestay_id) }}"
                                        class="mt-4 block rounded-full bg-green-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-green-700">
                                        View details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
function confirmRemove(e, name) {
    e.preventDefault();
    Swal.fire({
        title: 'Remove from wishlist?',
        text: '"' + name + '" will be removed from your saved list.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove',
        cancelButtonText: 'Keep it',
        confirmButtonColor: '#dc2626',
        customClass: { popup: 'rounded-[28px]' },
    }).then((result) => {
        if (result.isConfirmed) {
            e.target.submit();
        }
    });
    return false;
}
</script>
@endpush
@endsection