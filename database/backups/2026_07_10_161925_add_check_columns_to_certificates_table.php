<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Add checked columns
            $table->unsignedBigInteger('checked_by')->nullable()->after('generated_by');
            $table->timestamp('checked_at')->nullable()->after('checked_by');

            // Add foreign key constraint for checked_by
            $table->foreign('checked_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['checked_by']);
            $table->dropColumn(['checked_by', 'checked_at']);
        });
    }
};
