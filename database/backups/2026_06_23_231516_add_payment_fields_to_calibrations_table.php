<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            if (!Schema::hasColumn('calibrations', 'is_external')) {
                $table->boolean('is_external')->default(false)->after('status');
            }
            if (!Schema::hasColumn('calibrations', 'requires_payment')) {
                $table->boolean('requires_payment')->default(true)->after('is_external');
            }
            if (!Schema::hasColumn('calibrations', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'partial', 'overdue', 'refunded'])->default('pending')->after('requires_payment');
            }
            if (!Schema::hasColumn('calibrations', 'amount_due')) {
                $table->decimal('amount_due', 15, 2)->default(0)->after('payment_status');
            }
            if (!Schema::hasColumn('calibrations', 'amount_paid')) {
                $table->decimal('amount_paid', 15, 2)->default(0)->after('amount_due');
            }
            if (!Schema::hasColumn('calibrations', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('amount_paid');
            }
            if (!Schema::hasColumn('calibrations', 'payment_reference')) {
                $table->string('payment_reference', 100)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('calibrations', 'payment_date')) {
                $table->timestamp('payment_date')->nullable()->after('payment_reference');
            }
        });
    }

    public function down()
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $columns = ['is_external', 'requires_payment', 'payment_status', 'amount_due', 'amount_paid', 'payment_method', 'payment_reference', 'payment_date'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('calibrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
