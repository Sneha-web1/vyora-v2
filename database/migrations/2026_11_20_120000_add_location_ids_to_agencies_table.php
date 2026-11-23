<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            if (!Schema::hasColumn('agencies', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable()->after('address');
            }
            if (!Schema::hasColumn('agencies', 'country')) {
                $table->string('country', 100)->nullable()->after('country_id');
            }
            if (!Schema::hasColumn('agencies', 'state_id')) {
                $table->unsignedBigInteger('state_id')->nullable()->after('country');
            }
            if (!Schema::hasColumn('agencies', 'city_id')) {
                $table->unsignedBigInteger('city_id')->nullable()->after('state_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn(['country_id', 'country', 'state_id', 'city_id']);
        });
    }
};
