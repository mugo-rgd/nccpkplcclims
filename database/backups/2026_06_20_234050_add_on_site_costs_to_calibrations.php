<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            // On-site costs
            if (!Schema::hasColumn('calibrations', 'per_diem_cost')) {
                $table->decimal('per_diem_cost', 15, 2)->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('calibrations', 'transport_cost')) {
                $table->decimal('transport_cost', 15, 2)->nullable()->after('per_diem_cost');
            }
            if (!Schema::hasColumn('calibrations', 'other_costs')) {
                $table->decimal('other_costs', 15, 2)->nullable()->after('transport_cost');
            }
            if (!Schema::hasColumn('calibrations', 'other_costs_description')) {
                $table->text('other_costs_description')->nullable()->after('other_costs');
            }
            if (!Schema::hasColumn('calibrations', 'number_of_staff')) {
                $table->integer('number_of_staff')->default(1)->after('other_costs_description');
            }
            if (!Schema::hasColumn('calibrations', 'travel_days')) {
                $table->integer('travel_days')->default(1)->after('number_of_staff');
            }
            if (!Schema::hasColumn('calibrations', 'site_distance_km')) {
                $table->integer('site_distance_km')->nullable()->after('travel_days');
            }
        });
    }

    public function down()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->dropColumn([
                'per_diem_cost',
                'transport_cost',
                'other_costs',
                'other_costs_description',
                'number_of_staff',
                'travel_days',
                'site_distance_km',
            ]);
        });
    }
};
