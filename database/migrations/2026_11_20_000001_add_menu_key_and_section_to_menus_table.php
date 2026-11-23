<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            // identifies a menu defined in config/menus.php so `php artisan menus:sync` can be re-run safely
            if (!Schema::hasColumn('menus', 'menu_key')) {
                $table->string('menu_key')->nullable()->unique()->after('id');
            }
            // grey heading printed above a top-level menu in the sidebar (HRM, PAYROLL ...)
            if (!Schema::hasColumn('menus', 'section')) {
                $table->string('section')->nullable()->after('menu_key');
            }
        });

        // a role can only be mapped to a menu once
        Schema::table('role_menu', function (Blueprint $table) {
            $table->unique(['role_id', 'menu_id'], 'role_menu_role_id_menu_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('role_menu', function (Blueprint $table) {
            $table->dropUnique('role_menu_role_id_menu_id_unique');
        });

        Schema::table('menus', function (Blueprint $table) {
            if (Schema::hasColumn('menus', 'menu_key')) {
                $table->dropUnique(['menu_key']);
                $table->dropColumn('menu_key');
            }
            if (Schema::hasColumn('menus', 'section')) {
                $table->dropColumn('section');
            }
        });
    }
};
