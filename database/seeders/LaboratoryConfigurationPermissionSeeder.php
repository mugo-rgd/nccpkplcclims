<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LaboratoryConfigurationPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            /*
            |--------------------------------------------------------------------------
            | Equipment Pricing
            |--------------------------------------------------------------------------
            */
            'view_equipment_pricing',
            'create_equipment_pricing',
            'edit_equipment_pricing',
            'delete_equipment_pricing',

            /*
            |--------------------------------------------------------------------------
            | Laboratory Services
            |--------------------------------------------------------------------------
            */
            'view_laboratory_services',
            'create_laboratory_services',
            'edit_laboratory_services',
            'delete_laboratory_services',

            /*
            |--------------------------------------------------------------------------
            | Equipment Categories
            |--------------------------------------------------------------------------
            */
            'view_equipment_categories',
            'create_equipment_categories',
            'edit_equipment_categories',
            'delete_equipment_categories',

            /*
            |--------------------------------------------------------------------------
            | Equipment Manufacturers
            |--------------------------------------------------------------------------
            */
            'view_equipment_manufacturers',
            'create_equipment_manufacturers',
            'edit_equipment_manufacturers',
            'delete_equipment_manufacturers',

            /*
            |--------------------------------------------------------------------------
            | Audit Trail
            |--------------------------------------------------------------------------
            */
            'view_laboratory_configuration_audit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Super Administrator
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::where('name', 'super_admin')->first();

        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        /*
        |--------------------------------------------------------------------------
        | Laboratory Manager
        |--------------------------------------------------------------------------
        */

        $labManager = Role::where('name', 'lab_manager')->first();

        if ($labManager) {

            $labManager->givePermissionTo([

                'view_equipment_pricing',
                'create_equipment_pricing',
                'edit_equipment_pricing',
                'delete_equipment_pricing',

                'view_laboratory_services',
                'create_laboratory_services',
                'edit_laboratory_services',
                'delete_laboratory_services',

                'view_equipment_categories',
                'create_equipment_categories',
                'edit_equipment_categories',
                'delete_equipment_categories',

                'view_equipment_manufacturers',
                'create_equipment_manufacturers',
                'edit_equipment_manufacturers',
                'delete_equipment_manufacturers',

                'view_laboratory_configuration_audit',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Supervisor
        |--------------------------------------------------------------------------
        */

        $supervisor = Role::where('name', 'supervisor')->first();

        if ($supervisor) {

            $supervisor->givePermissionTo([

                'view_equipment_pricing',
                'create_equipment_pricing',
                'edit_equipment_pricing',

                'view_laboratory_services',
                'create_laboratory_services',
                'edit_laboratory_services',

                'view_equipment_categories',
                'create_equipment_categories',
                'edit_equipment_categories',

                'view_equipment_manufacturers',
                'create_equipment_manufacturers',
                'edit_equipment_manufacturers',

                'view_laboratory_configuration_audit',
            ]);
        }
    }
}

