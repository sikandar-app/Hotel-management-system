<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRoleName = 'Owner';
        $roles = [
            ['name' => $adminRoleName, 'guard_name' => 'web'],
            ['name' => 'Employee', 'guard_name' => 'web'],
            ['name' => 'Booking manager', 'guard_name' => 'web'],
        ];

        foreach ($roles as $role) {
            $roleExists = Role::where('name', $role['name'])->exists();
            if(!$roleExists) Role::create(['name' => $role['name']], ['guard_name' => $role['guard_name']]);
        }

        // Create Permissions
        $permissions = [
            'dashboard',
            'users-all',
            'users-view',
            'users-create',
            'users-edit',
            'users-delete',
            'roles-all',
            'roles-view',
            'roles-create',
            'roles-edit',
            'roles-delete',
            'roles-assign-permissions',
            'permissions-all',
            'permissions-view',
            'permissions-create',
            'permissions-edit',
            'permissions-delete',
            'booking-all',
            'booking-view',
            'booking-create',
            'booking-edit',
            'booking-delete',
            'booking-export',
            'booking-confirmed',
            'room-all',
            'room-view',
            'room-create',
            'room-edit',
            'room-delete',
            'tax-all',
            'tax-view',
            'tax-create',
            'tax-edit',
            'tax-delete',
            'invoices-all',
            'invoices-view',
            'invoices-create',
            'invoices-edit',
            'invoices-delete',
            'invoices-approved',
            'invoices-pdf-export',
            'monthly-occupancy-all',
            'monthly-occupancy-view',
            'occupancy-all',
            'occupancy-view',
            'expenses-all',
            'expenses-view',
            'expenses-create',
            'expenses-edit',
            'expenses-delete',
            'expense-category-all',
            'expense-category-view',
            'expense-category-create',
            'expense-category-edit',
            'expense-category-delete',
        ];

        $adminRole = Role::where('name' , $adminRoleName)->first();

        // Create and assign each permission
        foreach ($permissions as $permission) {
            $createdPermission = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);

            // Assign all permissions to admin role
            $adminRole->givePermissionTo($createdPermission);
        }
    }
}
