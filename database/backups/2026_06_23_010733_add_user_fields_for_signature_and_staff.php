<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'staff_number')) {
                $table->string('staff_number', 50)->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'designation')) {
                $table->string('designation', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'signature_path')) {
                $table->string('signature_path', 255)->nullable()->after('designation');
            }
            if (!Schema::hasColumn('users', 'use_auto_signature')) {
                $table->boolean('use_auto_signature')->default(true)->after('signature_path');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['staff_number', 'designation', 'signature_path', 'use_auto_signature']);
        });
    }
};
