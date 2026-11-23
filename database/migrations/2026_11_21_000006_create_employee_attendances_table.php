<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_attendances')) {
            // Table does not exist yet on this database: create it fresh with every column.
            Schema::create('employee_attendances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->date('attendance_date');
                $table->timestamp('check_in_at')->nullable();
                $table->timestamp('check_out_at')->nullable();
                $table->string('check_in_ip', 45)->nullable();
                $table->string('check_out_ip', 45)->nullable();
                $table->decimal('check_in_latitude', 10, 7)->nullable();
                $table->decimal('check_in_longitude', 10, 7)->nullable();
                $table->decimal('check_out_latitude', 10, 7)->nullable();
                $table->decimal('check_out_longitude', 10, 7)->nullable();
                $table->enum('status', ['checked_in', 'checked_out'])->default('checked_in');
                $table->text('note')->nullable();
                $table->timestamps();

                $table->foreign('employee_id')
                    ->references('employee_id')
                    ->on('employees')
                    ->onDelete('cascade');

                $table->unique(['employee_id', 'attendance_date']);
                $table->index(['attendance_date', 'status']);
            });

            return;
        }

        // Table already exists on this database (created by an earlier migration run):
        // just make sure the geo-location columns are present, nothing else.
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
        Schema::dropIfExists('employee_attendances');
    }
};
