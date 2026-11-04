<?php

namespace App\Services;

use App\Models\Customer;

class CustomerVatService
{
    public function rate(?Customer $customer): float
    {
        $standardRate = 16.00;

        if (!$customer || !$customer->is_tax_exempt) {
            return $standardRate;
        }

        // Require an exemption reference.
        if (blank($customer->tax_exemption_certificate)) {
            return $standardRate;
        }

        // An expired exemption cannot be applied.
        if (
            $customer->tax_exemption_expiry &&
            $customer->tax_exemption_expiry->isPast()
        ) {
            return $standardRate;
        }

        // No rate configured: do not assume exemption means 0%.
        if ($customer->vat_rate === null) {
            return $standardRate;
        }

        return (float) $customer->vat_rate;
    }
}


