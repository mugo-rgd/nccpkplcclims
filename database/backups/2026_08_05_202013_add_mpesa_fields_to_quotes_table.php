<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (!Schema::hasColumn('quotes', 'payment_token')) {
                $table
                    ->string('payment_token', 100)
                    ->nullable()
                    ->unique()
                    ->after('balance_due');
            }

            if (!Schema::hasColumn('quotes', 'mpesa_payment_status')) {
                $table
                    ->string('mpesa_payment_status', 30)
                    ->default('unpaid')
                    ->index()
                    ->after('payment_token');
            }

            if (!Schema::hasColumn('quotes', 'paid_at')) {
                $table
                    ->timestamp('paid_at')
                    ->nullable()
                    ->after('mpesa_payment_status');
            }

            if (!Schema::hasColumn('quotes', 'payment_method')) {
                $table
                    ->string('payment_method', 30)
                    ->nullable()
                    ->after('paid_at');
            }

            if (!Schema::hasColumn('quotes', 'payment_reference')) {
                $table
                    ->string('payment_reference', 100)
                    ->nullable()
                    ->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $columns = [
                'payment_token',
                'mpesa_payment_status',
                'paid_at',
                'payment_method',
                'payment_reference',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('quotes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
