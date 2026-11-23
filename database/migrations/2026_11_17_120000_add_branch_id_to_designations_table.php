<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            if (!Schema::hasColumn('designations', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            }

            if (!Schema::hasColumn('designations', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('branch_id');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            }

            if (!Schema::hasColumn('designations', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('category_id');
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            }

            if (!Schema::hasColumn('designations', 'kpi_weightage')) {
                $table->decimal('kpi_weightage', 5, 2)->default(0)->after('code');
            }

            if (!Schema::hasColumn('designations', 'competency_weight')) {
                $table->decimal('competency_weight', 5, 2)->default(0)->after('kpi_weightage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            foreach (['branch_id', 'category_id', 'department_id'] as $col) {
                if (Schema::hasColumn('designations', $col)) {
                    $table->dropForeign([$col]);
                }
            }

            foreach (['branch_id', 'category_id', 'department_id', 'kpi_weightage', 'competency_weight'] as $col) {
                if (Schema::hasColumn('designations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
