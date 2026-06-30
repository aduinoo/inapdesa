<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('homestay_id');
            $table->string('folder')->unique();
            $table->string('status')->default('pending');
            $table->unsignedInteger('photo_count')->default(0);
            $table->json('metadata')->nullable();
            $table->string('sparse_model_path')->nullable();
            $table->string('point_cloud_path')->nullable();
            $table->text('error_message')->nullable();
            $table->longText('processing_log')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['homestay_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_scans');
    }
};
