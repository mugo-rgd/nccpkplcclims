<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get existing roles
        $labManager = Role::findByName('lab_manager');
        $supervisor = Role::findByName('supervisor');
        $superAdmin = Role::findByName('super_admin');

        // Define permission names (must exist in database)
        $allPermissions = [
            'view_service_types',
            'create_service_types',
            'edit_service_types',
            'delete_service_types',
            'view_equipment_types',
            'create_equipment_types',
            'edit_equipment_types',
            'delete_equipment_types',
        ];

        $supervisorPermissions = [
            'view_service_types',
            'create_service_types',
            'edit_service_types',
            'view_equipment_types',
            'create_equipment_types',
            'edit_equipment_types',
        ];

        // Assign permissions to roles
        $superAdmin->givePermissionTo($allPermissions);
        $labManager->givePermissionTo($allPermissions);
        $supervisor->givePermissionTo($supervisorPermissions);

        $this->command->info('Permissions assigned successfully!');
        $this->command->info('Super Admin and Lab Manager got all 8 permissions');
        $this->command->info('Supervisor got 6 permissions (no delete permissions)');
    }
}
