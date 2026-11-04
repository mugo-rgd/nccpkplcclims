<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->timestamp('reassigned_at')->nullable();
            $table->foreignId('reassigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('previous_technician_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->dropForeign(['reassigned_by']);
            $table->dropForeign(['previous_technician_id']);
            $table->dropColumn(['reassigned_at', 'reassigned_by', 'previous_technician_id']);
        });
    }
};
