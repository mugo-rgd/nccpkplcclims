<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Carbon;

class CustomerVatService
{
    private const STANDARD_RATE = 16.00;

    public function rate(?Customer $customer): float
    {
        if (!$customer) {
            return self::STANDARD_RATE;
        }

        if ($customer->vat_exemption_status !== 'approved') {
            return self::STANDARD_RATE;
        }

        if (
            !$customer->vat_verified_by ||
            !$customer->vat_verified_at ||
            blank($customer->vat_exemption_certificate_number) ||
            blank($customer->vat_exemption_expiry_date) ||
            $customer->customer_vat_rate === null
        ) {
            return self::STANDARD_RATE;
        }

        if (
            Carbon::parse(
                $customer->vat_exemption_expiry_date
            )->startOfDay()->lt(today())
        ) {
            return self::STANDARD_RATE;
        }

        $rate = (float) $customer->customer_vat_rate;

        if ($rate < 0 || $rate > 100) {
            return self::STANDARD_RATE;
        }

        return $rate;
    }
}
