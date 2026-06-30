<?php

namespace App\Http\Controllers;

use App\Models\RoomScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoomScanController extends Controller
{
    public function index()
    {
        $ownerId = Auth::id();

        $homestays = DB::table('homestays')
            ->where('owner_id', $ownerId)
            ->where('status', 'active')
            ->orderBy('homestay_name')
            ->get(['homestay_id', 'homestay_name', 'city', 'state']);

        $scans = RoomScan::query()
            ->where('user_id', $ownerId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (RoomScan $scan) use ($homestays) {
                $homestay = $homestays->firstWhere('homestay_id', $scan->homestay_id);
                $scan->homestay_name = $homestay->homestay_name ?? ('Homestay #' . $scan->homestay_id);
                $scan->homestay_location = trim(collect([$homestay->city ?? null, $homestay->state ?? null])->filter()->implode(', ')) ?: 'Malaysia';

                return $scan;
            });

        return view('profile.homestayOwners.room_scans.index', [
            'homestays' => $homestays,
            'scans' => $scans,
        ]);
    }

    public function store(Request $request)
    {
        $ownerId = Auth::id();
        $homestayIds = DB::table('homestays')
            ->where('owner_id', $ownerId)
            ->pluck('homestay_id')
            ->all();

        $validated = $request->validate([
            'homestay_id' => ['required', 'integer', 'in:' . implode(',', $homestayIds ?: [0])],
            'photos' => ['required', 'array', 'size:1'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'overview_video' => ['nullable', 'file', 'mimes:mp4,mov,webm,m4v', 'max:256000'],
        ], [
            'photos.size' => 'Please upload exactly one 360 panorama image for each room.',
            'overview_video.max' => 'Please keep the room overview video under 250 MB.',
        ]);

        $panoramaPhoto = $request->file('photos.0');

        if (! $panoramaPhoto) {
            throw ValidationException::withMessages([
                'photos' => 'Please upload exactly one 360 panorama image for each room.',
            ]);
        }

        $this->ensurePanoramaAspectRatio($panoramaPhoto->getRealPath());

        $folder = sprintf('scan_%s_%s', now()->format('Ymd_His'), str()->random(6));

        foreach ($request->file('photos', []) as $index => $photo) {
            $originalName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = strtolower($photo->getClientOriginalExtension() ?: $photo->extension() ?: 'jpg');
            $safeName = Str::slug($originalName, '_') ?: 'room_photo';
            $filename = sprintf('%03d_%s.%s', $index + 1, $safeName, $extension);

            $photo->storeAs("scans/{$folder}/images", $filename, 'local');
        }

        $overviewVideoPath = null;
        $overviewVideoOriginalName = null;

        if ($request->hasFile('overview_video')) {
            $overviewVideo = $request->file('overview_video');
            $overviewVideoOriginalName = $overviewVideo->getClientOriginalName();
            $overviewVideoExtension = strtolower($overviewVideo->getClientOriginalExtension() ?: $overviewVideo->extension() ?: 'mp4');
            $overviewVideoFilename = "room_overview.{$overviewVideoExtension}";
            $overviewVideoPath = "scans/{$folder}/overview/{$overviewVideoFilename}";

            $overviewVideo->storeAs("scans/{$folder}/overview", $overviewVideoFilename, 'local');
        }

        $scan = RoomScan::create([
            'user_id' => $ownerId,
            'homestay_id' => (int) $validated['homestay_id'],
            'folder' => $folder,
            'status' => 'completed',
            'photo_count' => count($validated['photos']),
            'metadata' => [
                'scan_type' => 'panorama_360',
                'original_filenames' => collect($request->file('photos', []))
                    ->map(fn ($file) => $file->getClientOriginalName())
                    ->values()
                    ->all(),
                'panorama_photo_original_name' => $panoramaPhoto->getClientOriginalName(),
                'overview_video_path' => $overviewVideoPath,
                'overview_video_original_name' => $overviewVideoOriginalName,
            ],
            'processing_log' => 'Single 360 panorama uploaded successfully. The room viewer is ready to use.',
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('owner.room-scans.show', $scan)
            ->with('success', '360 panorama uploaded. The room viewer is ready.');
    }

    public function show(RoomScan $roomScan)
    {
        abort_unless((int) $roomScan->user_id === (int) Auth::id(), 403);

        $homestay = DB::table('homestays')
            ->where('homestay_id', $roomScan->homestay_id)
            ->where('owner_id', Auth::id())
            ->first(['homestay_name', 'city', 'state']);

        $panoramaPath = $this->orderedPhotoPaths($roomScan)->first();
        $overviewVideoUrl = $this->overviewVideoUrl($roomScan);
        $overviewVideoName = $roomScan->metadata['overview_video_original_name'] ?? null;
        $panoramaPhotoUrl = $panoramaPath
            ? route('owner.room-scans.photo', ['roomScan' => $roomScan->id, 'photoIndex' => 0])
            : null;
        $panoramaPhotoName = $roomScan->metadata['panorama_photo_original_name']
            ?? ($panoramaPath ? basename($panoramaPath) : null);

        return view('profile.homestayOwners.room_scans.show', [
            'scan' => $roomScan,
            'homestay' => $homestay,
            'overviewVideoUrl' => $overviewVideoUrl,
            'overviewVideoName' => $overviewVideoName,
            'panoramaPhotoUrl' => $panoramaPhotoUrl,
            'panoramaPhotoName' => $panoramaPhotoName,
        ]);
    }

    public function photo(RoomScan $roomScan, int $photoIndex)
    {
        abort_unless((int) $roomScan->user_id === (int) Auth::id(), 403);

        $files = $this->orderedPhotoPaths($roomScan)->values();

        abort_unless(isset($files[$photoIndex]), 404);

        return response()->file(Storage::disk('local')->path($files[$photoIndex]));
    }

    public function video(RoomScan $roomScan)
    {
        abort_unless((int) $roomScan->user_id === (int) Auth::id(), 403);

        $videoPath = $this->overviewVideoPath($roomScan);

        abort_unless($videoPath && Storage::disk('local')->exists($videoPath), 404);

        return response()->file(Storage::disk('local')->path($videoPath));
    }

    private function orderedPhotoPaths(RoomScan $roomScan)
    {
        $disk = Storage::disk('local');

        return collect($disk->files("scans/{$roomScan->folder}/images"))
            ->filter(fn (string $path) => File::extension($path) !== '')
            ->map(function (string $path) use ($disk) {
                preg_match('/^(\d+)_/', basename($path), $matches);

                return [
                    'path' => $path,
                    'sequence' => isset($matches[1]) ? (int) $matches[1] : PHP_INT_MAX,
                    'modified' => $disk->lastModified($path),
                    'name' => basename($path),
                ];
            })
            ->sort(function (array $first, array $second) {
                return [$first['sequence'], $first['modified'], $first['name']]
                    <=> [$second['sequence'], $second['modified'], $second['name']];
            })
            ->pluck('path');
    }

    private function overviewVideoPath(RoomScan $roomScan): ?string
    {
        $path = $roomScan->metadata['overview_video_path'] ?? null;

        return is_string($path) && $path !== '' ? $path : null;
    }

    private function overviewVideoUrl(RoomScan $roomScan): ?string
    {
        $path = $this->overviewVideoPath($roomScan);

        if (! $path || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        return route('owner.room-scans.video', $roomScan);
    }

    private function ensurePanoramaAspectRatio(?string $path): void
    {
        if (! $path || ! is_file($path)) {
            throw ValidationException::withMessages([
                'photos.0' => 'The uploaded panorama image could not be read.',
            ]);
        }

        $imageSize = @getimagesize($path);
        $width = $imageSize[0] ?? null;
        $height = $imageSize[1] ?? null;

        if (! $width || ! $height) {
            throw ValidationException::withMessages([
                'photos.0' => 'The uploaded panorama image could not be read.',
            ]);
        }

        $ratio = $width / $height;

        if ($ratio < 1.7 || $ratio > 2.3) {
            throw ValidationException::withMessages([
                'photos.0' => 'Please upload an equirectangular 360 photo with an approximately 2:1 aspect ratio, such as 6000x3000.',
            ]);
        }
    }
}
