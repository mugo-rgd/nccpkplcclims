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
        Schema::table('certificates', function (Blueprint $table) {
            // Add checked_by column if it doesn't exist
            if (!Schema::hasColumn('certificates', 'checked_by')) {
                $table->unsignedBigInteger('checked_by')->nullable()->after('generated_by');
                $table->foreign('checked_by')->references('id')->on('users')->onDelete('set null');
            }

            // Add checked_at column if it doesn't exist
            if (!Schema::hasColumn('certificates', 'checked_at')) {
                $table->timestamp('checked_at')->nullable()->after('checked_by');
            }

            // Add approved_by column if it doesn't exist
            if (!Schema::hasColumn('certificates', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('checked_at');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            }

            // Add approved_at column if it doesn't exist
            if (!Schema::hasColumn('certificates', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            // Add issued_by column if it doesn't exist
            if (!Schema::hasColumn('certificates', 'issued_by')) {
                $table->unsignedBigInteger('issued_by')->nullable()->after('approved_at');
                $table->foreign('issued_by')->references('id')->on('users')->onDelete('set null');
            }

            // Add issued_date column if it doesn't exist
            if (!Schema::hasColumn('certificates', 'issued_date')) {
                $table->timestamp('issued_date')->nullable()->after('issued_by');
            }

            // Add expiry_date column if it doesn't exist
            if (!Schema::hasColumn('certificates', 'expiry_date')) {
                $table->timestamp('expiry_date')->nullable()->after('issued_date');
            }

            // Add is_signed column if it doesn't exist
            if (!Schema::hasColumn('certificates', 'is_signed')) {
                $table->boolean('is_signed')->default(false)->after('expiry_date');
            }

            // Add signed_by column if it doesn't exist
            if (!Schema::hasColumn('certificates', 'signed_by')) {
                $table->string('signed_by')->nullable()->after('is_signed');
            }

            // Add signed_at column if it doesn't exist
            if (!Schema::hasColumn('certificates', 'signed_at')) {
                $table->timestamp('signed_at')->nullable()->after('signed_by');
            }
        });

        // Update the ENUM to include 'checked' and any other missing statuses
        // First check what the current ENUM values are
        $result = DB::select("SHOW COLUMNS FROM certificates WHERE Field = 'status'");
        if (!empty($result)) {
            $enumStr = $result[0]->Type;
            // Check if 'checked' is already in the ENUM
            if (strpos($enumStr, "'checked'") === false) {
                DB::statement("ALTER TABLE certificates MODIFY COLUMN status ENUM('draft', 'pending', 'generated', 'checked', 'approved', 'rejected', 'sent', 'issued') DEFAULT 'draft'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('certificates', 'checked_by')) {
                $table->dropForeign(['checked_by']);
            }
            if (Schema::hasColumn('certificates', 'approved_by')) {
                $table->dropForeign(['approved_by']);
            }
            if (Schema::hasColumn('certificates', 'issued_by')) {
                $table->dropForeign(['issued_by']);
            }

            // Drop columns
            $columns = ['checked_by', 'checked_at', 'approved_by', 'approved_at',
                       'issued_by', 'issued_date', 'expiry_date', 'is_signed',
                       'signed_by', 'signed_at'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('certificates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Revert ENUM to original
        DB::statement("ALTER TABLE certificates MODIFY COLUMN status ENUM('draft', 'pending', 'generated', 'approved', 'rejected', 'issued') DEFAULT 'draft'");
    }
};
