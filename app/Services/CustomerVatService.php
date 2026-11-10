<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Carbon;
use Throwable;

class CustomerVatService
{
    /**
     * Get the configured standard VAT rate.
     *
     * The standard VAT rate is configurable because
     * government VAT rates may change.
     */
    public function standardRate(): float
    {
        $rate = config('services.vat.standard_rate', 16.00);

        if (!is_numeric($rate)) {
            return 16.00;
        }

        $rate = (float) $rate;

        if (!is_finite($rate) || $rate < 0 || $rate > 100) {
            return 16.00;
        }

        return round($rate, 2);
    }

    /**
     * Determine the applicable VAT rate for a customer.
     *
     * Rules:
     * 1. Default to the configured standard VAT rate.
     * 2. Customer exemption must be approved.
     * 3. Customer must be marked tax-exempt.
     * 4. Lab Manager verification must be recorded.
     * 5. Exemption certificate number must be present.
     * 6. Exemption expiry date must be valid.
     * 7. Customer-specific VAT rate must be valid.
     *
     * This method does not modify existing quotations.
     */
    public function rate(?Customer $customer): float
    {
        $standardRate = $this->standardRate();

        if ($customer === null) {
            return $standardRate;
        }

        /*
        |--------------------------------------------------------------------------
        | Validate approved VAT exemption
        |--------------------------------------------------------------------------
        */

        if (
            $customer->vat_exemption_status !== 'approved' ||
            !$customer->is_tax_exempt ||
            !$customer->vat_verified_by ||
            !$customer->vat_verified_at ||
            blank($customer->vat_exemption_certificate_number) ||
            blank($customer->vat_exemption_expiry_date) ||
            $customer->customer_vat_rate === null
        ) {
            return $standardRate;
        }

        /*
        |--------------------------------------------------------------------------
        | Validate exemption expiry
        |--------------------------------------------------------------------------
        */

        try {
            $expiryDate = Carbon::parse(
                $customer->vat_exemption_expiry_date
            )->startOfDay();

            if ($expiryDate->lt(today())) {
                return $standardRate;
            }
        } catch (Throwable $exception) {
            return $standardRate;
        }

        /*
        |--------------------------------------------------------------------------
        | Validate approved customer VAT rate
        |--------------------------------------------------------------------------
        */

        if (!is_numeric($customer->customer_vat_rate)) {
            return $standardRate;
        }

        $customerRate = (float) $customer->customer_vat_rate;

        if (
            !is_finite($customerRate) ||
            $customerRate < 0 ||
            $customerRate > 100
        ) {
            return $standardRate;
        }

        /*
        |--------------------------------------------------------------------------
        | Return approved customer-specific VAT rate
        |--------------------------------------------------------------------------
        */

        return round($customerRate, 2);
    }
}
