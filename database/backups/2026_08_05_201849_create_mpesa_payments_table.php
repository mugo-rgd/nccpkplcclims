<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_payments', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('quote_id')
                ->constrained('quotes')
                ->cascadeOnDelete();

            $table
                ->foreignId('calibration_id')
                ->nullable()
                ->constrained('calibrations')
                ->nullOnDelete();

            $table
                ->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table
                ->string('payment_reference', 100)
                ->unique();

            $table->string('phone_number', 20);

            $table->decimal('amount', 15, 2);

            $table
                ->string('currency', 10)
                ->default('KES');

            $table
                ->string('status', 30)
                ->default('pending')
                ->index();

            $table
                ->string('merchant_request_id')
                ->nullable()
                ->index();

            $table
                ->string('checkout_request_id')
                ->nullable()
                ->unique();

            $table
                ->string('response_code', 50)
                ->nullable();

            $table
                ->text('response_description')
                ->nullable();

            $table
                ->text('customer_message')
                ->nullable();

            $table
                ->integer('result_code')
                ->nullable();

            $table
                ->text('result_description')
                ->nullable();

            $table
                ->string('mpesa_receipt_number', 100)
                ->nullable()
                ->unique();

            $table
                ->decimal('paid_amount', 15, 2)
                ->nullable();

            $table
                ->string('transaction_phone', 20)
                ->nullable();

            $table
                ->timestamp('transaction_date')
                ->nullable();

            $table
                ->timestamp('initiated_at')
                ->nullable();

            $table
                ->timestamp('completed_at')
                ->nullable();

            $table
                ->timestamp('failed_at')
                ->nullable();

            $table
                ->timestamp('cancelled_at')
                ->nullable();

            $table->json('request_payload')->nullable();
            $table->json('initial_response')->nullable();
            $table->json('callback_payload')->nullable();

            $table
                ->string('ip_address', 45)
                ->nullable();

            $table
                ->string('user_agent', 500)
                ->nullable();

            $table->timestamps();

            $table->index([
                'quote_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_payments');
    }
};
