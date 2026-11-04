<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('certificates', 'calibration_item_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->foreignId('calibration_item_id')
                    ->nullable()
                    ->after('calibration_id')
                    ->constrained('calibration_items')
                    ->restrictOnDelete();

                $table->index(
                    'calibration_item_id',
                    'certificates_calibration_item_index'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('certificates', 'calibration_item_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropIndex('certificates_calibration_item_index');
                $table->dropConstrainedForeignId('calibration_item_id');
            });
        }
    }
};