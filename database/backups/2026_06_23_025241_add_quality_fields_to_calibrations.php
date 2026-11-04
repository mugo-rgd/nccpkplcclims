<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->boolean('quality_checked')->default(false);
            $table->timestamp('quality_checked_at')->nullable();
            $table->foreignId('quality_checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('quality_approved')->default(false);
            $table->boolean('quality_rejected')->default(false);
            $table->text('quality_notes')->nullable();
            $table->json('quality_checklist')->nullable();
        });
    }

    public function down()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->dropColumn([
                'quality_checked',
                'quality_checked_at',
                'quality_checked_by',
                'quality_approved',
                'quality_rejected',
                'quality_notes',
                'quality_checklist'
            ]);
        });
    }
};
