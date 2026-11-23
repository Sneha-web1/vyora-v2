<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'posting_country')) {
                $table->string('posting_country', 255)->nullable();
            }
            if (!Schema::hasColumn('employees', 'blood_group')) {
                $table->string('blood_group', 10)->nullable();
            }
            if (!Schema::hasColumn('employees', 'login_pin')) {
                $table->string('login_pin', 255)->nullable();
            }
            if (!Schema::hasColumn('employees', 'is_pin_enabled')) {
                $table->boolean('is_pin_enabled')->default(false);
            }
            if (!Schema::hasColumn('employees', 'pin_set_at')) {
                $table->timestamp('pin_set_at')->nullable();
            }
            if (!Schema::hasColumn('employees', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            if (!Schema::hasColumn('employees', 'supervisor_id')) {
                $table->unsignedBigInteger('supervisor_id')->nullable();
            }
            if (!Schema::hasColumn('employees', 'is_supervisor')) {
                $table->boolean('is_supervisor')->default(false);
            }
            if (!Schema::hasColumn('employees', 'week_off_days')) {
                $table->json('week_off_days')->nullable();
            }
            if (!Schema::hasColumn('employees', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('employees', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable();
            }
        });

        // legacy HRMS columns -> nullable
        Schema::table('employees', function (Blueprint $table) {
            $table->string('patId', 15)->nullable()->change();
            $table->integer('posting_district')->nullable()->change();
            $table->string('band', 50)->nullable()->change();
            $table->integer('department_id')->nullable()->change();
            $table->date('retirement_date')->nullable()->change();
            $table->integer('offline_review')->nullable()->change();
            $table->integer('pmp_type')->nullable()->change();
            $table->integer('category_id')->nullable()->change();
            $table->double('last_year_pli')->nullable()->change();
            $table->double('next_year_pli')->nullable()->change();
            $table->string('increment_last_yr', 70)->nullable()->change();
            $table->string('profile_image', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            foreach (['posting_country', 'blood_group', 'login_pin', 'is_pin_enabled', 'pin_set_at', 'last_login_at',
                      'supervisor_id', 'is_supervisor', 'week_off_days', 'address', 'deleted_at'] as $col) {
                if (Schema::hasColumn('employees', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
