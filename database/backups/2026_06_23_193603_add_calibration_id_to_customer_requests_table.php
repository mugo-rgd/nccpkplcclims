<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('customer_requests')) {
            Schema::table('customer_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('customer_requests', 'calibration_id')) {
                    $table->foreignId('calibration_id')->nullable()->after('customer_id');
                }
                if (!Schema::hasColumn('customer_requests', 'job_card_id')) {
                    $table->foreignId('job_card_id')->nullable()->after('calibration_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('customer_requests')) {
            Schema::table('customer_requests', function (Blueprint $table) {
                if (Schema::hasColumn('customer_requests', 'calibration_id')) {
                    $table->dropColumn('calibration_id');
                }
                if (Schema::hasColumn('customer_requests', 'job_card_id')) {
                    $table->dropColumn('job_card_id');
                }
            });
        }
    }
};
