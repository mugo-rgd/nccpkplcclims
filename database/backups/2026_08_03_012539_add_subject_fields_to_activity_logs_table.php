<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_logs', 'action')) {
                $table
                    ->string('action', 100)
                    ->nullable()
                    ->after('user_id');
            }

            if (!Schema::hasColumn('activity_logs', 'description')) {
                $table
                    ->text('description')
                    ->nullable()
                    ->after('action');
            }

            if (!Schema::hasColumn('activity_logs', 'subject_type')) {
                $table
                    ->string('subject_type')
                    ->nullable()
                    ->after('description');
            }

            if (!Schema::hasColumn('activity_logs', 'subject_id')) {
                $table
                    ->unsignedBigInteger('subject_id')
                    ->nullable()
                    ->after('subject_type');
            }

            if (!Schema::hasColumn('activity_logs', 'properties')) {
                $table
                    ->json('properties')
                    ->nullable()
                    ->after('subject_id');
            }

            if (!Schema::hasColumn('activity_logs', 'ip_address')) {
                $table
                    ->string('ip_address', 45)
                    ->nullable()
                    ->after('properties');
            }

            if (!Schema::hasColumn('activity_logs', 'user_agent')) {
                $table
                    ->text('user_agent')
                    ->nullable()
                    ->after('ip_address');
            }

            $table->index([
                'subject_type',
                'subject_id',
            ]);
        });
    }

    public function down(): void
    {
        /*
         * Leave audit data intact during rollback.
         */
    }
};
