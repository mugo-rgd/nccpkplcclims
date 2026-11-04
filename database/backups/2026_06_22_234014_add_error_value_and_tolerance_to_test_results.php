<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->decimal('error_value', 10, 4)->nullable()->after('uncertainty');
            $table->decimal('tolerance', 5, 2)->default(0.50)->after('error_value');
        });
    }

    public function down()
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropColumn(['error_value', 'tolerance']);
        });
    }
};
