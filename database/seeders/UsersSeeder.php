<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Create Lab Manager
        $labManager = User::updateOrCreate(
            ['email' => 'labmanager@kplc.co.ke'],
            [
                'name' => 'Lab Manager',
                'password' => Hash::make('Manager@123'),
                'role' => 'manager',
                'is_active' => true,
                'status' => 1,
            ]
        );
        $labManager->assignRole('lab_manager');

        // Create Supervisor
        $supervisor = User::updateOrCreate(
            ['email' => 'supervisor@kplc.co.ke'],
            [
                'name' => 'Lab Supervisor',
                'password' => Hash::make('Supervisor@123'),
                'role' => 'supervisor',
                'is_active' => true,
                'status' => 1,
            ]
        );
        $supervisor->assignRole('supervisor');

        // Create Quality Assurance
        $qa = User::updateOrCreate(
            ['email' => 'qa@kplc.co.ke'],
            [
                'name' => 'Quality Assurance',
                'password' => Hash::make('QA@123'),
                'role' => 'checker',
                'is_active' => true,
                'status' => 1,
            ]
        );
        $qa->assignRole('quality_assurance');

        $this->command->info('✅ Users created:');
        $this->command->info('Lab Manager: labmanager@kplc.co.ke / Manager@123');
        $this->command->info('Supervisor: supervisor@kplc.co.ke / Supervisor@123');
        $this->command->info('QA: qa@kplc.co.ke / QA@123');
    }
}
