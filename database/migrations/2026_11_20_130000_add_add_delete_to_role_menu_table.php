<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Roles & Permissions: every menu of a role now has 4 switches  ->  View / Add / Edit / Delete.
 * (role_menu already had can_view + can_edit)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_menu', function (Blueprint $table) {
            if (!Schema::hasColumn('role_menu', 'can_add')) {
                $table->boolean('can_add')->default(0)->after('can_view');
            }
            if (!Schema::hasColumn('role_menu', 'can_delete')) {
                $table->boolean('can_delete')->default(0)->after('can_edit');
            }
        });

        // keep today's behaviour for existing roles: whoever could edit can also add, delete stays OFF
        DB::table('role_menu')->where('can_edit', 1)->update(['can_add' => 1]);
        // Super Admin owns everything
        $superAdminId = DB::table('roles')->where('name', 'Super Admin')->value('id');
        if ($superAdminId) {
            DB::table('role_menu')->where('role_id', $superAdminId)->update([
                'can_view' => 1, 'can_add' => 1, 'can_edit' => 1, 'can_delete' => 1,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('role_menu', function (Blueprint $table) {
            if (Schema::hasColumn('role_menu', 'can_add')) {
                $table->dropColumn('can_add');
            }
            if (Schema::hasColumn('role_menu', 'can_delete')) {
                $table->dropColumn('can_delete');
            }
        });
    }
};
