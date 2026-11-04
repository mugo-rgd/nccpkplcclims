<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('job_cards', function (Blueprint $table) {
            // Customer related
            if (!Schema::hasColumn('job_cards', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('job_cards', 'customer_request_id')) {
                $table->foreignId('customer_request_id')->nullable()->after('customer_id');
            }

            if (!Schema::hasColumn('job_cards', 'calibration_id')) {
                $table->foreignId('calibration_id')->nullable()->after('customer_request_id');
            }

            // Job numbers
            if (!Schema::hasColumn('job_cards', 'job_card_number')) {
                $table->string('job_card_number')->unique()->nullable()->after('calibration_id');
            }

            if (!Schema::hasColumn('job_cards', 'job_number')) {
                $table->string('job_number')->nullable()->after('job_card_number');
            }

            // Status
            if (!Schema::hasColumn('job_cards', 'status')) {
                $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'verified', 'collected', 'cancelled'])->default('pending')->after('job_number');
            }

            // Descriptions
            if (!Schema::hasColumn('job_cards', 'description')) {
                $table->text('description')->nullable()->after('status');
            }

            if (!Schema::hasColumn('job_cards', 'work_done')) {
                $table->text('work_done')->nullable()->after('description');
            }

            if (!Schema::hasColumn('job_cards', 'remarks')) {
                $table->text('remarks')->nullable()->after('work_done');
            }

            // Assignment fields
            if (!Schema::hasColumn('job_cards', 'allocated_to')) {
                $table->foreignId('allocated_to')->nullable()->after('remarks');
            }

            if (!Schema::hasColumn('job_cards', 'allocated_by')) {
                $table->foreignId('allocated_by')->nullable()->after('allocated_to');
            }

            if (!Schema::hasColumn('job_cards', 'checked_by')) {
                $table->foreignId('checked_by')->nullable()->after('allocated_by');
            }

            if (!Schema::hasColumn('job_cards', 'collected_by')) {
                $table->foreignId('collected_by')->nullable()->after('checked_by');
            }

            // Timestamps
            if (!Schema::hasColumn('job_cards', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('collected_by');
            }

            if (!Schema::hasColumn('job_cards', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('assigned_at');
            }

            if (!Schema::hasColumn('job_cards', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }

            if (!Schema::hasColumn('job_cards', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('completed_at');
            }

            if (!Schema::hasColumn('job_cards', 'collected_at')) {
                $table->timestamp('collected_at')->nullable()->after('verified_at');
            }
        });
    }

    public function down()
    {
        Schema::table('job_cards', function (Blueprint $table) {
            $columns = [
                'customer_id',
                'customer_request_id',
                'calibration_id',
                'job_card_number',
                'job_number',
                'status',
                'description',
                'work_done',
                'remarks',
                'allocated_to',
                'allocated_by',
                'checked_by',
                'collected_by',
                'assigned_at',
                'started_at',
                'completed_at',
                'verified_at',
                'collected_at'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('job_cards', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
