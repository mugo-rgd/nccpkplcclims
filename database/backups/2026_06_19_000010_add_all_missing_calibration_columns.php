<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            // Add all missing columns one by one with checks
            $columns = [
                'equipment_name' => ['type' => 'string', 'nullable' => true, 'after' => 'customer_id'],
                'serial_number' => ['type' => 'string', 'nullable' => true, 'after' => 'equipment_name'],
                'manufacturer' => ['type' => 'string', 'nullable' => true, 'after' => 'serial_number'],
                'model' => ['type' => 'string', 'nullable' => true, 'after' => 'manufacturer'],
                'accuracy_class' => ['type' => 'string', 'nullable' => true, 'after' => 'model'],
                'ratings' => ['type' => 'string', 'nullable' => true, 'after' => 'accuracy_class'],
                'equipment_type' => ['type' => 'string', 'nullable' => true, 'after' => 'ratings'],
                'equipment_condition' => ['type' => 'string', 'nullable' => true, 'after' => 'equipment_type'],
                'last_calibration_date' => ['type' => 'date', 'nullable' => true, 'after' => 'equipment_condition'],
                'calibration_location' => ['type' => 'string', 'default' => 'laboratory', 'after' => 'performed_by'],
                'priority' => ['type' => 'string', 'default' => 'normal', 'after' => 'calibration_location'],
                'notes' => ['type' => 'text', 'nullable' => true, 'after' => 'rejection_reason'],
            ];

            foreach ($columns as $name => $config) {
                if (!Schema::hasColumn('calibrations', $name)) {
                    if ($config['type'] === 'text') {
                        $table->text($name)->nullable();
                    } elseif ($config['type'] === 'date') {
                        $table->date($name)->nullable();
                    } else {
                        if ($config['nullable'] ?? false) {
                            $table->string($name)->nullable();
                        } else {
                            $table->string($name)->default($config['default'] ?? '');
                        }
                    }
                }
            }
        });
    }

    public function down()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $columns = [
                'equipment_name',
                'serial_number',
                'manufacturer',
                'model',
                'accuracy_class',
                'ratings',
                'equipment_type',
                'equipment_condition',
                'last_calibration_date',
                'calibration_location',
                'priority',
                'notes',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('calibrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
