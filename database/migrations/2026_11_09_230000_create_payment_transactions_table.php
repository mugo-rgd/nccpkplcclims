<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->restrictOnDelete();
            $table->foreignId('mpesa_payment_id')->nullable()->unique()->constrained('mpesa_payments')->restrictOnDelete();
            $table->string('method', 30); // mpesa, bank_transfer, cash, cheque
            $table->string('status', 30)->default('pending'); // pending, confirmed, rejected
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('KES');
            $table->string('reference', 120)->nullable();
            $table->string('receipt_path', 500)->nullable();
            $table->string('clearance_path', 500)->nullable();
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->unique(['method', 'reference'], 'payment_transactions_method_reference_unique');
            $table->index(['quote_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('payment_transactions'); }
};
