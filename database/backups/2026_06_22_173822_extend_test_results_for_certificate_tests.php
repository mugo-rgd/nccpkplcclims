<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('test_results', function (Blueprint $table) {
            // Certificate-specific test fields
            $table->string('test_name')->nullable()->after('parameter');
            $table->string('test_type')->nullable()->after('test_name'); // accuracy, register, starting, creep
            $table->string('applied_value')->nullable()->after('test_type');
            $table->string('power_factor')->nullable()->after('applied_value');
            $table->string('voltage')->nullable()->after('power_factor');
            $table->string('frequency')->nullable()->after('voltage');

            // For uncertainty calculations
            $table->json('trial_data')->nullable()->after('error_percentage');
            $table->decimal('uncertainty', 10, 6)->nullable()->after('trial_data');

            // For register tests
            $table->decimal('start_reading', 10, 2)->nullable()->after('uncertainty');
            $table->decimal('stop_reading', 10, 2)->nullable()->after('start_reading');
            $table->decimal('energy_difference', 10, 2)->nullable()->after('stop_reading');
            $table->decimal('dial_error', 10, 4)->nullable()->after('energy_difference');

            // Test order and result
            $table->integer('test_order')->default(0)->after('dial_error');
            $table->string('result')->nullable()->after('test_order'); // PASS/FAIL

            // Indexes
            $table->index('test_type');
            $table->index('test_order');
        });
    }

    public function down()
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropColumn([
                'test_name',
                'test_type',
                'applied_value',
                'power_factor',
                'voltage',
                'frequency',
                'trial_data',
                'uncertainty',
                'start_reading',
                'stop_reading',
                'energy_difference',
                'dial_error',
                'test_order',
                'result'
            ]);
        });
    }
};
