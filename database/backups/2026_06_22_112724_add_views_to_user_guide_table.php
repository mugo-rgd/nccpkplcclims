<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_guide', function (Blueprint $table) {
            $table->integer('views')->default(0)->after('content_html');
        });
    }

    public function down()
    {
        Schema::table('user_guide', function (Blueprint $table) {
            $table->dropColumn('views');
        });
    }
};
