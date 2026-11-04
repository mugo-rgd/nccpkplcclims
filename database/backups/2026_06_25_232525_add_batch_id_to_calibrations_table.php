<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            if (!Schema::hasColumn('calibrations', 'batch_id')) {
                $table->string('batch_id')->nullable()->after('id');
                $table->index('batch_id');
            }
            if (!Schema::hasColumn('calibrations', 'batch_number')) {
                $table->integer('batch_number')->nullable()->after('batch_id');
            }
        });
    }

    public function down()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            if (Schema::hasColumn('calibrations', 'batch_id')) {
                $table->dropColumn('batch_id');
            }
            if (Schema::hasColumn('calibrations', 'batch_number')) {
                $table->dropColumn('batch_number');
            }
        });
    }
};
