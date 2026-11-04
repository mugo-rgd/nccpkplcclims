<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('serial_number')->unique();
            $table->string('manufacturer');
            $table->string('model');
            $table->string('type');
            $table->string('accuracy_class')->nullable();
            $table->string('status')->default('active');
            $table->date('manufacture_date')->nullable();
            $table->date('last_calibration_date')->nullable();
            $table->date('next_calibration_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('serial_number');
            $table->index(['customer_id', 'status']);
            $table->index('next_calibration_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};