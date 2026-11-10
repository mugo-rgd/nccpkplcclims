<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuotationNumberService
{
    private const BASE_PREFIX = 'KP1/13B.1/QT/3/7-';

    private const NUMBER_LENGTH = 5;

    /**
     * Generate the next quotation number.
     *
     * IMPORTANT:
     * Must be called inside the same database transaction
     * that creates the quotation.
     */
    public function generate(Customer $customer): string
    {
        if (DB::transactionLevel() < 1) {
            throw new RuntimeException(
                'Quotation number generation must run inside a database transaction.'
            );
        }

        /*
         * Obtain an exclusive database lock.
         *
         * The lock is held until the surrounding transaction
         * commits or rolls back.
         */
        $lock = DB::table('quotation_number_locks')
            ->where('id', 1)
            ->lockForUpdate()
            ->first();

        if (!$lock) {
            throw new RuntimeException(
                'Quotation numbering lock record is missing. Run the required migration.'
            );
        }

        $initials = $this->getCustomerInitials($customer);

        $prefix = self::BASE_PREFIX . $initials . '-';

        /*
         * Read existing quotation numbers for this prefix.
         *
         * Find the highest numeric suffix rather than relying
         * on the most recently inserted quotation.
         */
        $maximum = 0;

        Quote::query()
            ->where('quote_number', 'LIKE', $prefix . '%')
            ->select(['id', 'quote_number'])
            ->orderBy('id')
            ->chunk(500, function ($quotes) use ($prefix, &$maximum) {
                foreach ($quotes as $quote) {
                    $number = (string) $quote->quote_number;

                    if (!str_starts_with($number, $prefix)) {
                        continue;
                    }

                    $suffix = substr(
                        $number,
                        strlen($prefix)
                    );

                    if (!preg_match('/^\d{5}$/', $suffix)) {
                        continue;
                    }

                    $maximum = max(
                        $maximum,
                        (int) $suffix
                    );
                }
            });

        $nextNumber = $maximum + 1;

        if ($nextNumber > 99999) {
            throw new RuntimeException(
                'Quotation numbering limit of 99999 has been reached for prefix ' . $prefix
            );
        }

        return $prefix . str_pad(
            (string) $nextNumber,
            self::NUMBER_LENGTH,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Preserve the original customer-initials algorithm
     * from CalibrationController.
     */
    private function getCustomerInitials(
        Customer $customer
    ): string {
        $customerName = trim(
            (string) (
                $customer->company_name
                ?? $customer->name
                ?? ''
            )
        );

        if ($customerName === '') {
            return 'CUS';
        }

        /*
         * Remove common company suffixes before
         * generating initials.
         */
        $customerName = preg_replace(
            '/\b(LIMITED|LTD|PLC|COMPANY|CO|KENYA)\b/i',
            '',
            $customerName
        );

        $words = preg_split(
            '/\s+/',
            trim($customerName),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $initials = collect($words)
            ->take(4)
            ->map(
                static fn (string $word): string =>
                    strtoupper(
                        substr($word, 0, 1)
                    )
            )
            ->implode('');

        if ($initials === '') {
            $initials = strtoupper(
                substr(
                    preg_replace(
                        '/[^A-Za-z0-9]/',
                        '',
                        $customerName
                    ),
                    0,
                    4
                )
            );
        }

        return $initials !== ''
            ? $initials
            : 'CUS';
    }
}
