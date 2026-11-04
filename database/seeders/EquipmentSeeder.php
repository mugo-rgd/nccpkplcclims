<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentManufacturer;
use Carbon\Carbon;

class EquipmentSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🔄 Seeding equipment inventory...');

        $equipmentData = [
            [
                'name' => 'Multifunctional Calibrator',
                'model' => '5522A-PQ/6 240',
                'serial_number' => '2932903',
                'manufacturer' => 'Fluke',
                'calibration_laboratory' => 'KEBS AC/DC LAB',
                'remarks' => 'Training required on advanced calibration measurements',
                'category' => 'Calibration Equipment',
            ],
            [
                'name' => 'AC/DC Calibration bench',
                'model' => 'Times Electronics UK',
                'serial_number' => '1091B15',
                'manufacturer' => 'Times Electronics',
                'calibration_laboratory' => 'KEBS AC/DC LAB',
                'remarks' => '',
                'category' => 'Calibration Equipment',
            ],
            [
                'name' => 'Electrical Test Calibrator',
                'model' => '3200B',
                'serial_number' => 'M1470A18',
                'manufacturer' => 'Transmille',
                'calibration_laboratory' => 'KEBS AC/DC LAB',
                'remarks' => '',
                'category' => 'Calibration Equipment',
            ],
            [
                'name' => 'HV Insulation Tester Adapter',
                'model' => 'EXTHV',
                'serial_number' => '112262A18',
                'manufacturer' => 'Transmille',
                'calibration_laboratory' => 'KEBS AC/DC LAB',
                'remarks' => '',
                'category' => 'Safety Equipment',
            ],
            [
                'name' => 'Advanced Multimeter',
                'model' => 'Transmille',
                'serial_number' => 'N2057G19',
                'manufacturer' => 'Transmille',
                'calibration_laboratory' => 'KEBS AC/DC LAB',
                'remarks' => '',
                'category' => 'Measurement Equipment',
            ],
            [
                'name' => 'Electrical safety analyzer',
                'model' => 'EEC SE7451',
                'serial_number' => '1718978',
                'manufacturer' => 'EEC',
                'calibration_laboratory' => 'KEBS AC/DC LAB',
                'remarks' => '',
                'category' => 'Safety Equipment',
            ],
            [
                'name' => 'Portable Reference Standard',
                'model' => 'PRS 200.3',
                'serial_number' => '28246',
                'manufacturer' => 'Zera',
                'calibration_laboratory' => 'KEBS ENERGY LAB',
                'remarks' => '',
                'category' => 'Reference Standards',
            ],
            [
                'name' => 'Portable Reference Standard',
                'model' => 'PRS 200.3',
                'serial_number' => '28247',
                'manufacturer' => 'Zera',
                'calibration_laboratory' => 'KEBS ENERGY LAB',
                'remarks' => '',
                'category' => 'Reference Standards',
            ],
            [
                'name' => '3 Phase reference meter',
                'model' => 'MICROSTAR',
                'serial_number' => '40013032365',
                'manufacturer' => 'MICROSTAR',
                'calibration_laboratory' => 'KEBS ENERGY LAB',
                'remarks' => 'NONE',
                'category' => 'Reference Standards',
            ],
            [
                'name' => '1 Phase reference meter',
                'model' => 'HEXING',
                'serial_number' => '62132219',
                'manufacturer' => 'HEXING',
                'calibration_laboratory' => 'KEBS ENERGY LAB',
                'remarks' => '',
                'category' => 'Reference Standards',
            ],
            [
                'name' => 'Precision Current Transformer',
                'model' => 'KSO 1811',
                'serial_number' => '08/50215230',
                'manufacturer' => 'Ritz',
                'calibration_laboratory' => 'KEBS ENERGY LAB',
                'remarks' => 'Training required on calibration and license for VT for CPC 100',
                'category' => 'Test Equipment',
            ],
            [
                'name' => 'CT Analyzer',
                'model' => 'Omicron',
                'serial_number' => 'EL038Z',
                'manufacturer' => 'Omicron',
                'calibration_laboratory' => 'KEBS ENERGY LAB',
                'remarks' => '',
                'category' => 'Test Equipment',
            ],
            [
                'name' => 'CPC 100',
                'model' => 'Omicron',
                'serial_number' => 'DF431E',
                'manufacturer' => 'Omicron',
                'calibration_laboratory' => 'KEBS ENERGY LAB',
                'remarks' => '',
                'category' => 'Test Equipment',
            ],
            [
                'name' => 'CPC 100',
                'model' => 'Omicron',
                'serial_number' => 'WB506F',
                'manufacturer' => 'Omicron',
                'calibration_laboratory' => 'KEBS ENERGY LAB',
                'remarks' => '',
                'category' => 'Test Equipment',
            ],
            [
                'name' => 'Temp./Humidity Datalogger',
                'model' => 'Extech RHT20',
                'serial_number' => '10103559',
                'manufacturer' => 'Extech',
                'calibration_laboratory' => 'KEBS TEMP LAB',
                'remarks' => 'MORE DATA LOGGERS TO BE PROCURED',
                'category' => 'Environmental',
            ],
            [
                'name' => 'Temp./Humidity Datalogger',
                'model' => 'Extech RHT20',
                'serial_number' => '10103554',
                'manufacturer' => 'Extech',
                'calibration_laboratory' => 'KEBS TEMP LAB',
                'remarks' => '',
                'category' => 'Environmental',
            ],
            [
                'name' => 'Digital Multimeter',
                'model' => 'KYORITSU',
                'serial_number' => '8166786',
                'manufacturer' => 'KYORITSU',
                'calibration_laboratory' => 'MCL',
                'remarks' => '2 PC DIGITAL METERS REQUIRED',
                'category' => 'Measurement Equipment',
            ],
            [
                'name' => 'Single Phase Meter Test Bench',
                'model' => 'clou electronics',
                'serial_number' => '20181127',
                'manufacturer' => 'clou electronics',
                'calibration_laboratory' => 'MCL',
                'remarks' => '2 PC BENCHES REQUIRED',
                'category' => 'Test Equipment',
            ],
            [
                'name' => 'Single Phase Meter Test Bench',
                'model' => 'clou electronics',
                'serial_number' => '20181561',
                'manufacturer' => 'clou electronics',
                'calibration_laboratory' => 'MCL',
                'remarks' => '',
                'category' => 'Test Equipment',
            ],
            [
                'name' => '3 Phase meter test bench',
                'model' => 'Haiyan kaipu',
                'serial_number' => 'C221101',
                'manufacturer' => 'Haiyan kaipu',
                'calibration_laboratory' => 'MCL',
                'remarks' => '1 PC BENCH REQUIRED',
                'category' => 'Test Equipment',
            ],
            [
                'name' => '3 Phase meter test bench',
                'model' => 'Haiyan kaipu',
                'serial_number' => 'C221107',
                'manufacturer' => 'Haiyan kaipu',
                'calibration_laboratory' => 'MCL',
                'remarks' => '',
                'category' => 'Test Equipment',
            ],
            [
                'name' => 'Portable working standard',
                'model' => 'Zera P.R.S MT320',
                'serial_number' => '050040081',
                'manufacturer' => 'Zera',
                'calibration_laboratory' => 'MCL',
                'remarks' => '6 PCS TO BE PROCURED',
                'category' => 'Reference Standards',
            ],
            [
                'name' => 'Portable working standard',
                'model' => 'Zera Zera MT320',
                'serial_number' => '50040082',
                'manufacturer' => 'Zera',
                'calibration_laboratory' => 'MCL',
                'remarks' => '',
                'category' => 'Reference Standards',
            ],
            [
                'name' => 'Portable working standard',
                'model' => 'ZeraMT320',
                'serial_number' => '50040083',
                'manufacturer' => 'Zera',
                'calibration_laboratory' => 'MCL',
                'remarks' => '',
                'category' => 'Reference Standards',
            ],
            [
                'name' => 'Portable working standard',
                'model' => 'Clou P.R.S RS350',
                'serial_number' => '13502018037',
                'manufacturer' => 'clou electronics',
                'calibration_laboratory' => 'MCL',
                'remarks' => '',
                'category' => 'Reference Standards',
            ],
            [
                'name' => 'Portable working standard',
                'model' => 'Clou P.R.S RS350',
                'serial_number' => '13502018046',
                'manufacturer' => 'clou electronics',
                'calibration_laboratory' => 'MCL',
                'remarks' => '',
                'category' => 'Reference Standards',
            ],
            [
                'name' => 'IP Protection test chamber',
                'model' => 'SHENZEN SUNSHINE',
                'serial_number' => 'SUN20180808',
                'manufacturer' => 'SHENZEN SUNSHINE',
                'calibration_laboratory' => 'MCL',
                'remarks' => '',
                'category' => 'Test Equipment',
            ],
            [
                'name' => 'Glow wire test apparatus',
                'model' => 'TI-14',
                'serial_number' => '66/12',
                'manufacturer' => 'SHENZEN SUNSHINE',
                'calibration_laboratory' => 'MCL',
                'remarks' => '',
                'category' => 'Test Equipment',
            ],
        ];

        foreach ($equipmentData as $data) {
            // Find or create category
            $category = EquipmentCategory::where('name', $data['category'])->first();
            if (!$category) {
                $category = EquipmentCategory::create([
                    'name' => $data['category'],
                    'code' => strtoupper(substr($data['category'], 0, 3)),
                    'is_active' => true,
                ]);
                $this->command->info("Created category: {$data['category']}");
            }

            // Find or create manufacturer
            $manufacturer = EquipmentManufacturer::where('name', $data['manufacturer'])->first();
            if (!$manufacturer) {
                $manufacturer = EquipmentManufacturer::create([
                    'name' => $data['manufacturer'],
                    'code' => strtoupper(substr($data['manufacturer'], 0, 3)),
                    'is_active' => true,
                ]);
                $this->command->info("Created manufacturer: {$data['manufacturer']}");
            }

            // Extract remarks
            $remarks = $data['remarks'] ?? '';
            $trainingRequired = null;
            $procurementNotes = null;

            if (stripos($remarks, 'training') !== false) {
                $trainingRequired = $remarks;
            }
            if (stripos($remarks, 'procured') !== false ||
                stripos($remarks, 'required') !== false ||
                stripos($remarks, 'PCS') !== false) {
                $procurementNotes = $remarks;
            }

            // Create equipment
            $equipment = Equipment::create([
                'equipment_number' => 'EQP-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'name' => $data['name'],
                'category_id' => $category->id,
                'manufacturer_id' => $manufacturer->id,
                'model' => $data['model'],
                'serial_number' => $data['serial_number'],
                'calibration_laboratory' => $data['calibration_laboratory'],
                'remarks' => $remarks,
                'training_required' => $trainingRequired,
                'procurement_notes' => $procurementNotes,
                'status' => 'active',
                'condition' => 'good',
                'created_by' => 1,
            ]);

            $this->command->info("Added equipment: {$data['name']}");
        }

        $this->command->info('✅ Equipment inventory seeded successfully!');
        $this->command->info('Total equipment: ' . Equipment::count());
    }
}
