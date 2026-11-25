<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('branches', 'pincode')) {
            DB::statement('ALTER TABLE `branches` MODIFY `pincode` VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branches', 'pincode')) {
            DB::statement("UPDATE `branches` SET `pincode` = '' WHERE `pincode` IS NULL");
            DB::statement('ALTER TABLE `branches` MODIFY `pincode` VARCHAR(255) NOT NULL');
        }
    }
};
