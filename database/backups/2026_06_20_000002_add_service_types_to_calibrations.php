<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            if (!Schema::hasColumn('calibrations', 'service_types')) {
                $table->json('service_types')->nullable()->after('priority');
            }

            if (!Schema::hasColumn('calibrations', 'equipment_type_id')) {
                $table->foreignId('equipment_type_id')
                    ->nullable()
                    ->after('equipment_type')
                    ->constrained('kplc_equipment_types')
                    ->onDelete('set null');
            }

            // Quote & Payment fields
            if (!Schema::hasColumn('calibrations', 'quote_sent_at')) {
                $table->timestamp('quote_sent_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('calibrations', 'quote_accepted_at')) {
                $table->timestamp('quote_accepted_at')->nullable()->after('quote_sent_at');
            }
            if (!Schema::hasColumn('calibrations', 'payment_received_at')) {
                $table->timestamp('payment_received_at')->nullable()->after('quote_accepted_at');
            }
            if (!Schema::hasColumn('calibrations', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('payment_received_at');
            }
        });
    }

    public function down()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->dropColumn([
                'service_types',
                'equipment_type_id',
                'quote_sent_at',
                'quote_accepted_at',
                'payment_received_at',
                'payment_status',
            ]);
        });
    }
};
