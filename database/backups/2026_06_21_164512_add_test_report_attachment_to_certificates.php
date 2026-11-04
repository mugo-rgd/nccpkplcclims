<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Test report attachment (PDF from machine)
            if (!Schema::hasColumn('certificates', 'test_report_attachment_path')) {
                $table->string('test_report_attachment_path')->nullable()->after('pdf_path');
            }
            if (!Schema::hasColumn('certificates', 'test_report_attachment_filename')) {
                $table->string('test_report_attachment_filename')->nullable()->after('test_report_attachment_path');
            }
            if (!Schema::hasColumn('certificates', 'test_report_original_name')) {
                $table->string('test_report_original_name')->nullable()->after('test_report_attachment_filename');
            }
            // Machine details that generated the report
            if (!Schema::hasColumn('certificates', 'machine_used')) {
                $table->string('machine_used')->nullable()->after('test_report_original_name');
            }
            if (!Schema::hasColumn('certificates', 'machine_serial')) {
                $table->string('machine_serial')->nullable()->after('machine_used');
            }
            if (!Schema::hasColumn('certificates', 'test_engineer')) {
                $table->string('test_engineer')->nullable()->after('machine_serial');
            }
            if (!Schema::hasColumn('certificates', 'test_date')) {
                $table->timestamp('test_date')->nullable()->after('test_engineer');
            }
        });
    }

    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn([
                'test_report_attachment_path',
                'test_report_attachment_filename',
                'test_report_original_name',
                'machine_used',
                'machine_serial',
                'test_engineer',
                'test_date',
            ]);
        });
    }
};
