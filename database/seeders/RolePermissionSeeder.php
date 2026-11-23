<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // --- Roles (only these two are built-in; create more from User Management > Roles) ---
        foreach ([Role::SUPER_ADMIN, Role::EMPLOYEE] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // --- Permissions ---
        $permissions = [
            'dashboard.view',
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'leave.apply', 'leave.approve', 'leave.reject', 'leave.view',
            'attendance.manage',
            'payroll.manage',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Super Admin -> everything
        Role::where('name', Role::SUPER_ADMIN)->first()->syncPermissions(Permission::all());

        // Employee -> self service
        Role::where('name', Role::EMPLOYEE)->first()->syncPermissions([
            'dashboard.view', 'leave.apply', 'leave.view',
        ]);
    }
}
