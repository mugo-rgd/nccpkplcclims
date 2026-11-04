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
        // Check if table exists before creating
        if (!Schema::hasTable('reassignment_histories')) {
            Schema::create('reassignment_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('calibration_id');
                $table->unsignedBigInteger('from_technician_id');
                $table->unsignedBigInteger('to_technician_id');
                $table->unsignedBigInteger('reassigned_by');
                $table->text('reason');
                $table->timestamp('reassigned_at');
                $table->timestamps();

                $table->foreign('calibration_id')->references('id')->on('calibrations')->onDelete('cascade');
                $table->foreign('from_technician_id')->references('id')->on('users');
                $table->foreign('to_technician_id')->references('id')->on('users');
                $table->foreign('reassigned_by')->references('id')->on('users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reassignment_histories');
    }
};
