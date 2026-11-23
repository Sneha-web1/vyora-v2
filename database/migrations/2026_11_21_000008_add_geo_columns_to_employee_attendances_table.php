<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_attendances', 'check_in_latitude')) {
                $table->decimal('check_in_latitude', 10, 7)->nullable()->after('check_out_ip');
            }
            if (!Schema::hasColumn('employee_attendances', 'check_in_longitude')) {
                $table->decimal('check_in_longitude', 10, 7)->nullable()->after('check_in_latitude');
            }
            if (!Schema::hasColumn('employee_attendances', 'check_out_latitude')) {
                $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_in_longitude');
            }
            if (!Schema::hasColumn('employee_attendances', 'check_out_longitude')) {
                $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropColumn(['check_in_latitude', 'check_in_longitude', 'check_out_latitude', 'check_out_longitude']);
        });
    }
};
