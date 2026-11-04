<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EquipmentCategory;

class EquipmentCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Calibration Equipment',
                'code' => 'CAL',
                'description' => 'Equipment used for calibration purposes including reference standards and calibrators',
                'is_active' => true,
            ],
            [
                'name' => 'Safety Equipment',
                'code' => 'SAF',
                'description' => 'Equipment used for safety testing and electrical safety analysis',
                'is_active' => true,
            ],
            [
                'name' => 'Measurement Equipment',
                'code' => 'MEA',
                'description' => 'Equipment used for electrical measurement including multimeters and analyzers',
                'is_active' => true,
            ],
            [
                'name' => 'Reference Standards',
                'code' => 'REF',
                'description' => 'Reference standard equipment used as calibration benchmarks',
                'is_active' => true,
            ],
            [
                'name' => 'Test Equipment',
                'code' => 'TES',
                'description' => 'Equipment used for testing including test benches and analyzers',
                'is_active' => true,
            ],
            [
                'name' => 'Environmental',
                'code' => 'ENV',
                'description' => 'Environmental monitoring equipment for temperature and humidity',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            EquipmentCategory::create($category);
        }
    }
}
