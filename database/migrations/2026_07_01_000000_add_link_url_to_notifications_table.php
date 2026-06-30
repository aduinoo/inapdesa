<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifications') || Schema::hasColumn('notifications', 'link_url')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->string('link_url')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('notifications') || !Schema::hasColumn('notifications', 'link_url')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('link_url');
        });
    }
};
