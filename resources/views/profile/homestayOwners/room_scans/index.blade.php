@extends('profile.homestayOwners.app_ownerHomestay')

@section('content')
<main class="mx-auto max-w-7xl px-6 py-10">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">3D Room Scan</p>
            <h1 class="mt-3 text-3xl font-semibold text-gray-900">Homestay 360 Room Tour</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600">
                Upload one equirectangular 360 panorama for a room and open it immediately as an immersive room view.
                Optional overview video uploads still work as a guide.
            </p>
        </div>
        <div class="rounded-[24px] bg-[#eef5eb] px-5 py-4 text-sm text-green-800">
            <p class="font-semibold">Panorama mode is ready</p>
            <p class="mt-1">Single 360 photos open right away with the built-in panorama viewer.</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
            <h2 class="text-xl font-semibold text-gray-900">Upload one 360 room photo</h2>
            <p class="mt-2 text-sm text-gray-500">
                Use one stitched 360 image for one room only. Best results come from a clean equirectangular panorama
                with an approximately 2:1 ratio, like 6000 x 3000.
            </p>

            <form action="{{ route('owner.room-scans.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="homestay_id" class="mb-2 block text-sm font-semibold text-gray-700">Choose homestay</label>
                    <select id="homestay_id" name="homestay_id"
                        class="w-full rounded-2xl border border-[#ddd4c2] px-4 py-3 text-sm text-gray-700 focus:border-green-600 focus:outline-none focus:ring-0">
                        <option value="">Select a homestay</option>
                        @foreach ($homestays as $homestay)
                            <option value="{{ $homestay->homestay_id }}" @selected(old('homestay_id') == $homestay->homestay_id)>
                                {{ $homestay->homestay_name }}{{ $homestay->city ? ' - ' . $homestay->city : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('homestay_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="photos" class="mb-2 block text-sm font-semibold text-gray-700">360 room panorama</label>
                    <input id="photos" name="photos[]" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                        class="w-full rounded-2xl border border-[#ddd4c2] px-4 py-3 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-green-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-green-700">
                    <p class="mt-2 text-xs text-gray-500">Upload exactly one 360 panorama image. Recommended: stitched equirectangular photo, about 2:1 ratio.</p>
                    @error('photos')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('photos.*')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('photos.0')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="overview_video" class="mb-2 block text-sm font-semibold text-gray-700">Room overview video</label>
                    <input id="overview_video" name="overview_video" type="file" accept="video/mp4,video/quicktime,video/webm,.mp4,.mov,.webm,.m4v"
                        class="w-full rounded-2xl border border-[#ddd4c2] px-4 py-3 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-[#173423] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#23543a]">
                    <p class="mt-2 text-xs text-gray-500">Optional. Upload one slow room-overview clip as a quick guide alongside the panorama.</p>
                    @error('overview_video')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-[24px] bg-[#fbf7ee] p-5 text-sm text-gray-600">
                    <p class="font-semibold text-gray-900">Panorama checklist</p>
                    <div class="mt-3 space-y-2">
                        <p>1. Export one stitched 360 image for one room only.</p>
                        <p>2. Keep the camera near the middle of the room so the rotation feels natural.</p>
                        <p>3. Avoid blur, heavy shadows, and people moving through the scene.</p>
                        <p>4. If you also upload a video, make it a slow, steady walk-through of the same room.</p>
                    </div>
                </div>

                <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700">
                    Upload 360 room photo
                </button>
            </form>
        </section>

        <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
            <h2 class="text-xl font-semibold text-gray-900">Recent scans</h2>
            <p class="mt-2 text-sm text-gray-500">Track upload status and reopen each room viewer.</p>

            <div class="mt-6 space-y-4">
                @forelse ($scans as $scan)
                    @php
                        $statusClasses = match ($scan->status) {
                            'completed' => 'bg-[#eef5eb] text-green-700',
                            'processing' => 'bg-[#fff4df] text-amber-700',
                            'failed' => 'bg-red-50 text-red-700',
                            default => 'bg-[#f5efe3] text-gray-600',
                        };

                    @endphp
                    <a href="{{ route('owner.room-scans.show', $scan->id) }}"
                        class="block rounded-[24px] border border-[#e9e2d5] bg-[#fcfaf4] p-5 transition hover:border-green-300 hover:shadow-sm">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <p class="text-lg font-semibold text-gray-900">{{ $scan->homestay_name }}</p>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $statusClasses }}">
                                        {{ $scan->status }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">{{ $scan->homestay_location }}</p>
                                <p class="mt-3 text-sm text-gray-600">
                                    360 panorama &middot; {{ $scan->photo_count }} file(s) &middot; Folder {{ $scan->folder }}
                                </p>
                                @if (!empty($scan->metadata['overview_video_path']))
                                    <p class="mt-2 text-sm text-[#173423]">Overview video attached</p>
                                @endif
                                @if ($scan->error_message)
                                    <p class="mt-3 text-sm text-red-600">{{ $scan->error_message }}</p>
                                @endif
                            </div>
                            <div class="text-left lg:text-right">
                                <p class="text-sm text-gray-500">{{ optional($scan->created_at)->diffForHumans() }}</p>
                                <p class="mt-3 text-sm font-semibold text-green-700">Open scan &rsaquo;</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rounded-[24px] border border-dashed border-[#d8dece] bg-[#fbfcf8] px-6 py-10 text-center">
                        <p class="text-lg font-semibold text-gray-900">No room scans yet</p>
                        <p class="mt-2 text-sm text-gray-500">Your first 360 room panorama will appear here once you upload it.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</main>
@endsection
