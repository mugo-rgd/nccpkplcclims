<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // FIRST: Check if columns exist before modifying
        Schema::table('users', function (Blueprint $table) {
            // Fix phone number - change from INT to VARCHAR with proper length
            if (Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 20)->nullable()->change();
            }

            // Add soft deletes if not exists
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }

            // Add new columns if not exists
            if (!Schema::hasColumn('users', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'session_id')) {
                $table->text('session_id')->nullable();
            }

            if (!Schema::hasColumn('users', 'legacy_role')) {
                $table->string('legacy_role')->nullable()->after('role');
            }
        });

        // Add indexes for performance
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'is_active'], 'idx_users_role_active');
            $table->index(['status', 'created_at'], 'idx_users_status_created');
            $table->index('last_login_at', 'idx_users_last_login');
            $table->index('staff_number', 'idx_users_staff_number');
        });

        // Fix customers table
        Schema::table('customers', function (Blueprint $table) {
            // Change registration_date to timestamp if it exists and is date
            if (Schema::hasColumn('customers', 'registration_date')) {
                $table->timestamp('registration_date')->nullable()->change();
            }

            // Add soft deletes
            if (!Schema::hasColumn('customers', 'deleted_at')) {
                $table->softDeletes();
            }

            // Add indexes
            $table->index(['status', 'is_active'], 'idx_customers_status_active');
            $table->index('company_name', 'idx_customers_company_name');
            $table->index('created_at', 'idx_customers_created_at');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_activity_at', 'session_id', 'legacy_role', 'deleted_at']);
            $table->dropIndex('idx_users_role_active');
            $table->dropIndex('idx_users_status_created');
            $table->dropIndex('idx_users_last_login');
            $table->dropIndex('idx_users_staff_number');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
            $table->dropIndex('idx_customers_status_active');
            $table->dropIndex('idx_customers_company_name');
            $table->dropIndex('idx_customers_created_at');
        });
    }
};
