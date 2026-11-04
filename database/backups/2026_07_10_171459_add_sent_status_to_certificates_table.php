<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, check if we need to add any missing columns
        Schema::table('certificates', function (Blueprint $table) {
            // Add sent_by if it doesn't exist
            if (!Schema::hasColumn('certificates', 'sent_by')) {
                $table->unsignedBigInteger('sent_by')->nullable()->after('approved_at');
                $table->foreign('sent_by')->references('id')->on('users')->onDelete('set null');
            }

            // Add sent_at if it doesn't exist
            if (!Schema::hasColumn('certificates', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('sent_by');
            }
        });

        // Update the ENUM to include all statuses
        DB::statement("ALTER TABLE certificates MODIFY COLUMN status ENUM('draft', 'pending', 'generated', 'checked', 'approved', 'rejected', 'sent', 'issued') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            if (Schema::hasColumn('certificates', 'sent_by')) {
                $table->dropForeign(['sent_by']);
                $table->dropColumn(['sent_by', 'sent_at']);
            }
        });

        // Revert ENUM
        DB::statement("ALTER TABLE certificates MODIFY COLUMN status ENUM('draft', 'pending', 'generated', 'checked', 'approved', 'rejected', 'issued') DEFAULT 'draft'");
    }
};
