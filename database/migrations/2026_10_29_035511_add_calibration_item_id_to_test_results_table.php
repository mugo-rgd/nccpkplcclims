<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->foreignId('calibration_item_id')
                ->nullable()
                ->after('calibration_id')
                ->constrained('calibration_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropForeign(['calibration_item_id']);
            $table->dropColumn('calibration_item_id');
        });
    }
};
