<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('shifts', 'week_off_days')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->json('week_off_days')->nullable()->after('total_working_hours');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('shifts', 'week_off_days')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->dropColumn('week_off_days');
            });
        }
    }
};
