<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reassignment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_technician_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('to_technician_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reassigned_by')->constrained('users')->onDelete('cascade');
            $table->text('reason')->nullable();
            $table->timestamp('reassigned_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reassignment_histories');
    }
};
