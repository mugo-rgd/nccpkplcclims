<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class QualityPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Quality permissions
        $permissions = [
            // Dashboard
            ['name' => 'view_quality_dashboard', 'group' => 'Dashboard'],

            // Quality Objectives
            ['name' => 'view_quality_objectives', 'group' => 'Quality Objectives'],
            ['name' => 'create_quality_objectives', 'group' => 'Quality Objectives'],
            ['name' => 'edit_quality_objectives', 'group' => 'Quality Objectives'],
            ['name' => 'delete_quality_objectives', 'group' => 'Quality Objectives'],

            // Quarterly Achievements
            ['name' => 'view_achievements', 'group' => 'Achievements'],
            ['name' => 'create_achievements', 'group' => 'Achievements'],
            ['name' => 'edit_achievements', 'group' => 'Achievements'],
            ['name' => 'delete_achievements', 'group' => 'Achievements'],
            ['name' => 'bulk_enter_achievements', 'group' => 'Achievements'],

            // Corrective Actions
            ['name' => 'view_corrective_actions', 'group' => 'Corrective Actions'],
            ['name' => 'create_corrective_actions', 'group' => 'Corrective Actions'],
            ['name' => 'edit_corrective_actions', 'group' => 'Corrective Actions'],
            ['name' => 'delete_corrective_actions', 'group' => 'Corrective Actions'],
            ['name' => 'approve_corrective_actions', 'group' => 'Corrective Actions'],

            // Reports
            ['name' => 'view_quality_reports', 'group' => 'Reports'],
            ['name' => 'generate_quality_reports', 'group' => 'Reports'],
            ['name' => 'export_quality_reports', 'group' => 'Reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'guard_name' => 'web'
            ], [
                'group' => $permission['group'],
                'description' => $permission['name']
            ]);
        }

        // Assign permissions to roles
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(Permission::all());
        }

        $labManager = Role::where('name', 'lab_manager')->first();
        if ($labManager) {
            $labManager->givePermissionTo([
                'view_quality_dashboard',
                'view_quality_objectives',
                'create_quality_objectives',
                'edit_quality_objectives',
                'view_achievements',
                'create_achievements',
                'edit_achievements',
                'bulk_enter_achievements',
                'view_corrective_actions',
                'create_corrective_actions',
                'edit_corrective_actions',
                'approve_corrective_actions',
                'view_quality_reports',
                'generate_quality_reports',
                'export_quality_reports'
            ]);
        }

        $qualityAssurance = Role::where('name', 'quality_assurance')->first();
        if ($qualityAssurance) {
            $qualityAssurance->givePermissionTo([
                'view_quality_dashboard',
                'view_quality_objectives',
                'view_achievements',
                'create_achievements',
                'view_corrective_actions',
                'create_corrective_actions',
                'edit_corrective_actions',
                'approve_corrective_actions',
                'view_quality_reports',
                'generate_quality_reports'
            ]);
        }

        $supervisor = Role::where('name', 'supervisor')->first();
        if ($supervisor) {
            $supervisor->givePermissionTo([
                'view_quality_dashboard',
                'view_quality_objectives',
                'view_achievements',
                'create_achievements',
                'view_corrective_actions',
                'create_corrective_actions',
                'view_quality_reports'
            ]);
        }
    }
}
