<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if job_cards table exists
        if (Schema::hasTable('job_cards')) {
            Schema::table('job_cards', function (Blueprint $table) {
                // Add calibration_id (missing from original migration)
                if (!Schema::hasColumn('job_cards', 'calibration_id')) {
                    $table->foreignId('calibration_id')->nullable()->after('customer_request_id');
                }

                // Add job_number for backward compatibility
                if (!Schema::hasColumn('job_cards', 'job_number')) {
                    $table->string('job_number')->nullable()->after('job_card_number');
                }

                // Add assigned_at (matching allocated_at but more standard naming)
                if (!Schema::hasColumn('job_cards', 'assigned_at')) {
                    $table->timestamp('assigned_at')->nullable()->after('allocated_at');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('job_cards')) {
            Schema::table('job_cards', function (Blueprint $table) {
                if (Schema::hasColumn('job_cards', 'calibration_id')) {
                    $table->dropColumn('calibration_id');
                }
                if (Schema::hasColumn('job_cards', 'job_number')) {
                    $table->dropColumn('job_number');
                }
                if (Schema::hasColumn('job_cards', 'assigned_at')) {
                    $table->dropColumn('assigned_at');
                }
            });
        }
    }
};
