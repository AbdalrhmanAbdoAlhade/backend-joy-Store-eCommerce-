<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'orders.view',
            'orders.update_status',
            'orders.create_manual',
            'products.manage',
            'inventory.manage',
            'reviews.moderate',
            'tickets.reply',
            'returns.manage',
            'coupons.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Employee gets all permissions
        $employeeRole->syncPermissions($permissions);

        // Admin gets everything (Spatie handles this via Gate::before or just assign all)
        $adminRole->syncPermissions($permissions);

        // Create default admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@toys-store.com'],
            [
                'name' => 'Admin',
                'phone' => '01000000000',
                'password' => Hash::make('password'),
                'status' => 'active',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');
    }
}
