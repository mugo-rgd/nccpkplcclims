<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Add calibration_id to link with calibrations
            if (!Schema::hasColumn('quotes', 'calibration_id')) {
                $table->foreignId('calibration_id')->nullable()->after('id')->constrained('calibrations')->onDelete('cascade');
            }

            // Add customer_id if not exists (already exists in your table)
            // customer_id already exists

            // Add created_by if not exists (already exists in your table)
            // created_by already exists

            // Add approved_by for tracking who approved the quote
            if (!Schema::hasColumn('quotes', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            }

            // Add quote_reference for additional reference
            if (!Schema::hasColumn('quotes', 'quote_reference')) {
                $table->string('quote_reference')->nullable()->after('quote_number');
            }

            // Add subtotal (before tax/discount)
            if (!Schema::hasColumn('quotes', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('quote_reference');
            }

            // Add tax_amount
            if (!Schema::hasColumn('quotes', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
            }

            // Add tax_rate
            if (!Schema::hasColumn('quotes', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('tax_amount');
            }

            // Add discount_amount
            if (!Schema::hasColumn('quotes', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('tax_rate');
            }

            // Add discount_percentage
            if (!Schema::hasColumn('quotes', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('discount_amount');
            }

            // Add total_amount (already exists in your table)
            // total_amount already exists

            // Add items as JSON for line items
            if (!Schema::hasColumn('quotes', 'items')) {
                $table->json('items')->nullable()->after('total_amount');
            }

            // Add notes
            if (!Schema::hasColumn('quotes', 'notes')) {
                $table->text('notes')->nullable()->after('items');
            }

            // Add sent_at
            if (!Schema::hasColumn('quotes', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('terms');
            }

            // Add approved_at
            if (!Schema::hasColumn('quotes', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('sent_at');
            }

            // Add rejected_at
            if (!Schema::hasColumn('quotes', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }

            // Add expired_at
            if (!Schema::hasColumn('quotes', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('rejected_at');
            }

            // Add approval_notes
            if (!Schema::hasColumn('quotes', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->after('terms');
            }

            // Add rejection_reason
            if (!Schema::hasColumn('quotes', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approval_notes');
            }

            // Add amount_paid
            if (!Schema::hasColumn('quotes', 'amount_paid')) {
                $table->decimal('amount_paid', 15, 2)->default(0)->after('total_amount');
            }

            // Add balance_due
            if (!Schema::hasColumn('quotes', 'balance_due')) {
                $table->decimal('balance_due', 15, 2)->default(0)->after('amount_paid');
            }

            // Add payment_due_date
            if (!Schema::hasColumn('quotes', 'payment_due_date')) {
                $table->timestamp('payment_due_date')->nullable()->after('balance_due');
            }

            // Add is_external
            if (!Schema::hasColumn('quotes', 'is_external')) {
                $table->boolean('is_external')->default(true)->after('payment_due_date');
            }

            // Add currency
            if (!Schema::hasColumn('quotes', 'currency')) {
                $table->string('currency', 10)->default('KES')->after('is_external');
            }

            // Add metadata
            if (!Schema::hasColumn('quotes', 'metadata')) {
                $table->json('metadata')->nullable()->after('currency');
            }

            // Add soft deletes
            if (!Schema::hasColumn('quotes', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down()
    {
        Schema::table('quotes', function (Blueprint $table) {
            $columns = [
                'calibration_id',
                'approved_by',
                'quote_reference',
                'subtotal',
                'tax_amount',
                'tax_rate',
                'discount_amount',
                'discount_percentage',
                'items',
                'notes',
                'sent_at',
                'approved_at',
                'rejected_at',
                'expired_at',
                'approval_notes',
                'rejection_reason',
                'amount_paid',
                'balance_due',
                'payment_due_date',
                'is_external',
                'currency',
                'metadata',
                'deleted_at'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('quotes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
