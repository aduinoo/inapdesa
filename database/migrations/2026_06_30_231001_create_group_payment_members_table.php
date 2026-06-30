<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_payment_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_payment_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email');
            $table->string('name')->nullable();
            $table->decimal('share_amount', 12, 2);
            $table->string('payment_status', 20)->default('pending');
            $table->string('bill_code', 64)->nullable();
            $table->string('order_id', 64)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_initiator')->default(false);
            $table->timestamps();

            $table->index('group_payment_id');
            $table->index(['user_id', 'payment_status']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_payment_members');
    }
};
