<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add role column
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('customer')->after('password');
            }

            // Add is_active column
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }

            // Add staff_number if missing
            if (!Schema::hasColumn('users', 'staff_number')) {
                $table->string('staff_number')->nullable()->after('email');
            }

            // Add phone_number if missing
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 20)->nullable()->after('staff_number');
            }

            // Add signature_path if missing
            if (!Schema::hasColumn('users', 'signature_path')) {
                $table->text('signature_path')->nullable()->after('phone_number');
            }

            // Add signature_filename if missing
            if (!Schema::hasColumn('users', 'signature_filename')) {
                $table->string('signature_filename', 255)->nullable()->after('signature_path');
            }

            // Add use_auto_signature if missing
            if (!Schema::hasColumn('users', 'use_auto_signature')) {
                $table->boolean('use_auto_signature')->default(false)->after('signature_filename');
            }

            // Add signature_updated_at if missing
            if (!Schema::hasColumn('users', 'signature_updated_at')) {
                $table->timestamp('signature_updated_at')->nullable()->after('use_auto_signature');
            }

            // Add station if missing
            if (!Schema::hasColumn('users', 'station')) {
                $table->string('station')->nullable()->after('signature_updated_at');
            }

            // Add last_login_at if missing
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('station');
            }

            // Add soft deletes if missing
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes()->after('last_login_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'role', 'is_active', 'staff_number', 'phone_number',
                'signature_path', 'signature_filename', 'use_auto_signature',
                'signature_updated_at', 'station', 'last_login_at', 'deleted_at'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
