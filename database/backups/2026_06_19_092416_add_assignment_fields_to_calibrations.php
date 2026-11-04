<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            if (!Schema::hasColumn('calibrations', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('performed_by');
            }
            if (!Schema::hasColumn('calibrations', 'assignment_notes')) {
                $table->text('assignment_notes')->nullable()->after('assigned_at');
            }
        });
    }

    public function down()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->dropColumn(['assigned_at', 'assignment_notes']);
        });
    }
};
