<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('calibrations', function (Blueprint $table) {
        $table->string('delivery_number')->nullable()->after('previous_technician_id');
        $table->string('collection_number')->nullable()->after('delivery_number');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calibrations', function (Blueprint $table) {
            //
        });
    }
};
