<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calibrations', function (Blueprint $table) {
            // Customer Verification fields
            $table->text('verification_of_item')->nullable()->after('notes');
            $table->text('review_of_service')->nullable()->after('verification_of_item');
            $table->enum('validity_period_required', ['yes', 'no'])->nullable()->after('review_of_service');
            $table->string('validity_period')->nullable()->after('validity_period_required');
            $table->date('validity_period_date')->nullable()->after('validity_period');
        });
    }

    public function down(): void
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->dropColumn([
                'verification_of_item',
                'review_of_service',
                'validity_period_required',
                'validity_period',
                'validity_period_date',
            ]);
        });
    }
};
