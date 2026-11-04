<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calibration_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('calibration_id')
                ->constrained('calibrations')
                ->cascadeOnDelete();

            /* ---------------------------------------------
             * Source line (optional)
             * Lets you jump from an item back to the quote
             * request line it originated from.
             * --------------------------------------------- */
            $table->foreignId('quote_request_item_id')
                ->nullable()
                ->constrained('public_quote_request_items')
                ->nullOnDelete();

            /* ---------------------------------------------
             * Equipment
             * --------------------------------------------- */
            $table->foreignId('equipment_type_id')
                ->nullable()
                ->constrained('kplc_equipment_types')
                ->nullOnDelete();

            $table->string('equipment_name')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('accuracy_class')->nullable();
            $table->string('ratings')->nullable();
            $table->string('equipment_condition')->nullable(); // good | fair | damaged

            /* ---------------------------------------------
             * Services requested for this specific meter.
             * JSON array of kplc_service_types.id values
             * (mirrors calibrations.service_types).
             * --------------------------------------------- */
            $table->json('service_types')->nullable();

            /* ---------------------------------------------
             * Per-item workflow state
             * --------------------------------------------- */
            $table->string('status')->default('pending');
            $table->string('calibration_location')->default('laboratory');
            $table->unsignedSmallInteger('position')->default(0);
            $table->text('notes')->nullable();

            /* ---------------------------------------------
             * Per-item results
             * --------------------------------------------- */
            $table->json('test_results')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['calibration_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calibration_items');
    }
};
