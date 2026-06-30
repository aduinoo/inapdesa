<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id');
                $table->unsignedBigInteger('homestay_id');
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('owner_id');
                $table->text('description');
                $table->string('status', 20)->default('pending');
                $table->timestamp('settled_at')->nullable();
                $table->timestamps();

                $table->index(['owner_id', 'status']);
                $table->index(['homestay_id', 'status']);
                $table->index(['customer_id', 'status']);
            });
        } else {
            Schema::table('reports', function (Blueprint $table) {
                if (! Schema::hasColumn('reports', 'booking_id')) {
                    $table->unsignedBigInteger('booking_id')->nullable();
                }
                if (! Schema::hasColumn('reports', 'customer_id')) {
                    $table->unsignedBigInteger('customer_id')->nullable();
                }
                if (! Schema::hasColumn('reports', 'owner_id')) {
                    $table->unsignedBigInteger('owner_id')->nullable();
                }
                if (! Schema::hasColumn('reports', 'settled_at')) {
                    $table->timestamp('settled_at')->nullable();
                }
                if (! Schema::hasColumn('reports', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (! Schema::hasColumn('reports', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });

            if (Schema::hasColumn('reports', 'user_id') && Schema::hasColumn('reports', 'customer_id')) {
                DB::statement('UPDATE reports SET customer_id = user_id WHERE customer_id IS NULL');
            }

            if (Schema::hasColumn('reports', 'owner_id')) {
                DB::statement('
                    UPDATE reports
                    INNER JOIN homestays ON reports.homestay_id = homestays.homestay_id
                    SET reports.owner_id = homestays.owner_id
                    WHERE reports.owner_id IS NULL
                ');
            }
        }

        if (! Schema::hasTable('report_messages')) {
            Schema::create('report_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('report_id');
                $table->unsignedBigInteger('sender_id');
                $table->string('recipient_scope', 30)->default('host');
                $table->text('message');
                $table->timestamp('created_at')->nullable();

                $table->index(['report_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('report_messages');
        Schema::dropIfExists('reports');
    }
};
