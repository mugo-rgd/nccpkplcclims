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
        // Modify the status column to include 'checked'
        DB::statement("ALTER TABLE certificates MODIFY COLUMN status ENUM('draft', 'pending', 'generated', 'checked', 'approved', 'rejected', 'issued') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE certificates MODIFY COLUMN status ENUM('draft', 'pending', 'generated', 'approved', 'rejected', 'issued') DEFAULT 'draft'");
    }
};
