<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_cancellation_requests')) {
            return;
        }

        Schema::create('booking_cancellation_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('homestay_id');
            $table->text('reason');
            $table->integer('days_before_check_in')->default(0);
            $table->decimal('refund_rate', 5, 2)->default(0);
            $table->decimal('requested_refund_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('host_decision_notes')->nullable();
            $table->timestamp('processed_by_host_at')->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'status']);
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_cancellation_requests');
    }
};
