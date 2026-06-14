<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permissions the application understands. Keep these stable — policies and
     * route middleware reference them by name.
     */
    public const PERMISSIONS = [
        'manage products',
        'manage inventory',
        'manage customers',
        'billing access',
        'analytics access',
        'settings access',
    ];

    public function run(): void
    {
        // Reset cached roles and permissions so re-seeding is idempotent.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        $cashier = Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web']);

        $admin->syncPermissions(self::PERMISSIONS);

        $staff->syncPermissions([
            'manage products',
            'manage inventory',
            'manage customers',
            'billing access',
        ]);

        $cashier->syncPermissions([
            'billing access',
        ]);
    }
}
