<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_tool_logs', function (Blueprint $table) {

            if (!Schema::hasColumn('ai_tool_logs', 'duration_ms')) {
                $table->unsignedInteger('duration_ms')
                    ->nullable()
                    ->after('successful');
            }

            if (!Schema::hasColumn('ai_tool_logs', 'result_size_bytes')) {
                $table->unsignedInteger('result_size_bytes')
                    ->nullable()
                    ->after('duration_ms');
            }

            if (!Schema::hasColumn('ai_tool_logs', 'error_message')) {
                $table->text('error_message')
                    ->nullable()
                    ->after('result_size_bytes');
            }

        });
    }

    public function down(): void
    {
        Schema::table('ai_tool_logs', function (Blueprint $table) {

            $table->dropColumn([
                'duration_ms',
                'result_size_bytes',
                'error_message',
            ]);

        });
    }
};
