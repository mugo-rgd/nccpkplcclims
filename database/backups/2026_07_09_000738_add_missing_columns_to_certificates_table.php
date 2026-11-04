<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Add missing columns
            $table->string('equipment_name')->nullable()->after('reference_number');
            $table->string('serial_number')->nullable()->after('equipment_name');
            $table->string('manufacturer')->nullable()->after('serial_number');
            $table->string('model')->nullable()->after('manufacturer');
            $table->string('equipment_type')->nullable()->after('model');
            $table->string('accuracy_class')->nullable()->after('equipment_type');
            $table->string('ratings')->nullable()->after('accuracy_class');
            $table->json('test_results')->nullable()->after('wiring');
            $table->string('test_result')->nullable()->after('test_results');

            // Also add status if missing
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'issued'])->default('draft')->after('comments');
        });
    }

    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn([
                'equipment_name',
                'serial_number',
                'manufacturer',
                'model',
                'equipment_type',
                'accuracy_class',
                'ratings',
                'test_results',
                'test_result',
                'status'
            ]);
        });
    }
};
