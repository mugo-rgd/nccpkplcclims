<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Calibration permissions
            'view_calibrations',
            'create_calibrations',
            'edit_calibrations',
            'delete_calibrations',
            'approve_calibrations',
            'reject_calibrations',
            'assign_calibrations',
            'perform_calibrations',

            // Test permissions
            'view_tests',
            'create_tests',
            'edit_tests',
            'delete_tests',
            'verify_tests',

            // Certificate permissions
            'view_certificates',
            'create_certificates',
            'edit_certificates',
            'delete_certificates',
            'approve_certificates',
            'sign_certificates',
            'verify_certificates',

            // Quality Control permissions
            'quality_check',
            'quality_approve',
            'quality_reject',
            'view_quality_reports',
            'generate_quality_reports',

            // Customer permissions
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',

            // Equipment permissions
            'view_equipment',
            'create_equipment',
            'edit_equipment',
            'delete_equipment',

            // User permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',

            // Report permissions
            'view_reports',
            'generate_reports',

            // Audit permissions
            'view_audit_logs',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Define common permissions for technicians (both lab_technician and technician)
        $technicianPermissions = [
            'view_calibrations',
            'perform_calibrations',
            'view_tests',
            'create_tests',
            'edit_tests',
            'delete_tests',
            'verify_tests',
            'view_certificates',
            'create_certificates',
            'edit_certificates',
            'view_customers',
            'view_equipment',
            'quality_check',
            'view_quality_reports',
        ];

        // Create roles and assign permissions
        // Super Admin - Full access
        $role = Role::findOrCreate('super_admin');
        $role->givePermissionTo(Permission::all());

        // Admin - Most permissions except super admin
        $role = Role::findOrCreate('admin');
        $role->givePermissionTo([
            'view_calibrations', 'create_calibrations', 'edit_calibrations', 'delete_calibrations',
            'approve_calibrations', 'reject_calibrations', 'assign_calibrations',
            'view_tests', 'create_tests', 'edit_tests', 'delete_tests', 'verify_tests',
            'view_certificates', 'create_certificates', 'edit_certificates', 'delete_certificates',
            'approve_certificates', 'sign_certificates', 'verify_certificates',
            'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
            'view_equipment', 'create_equipment', 'edit_equipment', 'delete_equipment',
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_reports', 'generate_reports',
            'view_audit_logs',
        ]);

        // Quality Controller - Quality management permissions
        $role = Role::findOrCreate('quality_controller');
        $role->givePermissionTo([
            'view_calibrations',
            'approve_calibrations',
            'reject_calibrations',
            'view_tests',
            'verify_tests',
            'view_certificates',
            'approve_certificates',
            'verify_certificates',
            'quality_check',
            'quality_approve',
            'quality_reject',
            'view_quality_reports',
            'generate_quality_reports',
            'view_customers',
            'view_equipment',
            'view_reports',
        ]);

        // Lab Manager - Operational permissions
        $role = Role::findOrCreate('lab_manager');
        $role->givePermissionTo([
            'view_calibrations', 'create_calibrations', 'edit_calibrations',
            'assign_calibrations',
            'view_tests', 'create_tests', 'edit_tests', 'delete_tests',
            'view_certificates', 'create_certificates', 'edit_certificates', 'delete_certificates',
            'approve_certificates', 'sign_certificates',
            'view_customers', 'create_customers', 'edit_customers',
            'view_equipment', 'create_equipment', 'edit_equipment',
            'view_reports', 'generate_reports',
        ]);

        // Supervisor - Supervisory permissions
        $role = Role::findOrCreate('supervisor');
        $role->givePermissionTo([
            'view_calibrations',
            'view_tests',
            'verify_tests',
            'view_certificates',
            'verify_certificates',
            'view_customers',
            'view_equipment',
            'view_reports',
        ]);

        // Lab Technician - Same as Technician
        $role = Role::findOrCreate('lab_technician');
        $role->givePermissionTo($technicianPermissions);

        // Technician - Same as Lab Technician
        $role = Role::findOrCreate('technician');
        $role->givePermissionTo($technicianPermissions);

        // Customer - Limited access
        $role = Role::findOrCreate('customer');
        $role->givePermissionTo([
            'view_calibrations',
            'view_certificates',
        ]);
    }
}
