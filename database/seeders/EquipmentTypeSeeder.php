<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EquipmentType;
use App\Models\EquipmentCategory;

class EquipmentTypeSeeder extends Seeder
{
    public function run()
    {
        // Get category IDs
        $categories = EquipmentCategory::pluck('id', 'code');

        $types = [
            // Calibration Equipment
            [
                'name' => 'Multifunctional Calibrator',
                'code' => 'MFC',
                'description' => 'Multifunctional calibrator for electrical parameters',
                'category_id' => $categories['CAL'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'AC/DC Calibration Bench',
                'code' => 'CDC',
                'description' => 'AC/DC calibration bench for electrical equipment',
                'category_id' => $categories['CAL'] ?? null,
                'is_active' => true,
            ],
            // Safety Equipment
            [
                'name' => 'Insulation Tester',
                'code' => 'INS',
                'description' => 'HV insulation tester adapter',
                'category_id' => $categories['SAF'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Electrical Safety Analyzer',
                'code' => 'ESA',
                'description' => 'Electrical safety analyzer for testing',
                'category_id' => $categories['SAF'] ?? null,
                'is_active' => true,
            ],
            // Measurement Equipment
            [
                'name' => 'Advanced Multimeter',
                'code' => 'AMM',
                'description' => 'Advanced multimeter for precision measurements',
                'category_id' => $categories['MEA'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Digital Multimeter',
                'code' => 'DMM',
                'description' => 'Digital multimeter for general measurements',
                'category_id' => $categories['MEA'] ?? null,
                'is_active' => true,
            ],
            // Reference Standards
            [
                'name' => 'Portable Reference Standard',
                'code' => 'PRS',
                'description' => 'Portable reference standard for field calibration',
                'category_id' => $categories['REF'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Phase Reference Meter',
                'code' => 'PRM',
                'description' => 'Single and 3 phase reference meter',
                'category_id' => $categories['REF'] ?? null,
                'is_active' => true,
            ],
            // Test Equipment
            [
                'name' => 'CT Analyzer',
                'code' => 'CTA',
                'description' => 'Current transformer analyzer',
                'category_id' => $categories['TES'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'Meter Test Bench',
                'code' => 'MTB',
                'description' => 'Single and 3 phase meter test bench',
                'category_id' => $categories['TES'] ?? null,
                'is_active' => true,
            ],
            [
                'name' => 'IP Protection Test Chamber',
                'code' => 'IPC',
                'description' => 'IP protection test chamber',
                'category_id' => $categories['TES'] ?? null,
                'is_active' => true,
            ],
            // Environmental
            [
                'name' => 'Data Logger',
                'code' => 'DAL',
                'description' => 'Temperature and humidity data logger',
                'category_id' => $categories['ENV'] ?? null,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            EquipmentType::create($type);
        }
    }
}
