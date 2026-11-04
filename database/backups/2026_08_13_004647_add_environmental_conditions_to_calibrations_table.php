<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calibrations', function (Blueprint $table) {

            $table->decimal('temperature_start', 5, 2)
                ->nullable()
                ->after('test_parameters');

            $table->decimal('temperature_end', 5, 2)
                ->nullable()
                ->after('temperature_start');

            $table->decimal('humidity_start', 5, 2)
                ->nullable()
                ->after('temperature_end');

            $table->decimal('humidity_end', 5, 2)
                ->nullable()
                ->after('humidity_start');

            $table->decimal('supply_voltage', 8, 2)
                ->nullable()
                ->after('humidity_end');

            $table->decimal('supply_frequency', 6, 2)
                ->nullable()
                ->after('supply_voltage');

            $table->foreignId('test_parameters_recorded_by')
                ->nullable()
                ->after('supply_frequency')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('test_parameters_recorded_at')
                ->nullable()
                ->after('test_parameters_recorded_by');
        });
    }

    public function down(): void
    {
        Schema::table('calibrations', function (Blueprint $table) {

            $table->dropForeign([
                'test_parameters_recorded_by'
            ]);

            $table->dropColumn([
                'temperature_start',
                'temperature_end',
                'humidity_start',
                'humidity_end',
                'supply_voltage',
                'supply_frequency',
                'test_parameters_recorded_by',
                'test_parameters_recorded_at',
            ]);
        });
    }
};