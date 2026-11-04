<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EquipmentManufacturer;

class EquipmentManufacturerSeeder extends Seeder
{
    public function run()
    {
        $manufacturers = [
            ['name' => 'Fluke', 'code' => 'FLK', 'description' => 'Fluke Corporation', 'website' => 'www.fluke.com', 'is_active' => true],
            ['name' => 'Conlog', 'code' => 'CON', 'description' => 'Conlog (Pty) Ltd', 'is_active' => true],
            ['name' => 'Hexing', 'code' => 'HEX', 'description' => 'Hexing Electrical Co.', 'is_active' => true],
            ['name' => 'Landis+Gyr', 'code' => 'LAG', 'description' => 'Landis+Gyr AG', 'is_active' => true],
            ['name' => 'Clou Electronics', 'code' => 'CLO', 'description' => 'Clou Electronics Co.', 'is_active' => true],
            ['name' => 'Omicron', 'code' => 'OMI', 'description' => 'Omicron Electronics', 'is_active' => true],
            ['name' => 'Ritz', 'code' => 'RIT', 'description' => 'Ritz Instrument Transformers', 'is_active' => true],
            ['name' => 'ABB', 'code' => 'ABB', 'description' => 'ABB Group', 'is_active' => true],
            ['name' => 'Extech', 'code' => 'EXT', 'description' => 'Extech Instruments', 'is_active' => true],
            ['name' => 'Haiyan Kaipu', 'code' => 'HYK', 'description' => 'Haiyan Kaipu Electric Co.', 'is_active' => true],
            ['name' => 'Zera', 'code' => 'ZER', 'description' => 'Zera GmbH', 'is_active' => true],
            ['name' => 'Shenzhen Sunshine', 'code' => 'SUN', 'description' => 'Shenzhen Sunshine Enterprise', 'is_active' => true],
            ['name' => 'Transmille', 'code' => 'TRA', 'description' => 'Transmille Ltd', 'is_active' => true],
        ];

        foreach ($manufacturers as $manufacturer) {
            EquipmentManufacturer::create($manufacturer);
        }
    }
}
