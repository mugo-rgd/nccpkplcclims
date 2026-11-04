<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SystemDocumentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view_system_documents',
            'download_system_documents',
            'upload_system_documents',
            'edit_system_documents',
            'approve_system_documents',
            'archive_system_documents',
            'delete_system_documents',
            'manage_system_documents',
            'view_restricted_system_documents',
            'review_system_documents',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Technician roles
        |--------------------------------------------------------------------------
        */

        foreach ([
            'lab_technician',
            'technician',
        ] as $roleName) {
            $role = Role::where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if ($role) {
                $role->givePermissionTo([
                    'view_system_documents',
                    'download_system_documents',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Lab Manager
        |--------------------------------------------------------------------------
        */

        $labManager = Role::where('name', 'lab_manager')
            ->where('guard_name', 'web')
            ->first();

 
if ($labManager) {
    $labManager->givePermissionTo([
        'view_system_documents',
        'download_system_documents',
        'upload_system_documents',
        'edit_system_documents',
        'approve_system_documents',
        'archive_system_documents',
        'review_system_documents',
    ]);
}

        /*
        |--------------------------------------------------------------------------
        | Super Administrator
        |--------------------------------------------------------------------------
        */

       
        $superAdmin = Role::where('name', 'super_admin')
    ->where('guard_name', 'web')
    ->first();

if ($superAdmin) {
    $superAdmin->givePermissionTo($permissions);
}

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
