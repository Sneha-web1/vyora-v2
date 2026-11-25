<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'tan_no')) {
                $table->string('tan_no', 50)->nullable()->after('pan_no');
            }
            if (!Schema::hasColumn('companies', 'duns_no')) {
                $table->string('duns_no', 50)->nullable()->after('iec');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'tan_no')) {
                $table->dropColumn('tan_no');
            }
            if (Schema::hasColumn('companies', 'duns_no')) {
                $table->dropColumn('duns_no');
            }
        });
    }
};
