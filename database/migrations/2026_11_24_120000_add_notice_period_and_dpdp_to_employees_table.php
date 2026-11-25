<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('employees', 'notice_period_days')) {
                $table->unsignedSmallInteger('notice_period_days')->default(30);
            }
            // DPDP (Digital Personal Data Protection Act, 2023) consent record
            if (!Schema::hasColumn('employees', 'dpdp_privacy_notice_ack')) {
                $table->boolean('dpdp_privacy_notice_ack')->default(false);
            }
            if (!Schema::hasColumn('employees', 'dpdp_consent_given')) {
                $table->boolean('dpdp_consent_given')->default(false);
            }
            if (!Schema::hasColumn('employees', 'dpdp_sharing_consent')) {
                $table->boolean('dpdp_sharing_consent')->default(false);
            }
            if (!Schema::hasColumn('employees', 'dpdp_consent_mode')) {
                $table->string('dpdp_consent_mode', 20)->nullable();
            }
            if (!Schema::hasColumn('employees', 'dpdp_consent_at')) {
                $table->timestamp('dpdp_consent_at')->nullable();
            }
            if (!Schema::hasColumn('employees', 'dpdp_consent_version')) {
                $table->string('dpdp_consent_version', 20)->nullable();
            }
            if (!Schema::hasColumn('employees', 'dpdp_consent_ip')) {
                $table->string('dpdp_consent_ip', 45)->nullable();
            }
            if (!Schema::hasColumn('employees', 'dpdp_consent_recorded_by')) {
                $table->string('dpdp_consent_recorded_by', 150)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            foreach ([
                'notice_period_days', 'dpdp_privacy_notice_ack', 'dpdp_consent_given', 'dpdp_sharing_consent',
                'dpdp_consent_mode', 'dpdp_consent_at', 'dpdp_consent_version', 'dpdp_consent_ip',
                'dpdp_consent_recorded_by',
            ] as $col) {
                if (Schema::hasColumn('employees', $col)) {
                    $table->dropColumn($col);
                }
            }
            // `address` is owned by the earlier EMS-columns migration, so it is left untouched here.
        });
    }
};
