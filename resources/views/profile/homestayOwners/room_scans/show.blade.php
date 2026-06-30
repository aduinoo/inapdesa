@extends('profile.homestayOwners.app_ownerHomestay')

@section('content')
<main class="mx-auto max-w-7xl px-6 py-10">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-green-700/70">3D Room Scan</p>
            <h1 class="mt-3 text-3xl font-semibold text-gray-900">{{ $homestay->homestay_name ?? ('Homestay #' . $scan->homestay_id) }}</h1>
            <p class="mt-2 text-sm leading-6 text-gray-600">
                {{ trim(collect([$homestay->city ?? null, $homestay->state ?? null])->filter()->implode(', ')) ?: 'Malaysia' }}
            </p>
        </div>
        <a href="{{ route('owner.room-scans.index') }}"
            class="inline-flex items-center justify-center rounded-full border border-[#d9d0be] bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-green-700 hover:text-green-700">
            Back to scans
        </a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <aside class="space-y-6">
            <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Scan status</p>
                <div class="mt-5 space-y-4 text-sm text-gray-600">
                    <p><span class="font-semibold text-gray-900">Folder:</span> {{ $scan->folder }}</p>
                    <p><span class="font-semibold text-gray-900">Status:</span> {{ ucfirst($scan->status) }}</p>
                    <p><span class="font-semibold text-gray-900">Scan type:</span> 360 panorama</p>
                    <p><span class="font-semibold text-gray-900">Files:</span> {{ $scan->photo_count }}</p>
                    @if ($panoramaPhotoName)
                        <p><span class="font-semibold text-gray-900">Panorama:</span> {{ $panoramaPhotoName }}</p>
                    @endif
                    <p><span class="font-semibold text-gray-900">Overview video:</span> {{ $overviewVideoUrl ? ($overviewVideoName ?: 'Uploaded') : 'Not uploaded' }}</p>
                    <p><span class="font-semibold text-gray-900">Started:</span> {{ $scan->started_at?->format('d M Y, h:i A') ?? 'Not started yet' }}</p>
                    <p><span class="font-semibold text-gray-900">Finished:</span> {{ $scan->completed_at?->format('d M Y, h:i A') ?? 'Still running' }}</p>
                </div>
            </section>

            <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Viewer Notes</p>
                <div class="mt-4 space-y-3 text-sm leading-6 text-gray-600">
                    <p>This scan uses one stitched 360 panorama, so the room can open immediately without extra reconstruction steps.</p>
                    <p>Drag left, right, up, or down to turn inside the room from the camera position where the panorama was captured.</p>
                    @if ($overviewVideoUrl)
                        <p>The optional overview video stays available below the viewer as a quick orientation guide.</p>
                    @endif
                </div>
            </section>

            @if ($scan->error_message)
                <section class="rounded-[30px] border border-red-200 bg-red-50 p-6 text-sm text-red-700 shadow-[0_18px_50px_rgba(185,28,28,0.08)]">
                    <p class="font-semibold">Processing failed</p>
                    <p class="mt-3">{{ $scan->error_message }}</p>
                </section>
            @endif
        </aside>

        <section class="rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
            <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">360 Panorama Viewer</h2>
                    <p class="mt-2 text-sm text-gray-500">Turn inside the room directly from the uploaded 360 image.</p>
                </div>
                @if ($panoramaPhotoUrl)
                    <span class="inline-flex rounded-full bg-[#eef5eb] px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-green-700">
                        Viewer ready
                    </span>
                @endif
            </div>

            @if ($panoramaPhotoUrl)
                <div class="space-y-5">
                    <div class="rounded-[26px] bg-[#f7f4ec] p-4 sm:p-5">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-green-700/70">360 Orientation</p>
                                <p id="panorama-step" class="mt-1 text-sm font-semibold text-gray-900">Loading panorama...</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" id="panorama-turn-left"
                                    class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-[#ddcfb6] bg-white text-lg text-gray-700 transition hover:border-green-600 hover:text-green-700">
                                    &#8249;
                                </button>
                                <button type="button" id="panorama-reset"
                                    class="inline-flex rounded-full border border-[#ddcfb6] bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition hover:border-green-600 hover:text-green-700">
                                    Reset view
                                </button>
                                <button type="button" id="panorama-turn-right"
                                    class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-[#ddcfb6] bg-white text-lg text-gray-700 transition hover:border-green-600 hover:text-green-700">
                                    &#8250;
                                </button>
                            </div>
                        </div>

                        <div id="panorama-viewer"
                            class="relative h-[560px] overflow-hidden rounded-[24px] bg-[radial-gradient(circle_at_top,#ffffff_0%,#f7f4ec_58%,#e6d7b7_100%)] shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5"
                            style="touch-action: none;">
                            <canvas id="panorama-canvas" class="absolute inset-0 h-full w-full"></canvas>

                            <div class="pointer-events-none absolute inset-0 z-10 bg-[radial-gradient(circle_at_center,transparent_0%,transparent_56%,rgba(15,23,42,0.16)_100%)]"></div>

                            <div class="pointer-events-none absolute inset-x-6 top-6 z-10 flex justify-center">
                                <div class="rounded-full bg-white/92 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-700 shadow-sm ring-1 ring-black/5">
                                    Drag To Turn Inside The Room
                                </div>
                            </div>

                            <div id="panorama-loading"
                                class="absolute inset-0 z-20 flex items-center justify-center bg-[#f7f4ec]/85 text-center text-sm font-semibold text-gray-500 backdrop-blur-sm">
                                Building panorama viewer...
                            </div>

                            <div class="pointer-events-none absolute inset-x-0 bottom-0 z-10 bg-gradient-to-t from-black/70 via-black/18 to-transparent p-5 text-white">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/75">Current View</p>
                                <p id="panorama-name" class="mt-2 truncate text-lg font-semibold">{{ $panoramaPhotoName ?? '360 panorama' }}</p>
                                <p class="mt-1 text-sm text-white/85">Use drag, arrow buttons, or keyboard arrows to turn around the room.</p>
                            </div>
                        </div>
                    </div>

                    @if ($overviewVideoUrl)
                        <div class="rounded-[26px] bg-[#f7f4ec] p-4 sm:p-5">
                            <div class="mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Room overview guide</h3>
                                <p class="mt-1 text-sm text-gray-500">Use the walkthrough clip as an extra reference while checking the panorama.</p>
                            </div>

                            <video class="w-full rounded-[20px] bg-black shadow-sm" controls preload="metadata">
                                <source src="{{ $overviewVideoUrl }}">
                            </video>
                        </div>
                    @endif
                </div>
            @elseif (in_array($scan->status, ['pending', 'processing'], true))
                <div class="flex h-[560px] items-center justify-center rounded-[26px] bg-[#f7f4ec] text-center">
                    <div>
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-2xl text-green-700 shadow-sm">
                            &#9203;
                        </div>
                        <p class="mt-5 text-lg font-semibold text-gray-900">Preparing panorama viewer...</p>
                        <p class="mt-2 text-sm text-gray-500">This should finish quickly once the upload is saved.</p>
                    </div>
                </div>
            @else
                <div class="flex h-[560px] items-center justify-center rounded-[26px] bg-[#f7f4ec] text-center">
                    <div>
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-2xl text-red-600 shadow-sm">
                            &#9888;
                        </div>
                        <p class="mt-5 text-lg font-semibold text-gray-900">No room view yet</p>
                        <p class="mt-2 text-sm text-gray-500">Check the upload and verify that the panorama image was saved correctly.</p>
                    </div>
                </div>
            @endif
        </section>
    </div>

    @if ($scan->processing_log)
        <section class="mt-6 rounded-[30px] bg-white p-6 shadow-[0_18px_50px_rgba(47,62,42,0.08)] ring-1 ring-black/5">
            <h2 class="text-xl font-semibold text-gray-900">Processing log</h2>
            <pre class="mt-4 max-h-[420px] overflow-auto rounded-[24px] bg-[#111827] p-5 text-xs leading-6 text-green-100">{{ $scan->processing_log }}</pre>
        </section>
    @endif
</main>
@endsection

@if ($panoramaPhotoUrl)
    @push('scripts')
        <script>
            (() => {
                const viewer = document.getElementById('panorama-viewer');
                const canvas = document.getElementById('panorama-canvas');
                const loading = document.getElementById('panorama-loading');
                const stepText = document.getElementById('panorama-step');
                const nameText = document.getElementById('panorama-name');
                const leftButton = document.getElementById('panorama-turn-left');
                const rightButton = document.getElementById('panorama-turn-right');
                const resetButton = document.getElementById('panorama-reset');
                const panoramaUrl = @json($panoramaPhotoUrl);
                const panoramaName = @json($panoramaPhotoName ?? '360 panorama');

                if (!viewer || !canvas || !panoramaUrl) {
                    return;
                }

                const context = canvas.getContext('2d', { alpha: false });
                const sourceCanvas = document.createElement('canvas');
                const sourceContext = sourceCanvas.getContext('2d', { willReadFrequently: true });
                const state = {
                    yaw: 0,
                    pitch: 0,
                    dragging: false,
                    startX: 0,
                    startY: 0,
                    startYaw: 0,
                    startPitch: 0,
                    renderFrame: null,
                    nextRenderProfile: 'detail',
                    settledRenderTimer: null,
                    sourceWidth: 0,
                    sourceHeight: 0,
                    sourcePixels: null,
                };

                const renderProfiles = {
                    interactive: {
                        maxPixels: 680000,
                        maxWidth: 1280,
                        minWidth: 420,
                        dprCap: 1.2,
                        useBilinear: false,
                    },
                    detail: {
                        maxPixels: 2200000,
                        maxWidth: 2200,
                        minWidth: 640,
                        dprCap: 2,
                        useBilinear: true,
                    },
                };
                const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
                const normalizeDegrees = (value) => ((value % 360) + 360) % 360;
                const formatPitch = (value) => `${Math.round(value)}${String.fromCharCode(176)}`;
                const formatYaw = (value) => `${Math.round(normalizeDegrees(value))}${String.fromCharCode(176)}`;

                const updateLabels = () => {
                    if (stepText) {
                        stepText.textContent = `Yaw ${formatYaw(state.yaw)} / Pitch ${formatPitch(state.pitch)}`;
                    }

                    if (nameText) {
                        nameText.textContent = panoramaName;
                    }
                };

                const render = () => {
                    state.renderFrame = null;

                    if (!context || !state.sourcePixels || !state.sourceWidth || !state.sourceHeight) {
                        return;
                    }

                    const profile = renderProfiles[state.nextRenderProfile] ?? renderProfiles.detail;
                    const viewerWidth = Math.max(1, viewer.clientWidth);
                    const viewerHeight = Math.max(1, viewer.clientHeight);
                    const aspect = viewerWidth / viewerHeight;
                    const deviceScale = Math.min(window.devicePixelRatio || 1, profile.dprCap);
                    const preferredWidth = Math.round(viewerWidth * deviceScale);
                    const widthByPixels = Math.round(Math.sqrt(profile.maxPixels * aspect));
                    const renderWidth = Math.max(
                        profile.minWidth,
                        Math.min(profile.maxWidth, widthByPixels, preferredWidth)
                    );
                    const renderHeight = Math.max(180, Math.round(renderWidth / aspect));

                    if (canvas.width !== renderWidth || canvas.height !== renderHeight) {
                        canvas.width = renderWidth;
                        canvas.height = renderHeight;
                    }

                    const frame = context.createImageData(renderWidth, renderHeight);
                    const output = frame.data;
                    const source = state.sourcePixels;
                    const sourceWidth = state.sourceWidth;
                    const sourceHeight = state.sourceHeight;
                    const horizontalFov = (95 * Math.PI) / 180;
                    const verticalFov = 2 * Math.atan(Math.tan(horizontalFov / 2) / aspect);
                    const tanHalfHorizontal = Math.tan(horizontalFov / 2);
                    const tanHalfVertical = Math.tan(verticalFov / 2);
                    const yaw = (state.yaw * Math.PI) / 180;
                    const pitch = (state.pitch * Math.PI) / 180;
                    const cosYaw = Math.cos(yaw);
                    const sinYaw = Math.sin(yaw);
                    const cosPitch = Math.cos(pitch);
                    const sinPitch = Math.sin(pitch);
                    const twoPi = Math.PI * 2;
                    const srcYMax = sourceHeight - 1;

                    for (let y = 0; y < renderHeight; y++) {
                        const normalizedY = 1 - (((y + 0.5) / renderHeight) * 2);
                        const cameraY = normalizedY * tanHalfVertical;

                        for (let x = 0; x < renderWidth; x++) {
                            const normalizedX = (((x + 0.5) / renderWidth) * 2) - 1;
                            const cameraX = normalizedX * tanHalfHorizontal;
                            const cameraZ = 1;
                            const length = Math.hypot(cameraX, cameraY, cameraZ) || 1;
                            const directionX = cameraX / length;
                            const directionY = cameraY / length;
                            const directionZ = cameraZ / length;

                            const yawX = (directionX * cosYaw) + (directionZ * sinYaw);
                            const yawZ = (-directionX * sinYaw) + (directionZ * cosYaw);
                            const pitchedY = (directionY * cosPitch) - (yawZ * sinPitch);
                            const pitchedZ = (directionY * sinPitch) + (yawZ * cosPitch);
                            const longitude = Math.atan2(yawX, pitchedZ);
                            const latitude = Math.asin(clamp(pitchedY, -1, 1));
                            const sampleX = ((longitude / twoPi) + 0.5) * sourceWidth;
                            const sampleY = (0.5 - (latitude / Math.PI)) * sourceHeight;
                            const targetIndex = ((y * renderWidth) + x) * 4;
                            const sampleFloorY = clamp(Math.floor(sampleY), 0, srcYMax);

                            if (!profile.useBilinear) {
                                const sampleFloorX = Math.floor(sampleX);
                                const sourceX = ((sampleFloorX % sourceWidth) + sourceWidth) % sourceWidth;
                                const sourceIndex = ((sampleFloorY * sourceWidth) + sourceX) * 4;

                                output[targetIndex] = source[sourceIndex];
                                output[targetIndex + 1] = source[sourceIndex + 1];
                                output[targetIndex + 2] = source[sourceIndex + 2];
                                output[targetIndex + 3] = 255;
                                continue;
                            }

                            const sampleFloorX = Math.floor(sampleX);
                            const nextSampleY = Math.min(srcYMax, sampleFloorY + 1);
                            const sampleWeightX = sampleX - sampleFloorX;
                            const sampleWeightY = clamp(sampleY - sampleFloorY, 0, 1);
                            const x0 = ((sampleFloorX % sourceWidth) + sourceWidth) % sourceWidth;
                            const x1 = (x0 + 1) % sourceWidth;
                            const topLeftIndex = ((sampleFloorY * sourceWidth) + x0) * 4;
                            const topRightIndex = ((sampleFloorY * sourceWidth) + x1) * 4;
                            const bottomLeftIndex = ((nextSampleY * sourceWidth) + x0) * 4;
                            const bottomRightIndex = ((nextSampleY * sourceWidth) + x1) * 4;

                            for (let channel = 0; channel < 3; channel++) {
                                const topBlend = source[topLeftIndex + channel]
                                    + ((source[topRightIndex + channel] - source[topLeftIndex + channel]) * sampleWeightX);
                                const bottomBlend = source[bottomLeftIndex + channel]
                                    + ((source[bottomRightIndex + channel] - source[bottomLeftIndex + channel]) * sampleWeightX);

                                output[targetIndex + channel] = Math.round(
                                    topBlend + ((bottomBlend - topBlend) * sampleWeightY)
                                );
                            }

                            output[targetIndex + 3] = 255;
                        }
                    }

                    context.putImageData(frame, 0, 0);
                    updateLabels();
                };

                const scheduleRender = (profile = 'detail') => {
                    state.nextRenderProfile = profile;

                    if (state.renderFrame !== null) {
                        cancelAnimationFrame(state.renderFrame);
                    }

                    state.renderFrame = requestAnimationFrame(render);
                };

                const scheduleSettledRender = (delay = 120) => {
                    if (state.settledRenderTimer !== null) {
                        window.clearTimeout(state.settledRenderTimer);
                    }

                    state.settledRenderTimer = window.setTimeout(() => {
                        scheduleRender('detail');
                        state.settledRenderTimer = null;
                    }, delay);
                };

                const scheduleInteractionRender = () => {
                    scheduleRender('interactive');
                    scheduleSettledRender();
                };

                const image = new Image();
                image.decoding = 'async';
                image.src = panoramaUrl;

                image.onload = () => {
                    sourceCanvas.width = image.naturalWidth;
                    sourceCanvas.height = image.naturalHeight;
                    sourceContext.drawImage(image, 0, 0);

                    state.sourceWidth = sourceCanvas.width;
                    state.sourceHeight = sourceCanvas.height;
                    state.sourcePixels = sourceContext.getImageData(0, 0, sourceCanvas.width, sourceCanvas.height).data;

                    loading?.remove();
                    scheduleRender('detail');
                };

                image.onerror = () => {
                    if (loading) {
                        loading.textContent = 'Could not open this panorama image.';
                    }
                };

                const rotateBy = (amount) => {
                    state.yaw += amount;
                    scheduleInteractionRender();
                };

                leftButton?.addEventListener('click', () => rotateBy(-25));
                rightButton?.addEventListener('click', () => rotateBy(25));
                resetButton?.addEventListener('click', () => {
                    state.yaw = 0;
                    state.pitch = 0;
                    scheduleRender('detail');
                });

                viewer.addEventListener('pointerdown', (event) => {
                    if (state.settledRenderTimer !== null) {
                        window.clearTimeout(state.settledRenderTimer);
                        state.settledRenderTimer = null;
                    }

                    state.dragging = true;
                    state.startX = event.clientX;
                    state.startY = event.clientY;
                    state.startYaw = state.yaw;
                    state.startPitch = state.pitch;
                    viewer.setPointerCapture(event.pointerId);
                });

                viewer.addEventListener('pointermove', (event) => {
                    if (!state.dragging) {
                        return;
                    }

                    const deltaX = event.clientX - state.startX;
                    const deltaY = event.clientY - state.startY;
                    state.yaw = state.startYaw - (deltaX * 0.14);
                    state.pitch = clamp(state.startPitch + (deltaY * 0.1), -55, 55);
                    scheduleInteractionRender();
                });

                const stopDragging = (event) => {
                    if (!state.dragging) {
                        return;
                    }

                    state.dragging = false;

                    if (event.pointerId !== undefined) {
                        viewer.releasePointerCapture(event.pointerId);
                    }

                    scheduleSettledRender(60);
                };

                viewer.addEventListener('pointerup', stopDragging);
                viewer.addEventListener('pointercancel', stopDragging);
                viewer.addEventListener('pointerleave', stopDragging);

                viewer.addEventListener('wheel', (event) => {
                    event.preventDefault();

                    const primaryYawDelta = Math.abs(event.deltaX) > 0.5 ? event.deltaX : event.deltaY;
                    const pitchDelta = Math.abs(event.deltaX) > 0.5 ? event.deltaY : 0;

                    state.yaw -= primaryYawDelta * 0.11;
                    state.pitch = clamp(state.pitch + (pitchDelta * 0.045), -55, 55);
                    scheduleInteractionRender();
                }, { passive: false });

                window.addEventListener('resize', () => scheduleRender('detail'));
                window.addEventListener('keydown', (event) => {
                    if (event.key === 'ArrowLeft') {
                        rotateBy(-18);
                    }

                    if (event.key === 'ArrowRight') {
                        rotateBy(18);
                    }

                    if (event.key === 'ArrowUp') {
                        state.pitch = clamp(state.pitch - 8, -55, 55);
                        scheduleInteractionRender();
                    }

                    if (event.key === 'ArrowDown') {
                        state.pitch = clamp(state.pitch + 8, -55, 55);
                        scheduleInteractionRender();
                    }
                });
            })();
        </script>
    @endpush
@endif
