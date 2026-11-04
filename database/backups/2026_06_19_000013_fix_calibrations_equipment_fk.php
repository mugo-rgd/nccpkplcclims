<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            // Drop existing foreign key if it exists
            try {
                $table->dropForeign(['equipment_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist - ignore error
            }
        });

        // Make equipment_id nullable
        Schema::table('calibrations', function (Blueprint $table) {
            $table->unsignedBigInteger('equipment_id')->nullable()->change();
        });

        // Add new foreign key referencing equipment table
        Schema::table('calibrations', function (Blueprint $table) {
            $table->foreign('equipment_id')
                  ->references('id')
                  ->on('equipment')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            try {
                $table->dropForeign(['equipment_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }
        });

        Schema::table('calibrations', function (Blueprint $table) {
            $table->unsignedBigInteger('equipment_id')->nullable(false)->change();
        });

        Schema::table('calibrations', function (Blueprint $table) {
            $table->foreign('equipment_id')
                  ->references('id')
                  ->on('equipments')
                  ->onDelete('cascade');
        });
    }
};
