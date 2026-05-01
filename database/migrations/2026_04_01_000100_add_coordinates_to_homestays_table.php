<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homestays', function (Blueprint $table) {
            if (! Schema::hasColumn('homestays', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('state');
            }

            if (! Schema::hasColumn('homestays', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('homestays', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('homestays', 'latitude') ? 'latitude' : null,
                Schema::hasColumn('homestays', 'longitude') ? 'longitude' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
