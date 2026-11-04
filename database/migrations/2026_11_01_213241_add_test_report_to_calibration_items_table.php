<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calibration_items', function (Blueprint $table) {
            $table->string('test_report_path')->nullable();
            $table->string('test_report_original_name')->nullable();
            $table->foreignId('test_report_uploaded_by')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('test_report_uploaded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('calibration_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('test_report_uploaded_by');
            $table->dropColumn([
                'test_report_path',
                'test_report_original_name',
                'test_report_uploaded_at',
            ]);
        });
    }
};