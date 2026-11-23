<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Only two built-in roles remain: Super Admin and Employee.
 * The old "Admin" and "HR" roles (and their demo logins) are removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        $removeIds = DB::table('roles')->whereIn('name', ['Admin', 'HR'])->pluck('id')->all();

        if (!empty($removeIds)) {
            // users that were logging in with these roles
            foreach (DB::table('users')->whereIn('role_id', $removeIds)->pluck('id') as $userId) {
                try {
                    DB::table('users')->where('id', $userId)->delete();
                } catch (QueryException $e) {
                    // user is referenced elsewhere - keep the row but lock it
                    DB::table('users')->where('id', $userId)->update(['status' => 0, 'role_id' => null]);
                }
            }

            DB::table('model_has_roles')->whereIn('role_id', $removeIds)->delete();
            DB::table('role_has_permissions')->whereIn('role_id', $removeIds)->delete();
            DB::table('role_menu')->whereIn('role_id', $removeIds)->delete();
            DB::table('roles')->whereIn('id', $removeIds)->delete();
        }

        // users.role_id existed before Spatie assignments did - mirror it into model_has_roles
        foreach (DB::table('users')->whereNotNull('role_id')->get(['id', 'role_id']) as $user) {
            DB::table('model_has_roles')->insertOrIgnore([
                'role_id'    => $user->role_id,
                'model_type' => 'App\\Models\\User',
                'model_id'   => $user->id,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // intentionally empty - removed demo roles/users are not restored
    }
};
