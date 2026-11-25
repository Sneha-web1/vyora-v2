<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_attendances', 'check_in_user_agent')) {
                $table->text('check_in_user_agent')->nullable()->after('check_out_longitude');
            }
            if (!Schema::hasColumn('employee_attendances', 'check_out_user_agent')) {
                $table->text('check_out_user_agent')->nullable()->after('check_in_user_agent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropColumn(['check_in_user_agent', 'check_out_user_agent']);
        });
    }
};
