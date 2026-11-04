<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_tax_exempt')->default(false);

            $table->decimal('vat_rate', 5, 2)
                ->nullable();

            $table->string('tax_exemption_certificate')
                ->nullable();

            $table->date('tax_exemption_expiry')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'is_tax_exempt',
                'vat_rate',
                'tax_exemption_certificate',
                'tax_exemption_expiry',
            ]);
        });
    }
};
