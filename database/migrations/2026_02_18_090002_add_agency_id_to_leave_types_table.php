<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('leave_types') && !Schema::hasColumn('leave_types', 'agency_id')) {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->unsignedBigInteger('agency_id')->nullable()->after('company_id')->index();
            });

            // Backfill existing leave types with first active agency, if any exist
            $defaultAgencyId = Schema::hasTable('agencies')
                ? DB::table('agencies')->where('status', 'Active')->value('id')
                : null;

            if ($defaultAgencyId) {
                DB::table('leave_types')->whereNull('agency_id')->update([
                    'agency_id' => $defaultAgencyId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('leave_types') && Schema::hasColumn('leave_types', 'agency_id')) {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->dropColumn('agency_id');
            });
        }
    }
};
