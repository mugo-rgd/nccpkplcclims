<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Service Type permissions
        $serviceTypePermissions = [
            'view_service_types',
            'create_service_types',
            'edit_service_types',
            'delete_service_types',
        ];

        foreach ($serviceTypePermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Equipment Type permissions
        $equipmentTypePermissions = [
            'view_equipment_types',
            'create_equipment_types',
            'edit_equipment_types',
            'delete_equipment_types',
        ];

        foreach ($equipmentTypePermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Optional: Assign to admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());
    }
}
