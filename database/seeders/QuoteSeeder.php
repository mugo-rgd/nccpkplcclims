<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\quote;
use App\Models\Calibration;
use App\Models\Customer;

class QuoteSeeder extends Seeder
{
    public function run()
    {
        $calibration = Calibration::first();
        $customer = Customer::first();

        if ($calibration && $customer) {
            quote::create([
                'calibration_id' => $calibration->id,
                'customer_id' => $customer->id,
                'created_by' => 1,
                'quote_number' => quote::generateQuoteNumber(),
                'quote_reference' => 'REF-' . rand(1000, 9999),
                'subtotal' => 15000.00,
                'tax_amount' => 2250.00,
                'tax_rate' => 16.00,
                'discount_amount' => 0.00,
                'discount_percentage' => 0.00,
                'total_amount' => 17250.00,
                'items' => json_encode([
                    [
                        'description' => 'Calibration Service',
                        'quantity' => 1,
                        'unit_price' => 15000.00,
                        'total' => 15000.00
                    ]
                ]),
                'notes' => 'Sample quotation',
                'terms' => 'Payment due within 30 days',
                'valid_until' => now()->addDays(30),
                'status' => 'approved',
                'is_external' => true,
                'currency' => 'KES',
                'amount_paid' => 0,
                'balance_due' => 17250.00,
            ]);

            $this->command->info('✅ Sample quote created successfully!');
        } else {
            $this->command->warn('⚠️ No calibration or customer found. Please create them first.');
        }
    }
}
