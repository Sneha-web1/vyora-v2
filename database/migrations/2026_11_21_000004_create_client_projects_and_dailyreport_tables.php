<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('client_projects')) {
            Schema::create('client_projects', function (Blueprint $table) {
                $table->increments('project_id');
                $table->integer('client_id')->default(0);
                $table->string('project_name', 255);
                $table->string('work_type', 100)->nullable();
                $table->text('notes')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->dateTime('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('dailyreport')) {
            Schema::create('dailyreport', function (Blueprint $table) {
                $table->increments('report_ID');
                $table->dateTime('DrEntryDate')->useCurrent()->nullable();
                $table->dateTime('CreationDate');
                $table->unsignedInteger('ProjectID');
                $table->text('WorkDescription');
                $table->string('StartingTime', 10);
                $table->string('EndingTime', 10);
                $table->string('TotalHours', 10);
                $table->unsignedInteger('EmpId')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dailyreport');
        Schema::dropIfExists('client_projects');
    }
};
