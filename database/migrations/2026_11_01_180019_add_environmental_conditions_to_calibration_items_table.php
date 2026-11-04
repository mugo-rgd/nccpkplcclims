<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calibration_items', function (Blueprint $table) {
            $table->json('test_parameters')->nullable();

            $table->decimal('temperature_start', 5, 2)->nullable();
            $table->decimal('temperature_end', 5, 2)->nullable();

            $table->decimal('humidity_start', 5, 2)->nullable();
            $table->decimal('humidity_end', 5, 2)->nullable();

            $table->decimal('supply_voltage', 8, 2)->nullable();
            $table->decimal('supply_frequency', 6, 2)->nullable();

            $table->unsignedBigInteger(
                'test_parameters_recorded_by'
            )->nullable();

            $table->timestamp(
                'test_parameters_recorded_at'
            )->nullable();

            $table->foreign('test_parameters_recorded_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('calibration_items', function (Blueprint $table) {
            $table->dropForeign([
                'test_parameters_recorded_by'
            ]);

            $table->dropColumn([
                'test_parameters',
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
