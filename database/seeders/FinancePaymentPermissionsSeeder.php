<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FinancePaymentPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';
        $permissions = ['payments.view', 'payments.record', 'payments.confirm', 'payments.reject', 'payments.receipts.download'];
        foreach ($permissions as $name) { Permission::findOrCreate($name, $guard); }
        foreach (['finance', 'super_admin', 'lab_manager', 'supervisor'] as $name) {
            $role = Role::findOrCreate($name, $guard);
            $role->givePermissionTo($permissions);
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
