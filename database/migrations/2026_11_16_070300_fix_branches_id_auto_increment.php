<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fixes existing `branches` tables where the `id` column was created
     * without AUTO_INCREMENT (e.g. via manual import), causing:
     * "Field 'id' doesn't have a default value".
     *
     * This does NOT drop the table or touch existing rows/data.
     */
    public function up(): void
    {
        if (!Schema::hasTable('branches')) {
            return;
        }

        // Make sure id is the primary key AND auto_increment.
        // MODIFY preserves existing data; it only changes the column definition.
        DB::statement('ALTER TABLE `branches` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');

        // If, for some reason, id was never set as primary key at all,
        // uncomment the line below (will error harmlessly if PK already exists):
        // DB::statement('ALTER TABLE `branches` ADD PRIMARY KEY (`id`)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('branches')) {
            return;
        }

        // Revert auto_increment (keeps column, just removes the attribute)
        DB::statement('ALTER TABLE `branches` MODIFY `id` BIGINT UNSIGNED NOT NULL');
    }
};
