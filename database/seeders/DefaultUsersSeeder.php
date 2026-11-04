<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    public function run()
    {
        // Create or update admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@kplc.co.ke'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'is_active' => true,
                'status' => 1,
            ]
        );
        $admin->assignRole('super_admin');

        // Create or update technician
        $tech = User::updateOrCreate(
            ['email' => 'tech@kplc.co.ke'],
            [
                'name' => 'Lab Technician',
                'password' => Hash::make('Tech@123'),
                'role' => 'technician',
                'is_active' => true,
                'status' => 1,
            ]
        );
        $tech->assignRole('lab_technician');

        // Create or update customer
        $customer = User::updateOrCreate(
            ['email' => 'customer@kplc.co.ke'],
            [
                'name' => 'Customer User',
                'password' => Hash::make('Customer@123'),
                'role' => 'customer',
                'is_active' => true,
                'status' => 1,
            ]
        );
        $customer->assignRole('customer');

        $this->command->info('Default users created/updated:');
        $this->command->info('Admin: admin@kplc.co.ke / Admin@123');
        $this->command->info('Technician: tech@kplc.co.ke / Tech@123');
        $this->command->info('Customer: customer@kplc.co.ke / Customer@123');
    }
}
