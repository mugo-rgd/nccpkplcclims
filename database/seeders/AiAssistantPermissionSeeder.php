<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AiAssistantPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $permissions = [
            'view_ai_assistant',
            'use_ai_assistant',
            'view_ai_conversations',
            'delete_ai_conversations',
            'view_ai_tool_logs',
        ];

        foreach ($permissions as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        /*
         * Super Administrator: full AI access.
         */
        $superAdmin = Role::query()
            ->where('name', 'super_admin')
            ->where('guard_name', 'web')
            ->first();

        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        /*
         * Laboratory Manager: full operational access,
         * excluding system-wide technical administration where appropriate.
         */
        $labManager = Role::query()
            ->where('name', 'lab_manager')
            ->where('guard_name', 'web')
            ->first();

        if ($labManager) {
            $labManager->givePermissionTo([
                'view_ai_assistant',
                'use_ai_assistant',
                'view_ai_conversations',
                'delete_ai_conversations',
                'view_ai_tool_logs',
            ]);
        }

        /*
         * Supervisor: use the assistant and manage own conversations.
         */
        $supervisor = Role::query()
            ->where('name', 'supervisor')
            ->where('guard_name', 'web')
            ->first();

        if ($supervisor) {
            $supervisor->givePermissionTo([
                'view_ai_assistant',
                'use_ai_assistant',
                'view_ai_conversations',
                'delete_ai_conversations',
            ]);
        }

        /*
         * Lab Technician: use the assistant and view own conversations.
         */
        $labTechnician = Role::query()
            ->where('name', 'lab_technician')
            ->where('guard_name', 'web')
            ->first();

        if ($labTechnician) {
            $labTechnician->givePermissionTo([
                'view_ai_assistant',
                'use_ai_assistant',
                'view_ai_conversations',
                'delete_ai_conversations',
            ]);
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
