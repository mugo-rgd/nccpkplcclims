<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_quote_request_items', function (Blueprint $table) {
            $table->string('equipment_name')->nullable()->after('service_type_id');
            $table->string('serial_number')->nullable()->after('equipment_name');
            $table->string('manufacturer')->nullable()->after('serial_number');
            $table->string('model')->nullable()->after('manufacturer');
            $table->string('rating')->nullable()->after('model');
        });
    }

    public function down(): void
    {
        Schema::table('public_quote_request_items', function (Blueprint $table) {
            $table->dropColumn([
                'equipment_name',
                'serial_number',
                'manufacturer',
                'model',
                'rating',
            ]);
        });
    }
};
