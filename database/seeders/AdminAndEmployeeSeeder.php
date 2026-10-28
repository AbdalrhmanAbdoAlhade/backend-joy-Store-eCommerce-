<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminAndEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // مسح الكاش بتاع Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ===== الصلاحيات =====
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
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // ===== الأدوار =====
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // الموظف ياخد كل الصلاحيات
        $employeeRole->syncPermissions($permissions);

        // الأدمن ياخد كل الصلاحيات كمان
        $adminRole->syncPermissions($permissions);

        // ===== حساب الأدمن =====
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

        // ===== حساب الموظف =====
        $employee = User::firstOrCreate(
            ['email' => 'employee@toys-store.com'],
            [
                'name' => 'موظف تجريبي',
                'phone' => '01111111111',
                'password' => Hash::make('password'),
                'status' => 'active',
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        $employee->assignRole('employee');
        $employee->syncPermissions($permissions); // لو حابب تعطيه صلاحيات مباشرة كمان

        $this->command->info('✅ تم إنشاء الأدمن والموظف بنجاح');
        $this->command->info('Admin   → admin@toys-store.com / password');
        $this->command->info('Employee → employee@toys-store.com / password');
    }
}
