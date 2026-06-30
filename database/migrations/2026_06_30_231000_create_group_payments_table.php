<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_payments', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('initiator_user_id');
            $table->unsignedBigInteger('homestay_id');
            $table->string('homestay_name');
            $table->string('location');
            $table->string('image')->nullable();
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('guest_count');
            $table->decimal('total_price', 12, 2);
            $table->decimal('share_amount', 12, 2);
            $table->unsignedInteger('member_count');
            $table->string('status', 20)->default('pending');
            $table->timestamp('deadline_at');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->timestamps();

            $table->index(['initiator_user_id', 'status']);
            $table->index(['status', 'deadline_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_payments');
    }
};
