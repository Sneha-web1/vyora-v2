<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('skills', 'status')) {
            return;
        }
        Schema::table('skills', function (Blueprint $table) {
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
