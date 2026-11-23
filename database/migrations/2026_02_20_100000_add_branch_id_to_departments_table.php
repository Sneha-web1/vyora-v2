<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
        });
    }
};
