<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KplcPermissionSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🔄 Setting up permissions and roles...');

        // First, clear cache
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Clear existing permissions (MySQL safe)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define permissions by module
        $permissions = [
            // Dashboard
            'view_dashboard' => 'View dashboard',
            'view_reports' => 'View reports',

            // Calibrations
            'view_calibrations' => 'View all calibrations',
            'create_calibrations' => 'Create calibrations',
            'edit_calibrations' => 'Edit calibrations',
            'delete_calibrations' => 'Delete calibrations',
            'approve_calibrations' => 'Approve calibrations',
            'reject_calibrations' => 'Reject calibrations',
            'perform_calibrations' => 'Perform calibration tests',

            // Certificates
            'view_certificates' => 'View certificates',
            'generate_certificates' => 'Generate certificates',
            'sign_certificates' => 'Sign certificates',
            'verify_certificates' => 'Verify certificates',

            // Customers
            'view_customers' => 'View customers',
            'create_customers' => 'Create customers',
            'edit_customers' => 'Edit customers',
            'delete_customers' => 'Delete customers',

            // Equipment
            'view_equipment' => 'View equipment',
            'manage_equipment' => 'Manage equipment',
            'dispatch_equipment' => 'Dispatch equipment',
            'receive_equipment' => 'Receive equipment',

            // Tests
            'view_tests' => 'View tests',
            'create_tests' => 'Create tests',
            'edit_tests' => 'Edit tests',
            'delete_tests' => 'Delete tests',
            'verify_tests' => 'Verify tests',

            // Quality Control
            'quality_check' => 'Perform quality checks',
            'quality_approve' => 'Approve quality',
            'quality_reject' => 'Reject quality',
            'view_quality_reports' => 'View quality reports',
            'generate_quality_reports' => 'Generate quality reports',

            // Users
            'view_users' => 'View users',
            'create_users' => 'Create users',
            'edit_users' => 'Edit users',
            'delete_users' => 'Delete users',
            'manage_roles' => 'Manage roles and permissions',

            // System
            'view_audit_logs' => 'View audit logs',
            'manage_settings' => 'Manage system settings',
            'export_data' => 'Export data',
            'import_data' => 'Import data',
        ];

        $this->command->info('Creating permissions...');
        foreach ($permissions as $name => $description) {
            Permission::create([
                'name' => $name,
                'guard_name' => 'web',
                'description' => $description,
            ]);
        }
        $this->command->info('✅ ' . count($permissions) . ' permissions created');

        // Define common permissions for technicians (both lab_technician and technician)
        $technicianPermissions = [
            'view_dashboard',
            'view_calibrations', 'create_calibrations', 'perform_calibrations',
            'view_certificates',
            'view_customers',
            'view_equipment',
            // Test permissions
            'view_tests', 'create_tests', 'edit_tests', 'delete_tests', 'verify_tests',
            // Quality permissions
            'quality_check', 'view_quality_reports',
        ];

        // Define roles with their permissions
        $rolePermissions = [
            'super_admin' => Permission::all()->pluck('name')->toArray(),

            'lab_manager' => [
                'view_dashboard', 'view_reports',
                'view_calibrations', 'create_calibrations', 'edit_calibrations', 'approve_calibrations', 'reject_calibrations',
                'view_certificates', 'generate_certificates', 'sign_certificates', 'verify_certificates',
                'view_customers', 'create_customers', 'edit_customers',
                'view_equipment', 'manage_equipment', 'dispatch_equipment', 'receive_equipment',
                'view_users', 'create_users', 'edit_users',
                'view_audit_logs', 'export_data',
            ],

            'supervisor' => [
                'view_dashboard', 'view_reports',
                'view_calibrations', 'create_calibrations', 'edit_calibrations', 'approve_calibrations',
                'view_certificates', 'generate_certificates', 'sign_certificates',
                'view_customers', 'create_customers', 'edit_customers',
                'view_equipment', 'manage_equipment',
                'view_users',
            ],

            'quality_assurance' => [
                'view_dashboard', 'view_reports',
                'view_calibrations', 'approve_calibrations', 'reject_calibrations',
                'view_certificates', 'verify_certificates',
                'view_equipment',
            ],

            // lab_technician now has the same permissions as technician
            'lab_technician' => $technicianPermissions,

            'customer_service' => [
                'view_dashboard',
                'view_calibrations',
                'view_certificates',
                'view_customers', 'create_customers', 'edit_customers',
                'view_equipment', 'receive_equipment',
            ],

            'customer' => [
                'view_dashboard',
                'view_calibrations',
                'view_certificates',
                'view_equipment',
            ],

            // technician has the same permissions as lab_technician
            'technician' => $technicianPermissions,
        ];

        $this->command->info('Creating roles and assigning permissions...');
        foreach ($rolePermissions as $roleName => $permissionsList) {
            $role = Role::create([
                'name' => $roleName,
                'guard_name' => 'web',
                'display_name' => ucwords(str_replace('_', ' ', $roleName)),
                'description' => "Role for {$roleName}",
            ]);

            $role->syncPermissions($permissionsList);
            $this->command->info("✅ Created role: {$roleName}");
        }

        // Assign roles to existing users
        $this->assignRolesToUsers();

        $this->command->info('✅ Permissions and roles setup completed!');
    }

    private function assignRolesToUsers()
    {
        $this->command->info('Assigning roles to users...');

        // Map legacy roles to new roles
        $roleMap = [
            'admin' => 'super_admin',
            'manager' => 'lab_manager',
            'supervisor' => 'supervisor',
            'checker' => 'quality_assurance',
            'technician' => 'lab_technician',  // Map technician to lab_technician
            'engineer' => 'lab_technician',
            'investigator' => 'quality_assurance',
            'customer' => 'customer',
            'customer_service' => 'customer_service',
        ];

        $users = User::all();
        $count = 0;

        foreach ($users as $user) {
            if ($user->role && isset($roleMap[$user->role])) {
                $newRole = $roleMap[$user->role];
                $user->assignRole($newRole);
                $count++;
            } else {
                // Default to customer if no role
                $user->assignRole('customer');
                $count++;
            }
        }

        $this->command->info("✅ Assigned roles to {$count} users");
    }
}
