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
        Schema::table('agencies', function (Blueprint $table) {
            if (!Schema::hasColumn('agencies', 'landline_no')) {
                $table->string('landline_no', 20)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('agencies', 'gst_no')) {
                $table->string('gst_no', 20)->nullable()->after('email');
            }
            if (!Schema::hasColumn('agencies', 'office_pincode')) {
                $table->string('office_pincode', 10)->nullable()->after('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            foreach (['landline_no', 'gst_no', 'office_pincode'] as $col) {
                if (Schema::hasColumn('agencies', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
