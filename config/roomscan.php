<?php

return [
    'min_photos' => (int) env('ROOM_SCAN_MIN_PHOTOS', 30),
    'max_photos' => (int) env('ROOM_SCAN_MAX_PHOTOS', 120),
    'queue' => env('ROOM_SCAN_QUEUE', 'default'),
    'public_disk' => env('ROOM_SCAN_PUBLIC_DISK', 'public'),
    'viewer_points_limit' => (int) env('ROOM_SCAN_VIEWER_POINTS_LIMIT', 12000),
    'acceptable_points' => (int) env('ROOM_SCAN_ACCEPTABLE_POINTS', 300),
    'command_timeout' => (int) env('ROOM_SCAN_COMMAND_TIMEOUT', 3600),
    'colmap_binary' => env('COLMAP_BIN'),
    'ffmpeg_binary' => env('FFMPEG_BIN'),
    'use_gpu' => (bool) env('ROOM_SCAN_USE_GPU', false),
];
