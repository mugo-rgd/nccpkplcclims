<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Calibration Equipment', 'code' => 'CAL', 'description' => 'Equipment used for calibration purposes'],
            ['name' => 'Safety Equipment', 'code' => 'SAF', 'description' => 'Equipment used for safety testing'],
            ['name' => 'Measurement Equipment', 'code' => 'MEA', 'description' => 'Equipment used for measurement'],
            ['name' => 'Reference Standards', 'code' => 'REF', 'description' => 'Reference standard equipment'],
            ['name' => 'Test Equipment', 'code' => 'TES', 'description' => 'Equipment used for testing'],
            ['name' => 'Environmental', 'code' => 'ENV', 'description' => 'Environmental monitoring equipment'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
