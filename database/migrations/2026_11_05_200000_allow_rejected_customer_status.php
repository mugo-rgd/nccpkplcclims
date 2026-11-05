<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE customers MODIFY COLUMN status ENUM('active','pending','suspended','rejected') NOT NULL DEFAULT 'pending'");
    }
    public function down(): void {
        if (DB::table('customers')->where('status', 'rejected')->exists()) {
            throw new \RuntimeException('Cannot roll back while rejected customers exist.');
        }
        DB::statement("ALTER TABLE customers MODIFY COLUMN status ENUM('active','pending','suspended') NOT NULL DEFAULT 'pending'");
    }
};
