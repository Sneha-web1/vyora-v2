<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('branches', 'email')) {
            DB::statement('ALTER TABLE `branches` MODIFY `email` VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branches', 'email')) {
            DB::statement("UPDATE `branches` SET `email` = '' WHERE `email` IS NULL");
            DB::statement('ALTER TABLE `branches` MODIFY `email` VARCHAR(255) NOT NULL');
        }
    }
};
