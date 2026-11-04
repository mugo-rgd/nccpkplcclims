
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            // VAT exemption workflow
            $table->string('vat_exemption_status', 20)
                ->default('none');

            // Approved customer-specific VAT rate
            $table->decimal('customer_vat_rate', 5, 2)
                ->nullable();

            // Exemption certificate information
            $table->string(
                'vat_exemption_certificate_number'
            )->nullable();

            $table->date(
                'vat_exemption_expiry_date'
            )->nullable();

            // Lab Manager verification
            $table->foreignId('vat_verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('vat_verified_at')
                ->nullable();

            $table->text('vat_verification_notes')
                ->nullable();

            $table->index('vat_exemption_status');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            $table->dropForeign(['vat_verified_by']);

            $table->dropIndex([
                'vat_exemption_status'
            ]);

            $table->dropColumn([
                'vat_exemption_status',
                'customer_vat_rate',
                'vat_exemption_certificate_number',
                'vat_exemption_expiry_date',
                'vat_verified_by',
                'vat_verified_at',
                'vat_verification_notes',
            ]);
        });
    }
};
