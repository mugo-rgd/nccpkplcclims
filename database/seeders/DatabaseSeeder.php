<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\LaboratoryConfigurationPermissionSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            EquipmentCategorySeeder::class,
            EquipmentTypeSeeder::class,
            EquipmentManufacturerSeeder::class,
        ]);
        $this->call([
        LaboratoryConfigurationPermissionSeeder::class,
    ]);
    }
}
