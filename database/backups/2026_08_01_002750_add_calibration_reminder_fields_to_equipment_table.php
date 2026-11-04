<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table
                ->timestamp('last_calibration_reminder_at')
                ->nullable()
                ->after('next_calibration_date');

            $table
                ->unsignedInteger('calibration_reminder_count')
                ->default(0)
                ->after('last_calibration_reminder_at');

            $table
                ->boolean('calibration_reminder_enabled')
                ->default(true)
                ->after('calibration_reminder_count');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn([
                'last_calibration_reminder_at',
                'calibration_reminder_count',
                'calibration_reminder_enabled',
            ]);
        });
    }
};
