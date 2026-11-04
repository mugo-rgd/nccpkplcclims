<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Create service types table
        Schema::create('kplc_service_types', function (Blueprint $table) {
            $table->id();
            $table->string('serv_code', 50)->unique();
            $table->string('serv_name', 191);
            $table->string('serv_method', 191);
            $table->text('notes');
            $table->text('comments')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 2. Create equipment types table
        Schema::create('kplc_equipment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->decimal('rate', 10, 2);
            $table->timestamps();
        });

        // Insert default service types
        DB::table('kplc_service_types')->insert([
            [
                'serv_code' => 'EM001',
                'serv_name' => 'Calibration/Testing of Energy Meters',
                'serv_method' => 'IEC 62052-11',
                'notes' => 'Electricity metering equipment (AC) -General requirements,tests, and test conditions, Part 11: Metering Equipment',
                'comments' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'serv_code' => 'EM002',
                'serv_name' => 'Calibration/Testing of Energy Meters',
                'serv_method' => 'IEC 62058-31',
                'notes' => 'Electricity metering equipment (AC) -Acceptance inspection -Part 31: Particular requirements for static meters for active energy (classes 0.2S, 0.5S, 1 and 2), Clause 5.4 to 5.10',
                'comments' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'serv_code' => 'CB001',
                'serv_name' => 'Testing of miniature breakers',
                'serv_method' => 'IEC 60898-2',
                'notes' => 'Circuit- breaker for overcurrent protection for house hold and similar installations',
                'comments' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'serv_code' => 'ET001',
                'serv_name' => 'Testing of low current Energy Transformers',
                'serv_method' => 'IEC 60044-1',
                'notes' => 'Instrument Transformers-Part 1: Current Transformers',
                'comments' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'serv_code' => 'ET002',
                'serv_name' => 'Testing of low current Energy Transformers',
                'serv_method' => 'IEC 61869-1&2',
                'notes' => 'Instrument transformers -Part 1: General requirements Part 2: Additional requirements for current transformers',
                'comments' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'serv_code' => 'CA001',
                'serv_name' => 'AC/DC testing and calibration',
                'serv_method' => 'ANSI/NCSL - Z 540.3 PART II',
                'notes' => 'Requirements for the calibration of measuring and Test equipment',
                'comments' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'serv_code' => 'OT001',
                'serv_name' => 'Others',
                'serv_method' => 'specify',
                'notes' => 'specify',
                'comments' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert default equipment types
        DB::table('kplc_equipment_types')->insert([
            ['name' => 'Voltmeters', 'rate' => 5000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ammeters', 'rate' => 5000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Resistance(Meggers) Meters', 'rate' => 15000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Multimeters', 'rate' => 15000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CT Analyzer', 'rate' => 20000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Current Transformer', 'rate' => 3000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Frequency Meters', 'rate' => 5000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Clamp on Meters', 'rate' => 5000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '1 Phase watt hour meter', 'rate' => 3000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '3 Phase Whole current watt hour Meter', 'rate' => 5000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '3 Phase CT&PT Connected watt hour Meter', 'rate' => 10000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ordinary low Voltage Transformer', 'rate' => 3000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Resistance boxes', 'rate' => 15000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Watt Hour Meter Programming', 'rate' => 10000.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Panel Meter', 'rate' => 10000.00, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('kplc_service_types');
        Schema::dropIfExists('kplc_equipment_types');
    }
};
