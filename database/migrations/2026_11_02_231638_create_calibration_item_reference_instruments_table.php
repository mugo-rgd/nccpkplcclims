<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calibration_item_reference_instruments', function (Blueprint $table) {
            $table->id();

            // FK 1 — needs a short name (auto-generated would be 66 chars)
            $table->foreignId('calibration_item_id')
                ->constrained('calibration_items', indexName: 'ciri_cal_item_fk')
                ->cascadeOnDelete();

            // FK 2 — already fits at 58 chars, but renamed for consistency
            $table->foreignId('equipment_id')
                ->constrained('equipment', indexName: 'ciri_equipment_fk')
                ->restrictOnDelete();

            $table->string('certificate_number')->nullable();
            $table->date('calibration_date')->nullable();
            $table->date('calibration_due_date')->nullable();
            $table->string('accuracy_class')->nullable();
            $table->string('calibration_laboratory')->nullable();

            $table->timestamps();

            $table->unique(
                ['calibration_item_id', 'equipment_id'],
                'cal_item_reference_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calibration_item_reference_instruments');
    }
};
