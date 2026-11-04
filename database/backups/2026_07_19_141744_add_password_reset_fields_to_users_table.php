<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_reset_requested_at')->nullable()->after('password');
            $table->string('password_reset_token')->nullable()->after('password_reset_requested_at');
            $table->timestamp('password_reset_completed_at')->nullable()->after('password_reset_token');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['password_reset_requested_at', 'password_reset_token', 'password_reset_completed_at']);
        });
    }
};
