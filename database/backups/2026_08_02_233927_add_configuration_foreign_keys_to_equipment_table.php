<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add controlled Laboratory Configuration relationships.
     */
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            /*
             * These columns should remain nullable so that legacy
             * equipment records can continue to work during migration.
             */
            if (!Schema::hasColumn('equipment', 'category_id')) {
                $table
                    ->unsignedBigInteger('category_id')
                    ->nullable()
                    ->after('name');
            }

            if (!Schema::hasColumn('equipment', 'manufacturer_id')) {
                $table
                    ->unsignedBigInteger('manufacturer_id')
                    ->nullable()
                    ->after('category_id');
            }

            if (!Schema::hasColumn('equipment', 'type_id')) {
                $table
                    ->unsignedBigInteger('type_id')
                    ->nullable()
                    ->after('manufacturer_id');
            }
        });

        /*
         * Foreign keys are added in a separate schema operation because
         * the columns may already exist.
         */
        Schema::table('equipment', function (Blueprint $table) {
            $table
                ->foreign('category_id')
                ->references('id')
                ->on('equipment_categories')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table
                ->foreign('manufacturer_id')
                ->references('id')
                ->on('equipment_manufacturers')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table
                ->foreign('type_id')
                ->references('id')
                ->on('kplc_equipment_types')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Remove the configuration relationships.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['manufacturer_id']);
            $table->dropForeign(['type_id']);
        });

        /*
         * Do not automatically drop the columns in production because
         * they may already have existed before this migration.
         */
    }
};
