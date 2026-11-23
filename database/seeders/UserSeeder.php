<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', Role::SUPER_ADMIN)->firstOrFail();

        // Employees log in from the `employees` table (/employee/login), so only Super Admin is seeded here.
        $user = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password123'),
                'role_id'  => $role->id,
                'status'   => 1,
            ]
        );

        $user->syncRoles([$role->name]);
    }
}
