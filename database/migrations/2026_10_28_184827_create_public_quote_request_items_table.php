<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_quote_request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('public_quote_request_id')
                ->constrained('public_quote_requests')
                ->cascadeOnDelete();

            $table->foreignId('equipment_type_id')
                ->constrained('kplc_equipment_types');

            $table->foreignId('service_type_id')
                ->constrained('kplc_service_types');

            $table->unsignedSmallInteger('quantity');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_quote_request_items');
    }
};