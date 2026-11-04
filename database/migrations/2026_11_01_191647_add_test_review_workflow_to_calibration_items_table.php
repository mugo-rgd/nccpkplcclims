<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calibration_items', function (Blueprint $table) {
            $table->string('review_status', 30)
                ->default('testing')
                ->index();

            $table->timestamp('testing_completed_at')->nullable();
            $table->foreignId('testing_completed_by')
                ->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')
                ->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')
                ->nullable()->constrained('users')->nullOnDelete();

            $table->text('review_comments')->nullable();
            $table->text('approval_comments')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('calibration_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('testing_completed_by');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropConstrainedForeignId('approved_by');

            $table->dropColumn([
                'review_status',
                'testing_completed_at',
                'reviewed_at',
                'approved_at',
                'review_comments',
                'approval_comments',
            ]);
        });
    }
};