<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'homestay_id',
        'folder',
        'status',
        'photo_count',
        'metadata',
        'sparse_model_path',
        'point_cloud_path',
        'error_message',
        'processing_log',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
