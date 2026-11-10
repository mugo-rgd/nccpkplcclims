<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use App\Models\Customer;
use App\Models\Quote;
use App\Services\DocumentGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Services\CustomerVatService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\QuotationNumberService;


class QuoteController extends Controller
{
    public function __construct(
        private readonly DocumentGenerationService $documentGenerationService
    ) {
    }

    /**
     * Display quotation details.
     */
    public function show(
        Quote $quote
    ): View {
        $quote->load([
            'customer',
            'calibration.customer',
            'calibration.equipment',
            'calibration.equipmentType',
        ]);

        $calibration = $quote->calibration;

        return view(
            'admin.quotes.show',
            compact(
                'quote',
                'calibration'
            )
        );
    }






public function cancelAndReplace(
    Request $request,
    Quote $quote,
    CustomerVatService $vatService
): RedirectResponse {
    /*
    |--------------------------------------------------------------------------
    | 1. Authorization
    |--------------------------------------------------------------------------
    */
    abort_unless(
        $request->user()?->hasAnyRole([
            'super_admin',
            'lab_manager',
        ]),
        403,
        'You are not authorized to cancel and replace quotations.'
    );

    $replacement = DB::transaction(function () use (
        $request,
        $quote,
        $vatService
    ): Quote {

        /*
        |--------------------------------------------------------------------------
        | 2. Lock and validate original quotation
        |--------------------------------------------------------------------------
        */
        $old = Quote::query()
            ->whereKey($quote->id)
            ->lockForUpdate()
            ->firstOrFail();

        if (!in_array($old->status, [
            Quote::STATUS_DRAFT,
            Quote::STATUS_APPROVED,
            Quote::STATUS_SENT,
            Quote::STATUS_REJECTED,
        ], true)) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'Only draft, approved, sent or rejected quotations can be cancelled and replaced.',
            ]);
        }

        $oldMetadata = is_array($old->metadata)
            ? $old->metadata
            : [];

        if (!empty($oldMetadata['replaced_by_quote_id'])) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'This quotation has already been replaced.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Protect quotations with payment activity
        |--------------------------------------------------------------------------
        */
        if (
            (float) ($old->amount_paid ?? 0) > 0
            || $old->mpesaPayments()->exists()
            || $old->paid_at !== null
            || in_array((string) $old->mpesa_payment_status, [
                'paid',
                'partially_paid',
            ], true)
        ) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'This quotation has recorded payment activity. '
                    . 'Finance must reconcile the payments before replacement.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Validate original quotation items
        |--------------------------------------------------------------------------
        */
        $items = $old->items_data;

        if (empty($items)) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'The original quotation has no saved items. '
                    . 'A replacement cannot be generated.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Lock calibration and verify latest quotation
        |--------------------------------------------------------------------------
        */
        $calibration = null;

        if ($old->calibration_id) {

            $calibration = Calibration::query()
                ->whereKey($old->calibration_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Prevent replacing an outdated quotation.
            $latestQuote = Quote::query()
                ->where('calibration_id', $calibration->id)
                ->where('status', '!=', Quote::STATUS_CANCELLED)
                ->orderByDesc('id')
                ->first();

            if (
                $latestQuote !== null
                && (int) $latestQuote->id !== (int) $old->id
            ) {
                throw ValidationException::withMessages([
                    'quotation' =>
                        'This quotation is not the latest active quotation '
                        . 'for this calibration. Please open the current quotation.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Check calibration payment activity
            |--------------------------------------------------------------------------
            */
            if (
                (float) ($calibration->amount_paid ?? 0) > 0
                || $calibration->payment_received_at !== null
                || $calibration->payment_date !== null
                || $calibration->payment_status === 'paid'
            ) {
                throw ValidationException::withMessages([
                    'quotation' =>
                        'This calibration has recorded payment activity. '
                        . 'Finance must reconcile the payments before replacement.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Lock customer and obtain current approved VAT rate
        |--------------------------------------------------------------------------
        */
        $customer = Customer::query()
            ->whereKey($old->customer_id)
            ->lockForUpdate()
            ->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'The quotation has no valid linked customer.',
            ]);
        }

        $taxRate = round(
            max(
                0,
                min(
                    100,
                    (float) $vatService->rate($customer)
                )
            ),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | 7. Replicate original quotation
        |--------------------------------------------------------------------------
        */
        $new = $old->replicate([
            'quote_number',
            'quote_reference',
            'status',
            'sent_at',
            'approved_at',
            'approved_by',
            'approval_notes',
            'rejected_at',
            'rejection_reason',
            'expired_at',
            'amount_paid',
            'balance_due',
            'paid_at',
            'payment_method',
            'payment_reference',
            'payment_token',
            'mpesa_payment_status',
            'pdf_path',
            'metadata',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 8. Generate new sequential quotation number
        |--------------------------------------------------------------------------
        |
        | Must remain inside the database transaction.
        | QuotationNumberService handles prefix-based sequencing
        | and locks the quotation numbering record.
        |
        */
        $new->quote_number = app(
            QuotationNumberService::class
        )->generate($customer);

        $new->quote_reference =
            'REF-' . strtoupper(Str::random(12));

        /*
        |--------------------------------------------------------------------------
        | 9. Set replacement quotation details
        |--------------------------------------------------------------------------
        */
        $new->customer_id = $customer->id;
        $new->calibration_id = $old->calibration_id;
        $new->created_by = $request->user()->id;

        $new->items = $items;

        $new->status = Quote::STATUS_DRAFT;

        $new->tax_rate = $taxRate;

        // Preserve the original discount percentage.
        $new->discount_percentage =
            (float) ($old->discount_percentage ?? 0);

        // Recalculate the discount from the percentage.
        $new->discount_amount = 0;

        // Totals will be recalculated after saving.
        $new->subtotal = 0;
        $new->tax_amount = 0;
        $new->total_amount = 0;

        /*
        |--------------------------------------------------------------------------
        | 10. Reset payment information
        |--------------------------------------------------------------------------
        */
        $new->amount_paid = 0;
        $new->balance_due = 0;

        $new->paid_at = null;
        $new->payment_method = null;
        $new->payment_reference = null;
        $new->payment_due_date = null;

        $new->payment_token = Str::random(64);
        $new->mpesa_payment_status = 'unpaid';

        /*
        |--------------------------------------------------------------------------
        | 11. Reset approval and document information
        |--------------------------------------------------------------------------
        */
        $new->sent_at = null;
        $new->approved_at = null;
        $new->approved_by = null;
        $new->approval_notes = null;

        $new->rejected_at = null;
        $new->rejection_reason = null;
        $new->expired_at = null;

        $new->pdf_path = null;

        $new->valid_until = today()->addDays(30);

        /*
        |--------------------------------------------------------------------------
        | 12. Record replacement history
        |--------------------------------------------------------------------------
        */
        $new->metadata = [
            'replaces_quote_id' => $old->id,
            'original_quote_number' => $old->quote_number,
            'replacement_reason' =>
                'Replacement following cancellation',
            'replacement_created_at' =>
                now()->toIso8601String(),
            'replacement_created_by' =>
                $request->user()->id,
        ];

        /*
        |--------------------------------------------------------------------------
        | 13. Save replacement quotation
        |--------------------------------------------------------------------------
        */
        $new->save();

        /*
        |--------------------------------------------------------------------------
        | 14. Recalculate quotation totals
        |--------------------------------------------------------------------------
        |
        | Uses the saved item prices, percentage discount,
        | approved VAT rate, and onsite costs.
        |
        */
        $new->updateTotals();

        /*
        |--------------------------------------------------------------------------
        | 15. Synchronize calibration financial information
        |--------------------------------------------------------------------------
        */
        if ($calibration !== null) {

            $isExternal = (bool) $new->is_external;

            $calibration->update([
                'amount_due' => $isExternal
                    ? (float) $new->total_amount
                    : 0,

                'amount_paid' => 0,

                'requires_payment' => $isExternal,

                'payment_status' => $isExternal
                    ? 'pending'
                    : null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 16. Cancel original quotation
        |--------------------------------------------------------------------------
        |
        | Keep the original quotation, its financial figures,
        | its items and its PDF unchanged.
        |
        */
        $oldMetadata['replaced_by_quote_id'] = $new->id;

        $oldMetadata['replacement_quote_number'] =
            $new->quote_number;

        $oldMetadata['cancelled_by'] =
            $request->user()->id;

        $oldMetadata['cancelled_at'] =
            now()->toIso8601String();

        $oldMetadata['cancellation_reason'] =
            'Cancelled and replaced by a new quotation.';

        $old->status = Quote::STATUS_CANCELLED;
        $old->metadata = $oldMetadata;

        $old->save();

        /*
        |--------------------------------------------------------------------------
        | 17. Return replacement quotation
        |--------------------------------------------------------------------------
        */
        return $new;

    }, 3);

    /*
    |--------------------------------------------------------------------------
    | 18. Redirect to replacement quotation
    |--------------------------------------------------------------------------
    */
    return redirect()
        ->route('admin.quotes.show', $replacement)
        ->with(
            'success',
            'Quotation '
            . $quote->quote_number
            . ' was cancelled successfully. '
            . 'Replacement quotation '
            . $replacement->quote_number
            . ' has been created as a draft.'
        );
}

    /**
     * Download the quotation PDF.
     *
     * If the quotation has already been stored, download
     * directly from storage.
     *
     * If the PDF does not exist yet, generate it using
     * resources/views/documents/quotation.blade.php,
     * store it, save pdf_path, then download it.
     */
  public function download(
    Quote $quote
) {
    $quote->load([
        'customer',
        'calibration.customer',
        'calibration.equipment',
        'calibration.equipmentType',
    ]);

    if (!$quote->calibration) {
        return back()->with(
            'error',
            'This quotation is not linked to a calibration.'
        );
    }

    $disk = Storage::disk('public');

    /*
     * Generate only if there is no usable stored PDF.
     */
    if (
        blank($quote->pdf_path)
        || !$disk->exists($quote->pdf_path)
    ) {
        try {
    $this->documentGenerationService
    ->generateQuotationForQuote($quote);

    $quote->refresh();

} catch (\Throwable $exception) {
    Log::error('Quotation PDF generation failed.', [
        'quote_id' => $quote->id,
        'error' => $exception->getMessage(),
    ]);

    return back()->with(
        'error',
        'Unable to generate the quotation PDF. Please contact the administrator.'
    );
}

        $quote->refresh();
    }

    if (
        blank($quote->pdf_path)
        || !$disk->exists($quote->pdf_path)
    ) {
        return back()->with(
            'error',
            'The quotation PDF could not be generated or found.'
        );
    }

    $safeQuoteNumber = preg_replace(
        '/[^A-Za-z0-9._-]/',
        '_',
        (string) $quote->quote_number
    );

    $filename =
        'Quotation-'
        . $safeQuoteNumber
        . '.pdf';

    return $disk->download(
        $quote->pdf_path,
        $filename,
        [
            'Content-Type' =>
                'application/pdf',
        ]
    );
}

/**
 * Generate the next quotation number for a customer.
 */
private function generateQuoteNumber(
    Customer $customer
): string {
    $initials = $this->getCustomerInitials(
        $customer
    );

    $prefix = 'KP1/13B.1/QT/3/7-' . $initials . '-';

    /*
     * Get the latest quotation for this customer's initials.
     */
    $lastQuote = Quote::query()
        ->where(
            'quote_number',
            'LIKE',
            $prefix . '%'
        )
        ->orderByDesc('id')
        ->first();

    $nextNumber = 1;

    if ($lastQuote && filled($lastQuote->quote_number)) {
        /*
         * Extract the final numeric part.
         *
         * Example:
         * KP1/13B.1/QT/3/7-ABC-00001
         */
        if (
            preg_match(
                '/-(\d+)$/',
                $lastQuote->quote_number,
                $matches
            )
        ) {
            $nextNumber = ((int) $matches[1]) + 1;
        }
    }

    return $prefix
        . str_pad(
            (string) $nextNumber,
            5,
            '0',
            STR_PAD_LEFT
        );
}

/**
 * Generate customer initials for quotation numbering.
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
     * Remove common company suffixes before generating initials.
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

 /**
 * Approve a quotation.
 */
public function approve(
    Request $request,
    Quote $quote
): RedirectResponse {
    abort_unless(
        $request->user()?->hasAnyRole([
            'supervisor',
            'lab_manager',
            'super_admin',
        ]),
        403
    );

    $validated = $request->validate([
        'approval_notes' => [
            'nullable',
            'string',
            'max:2000',
        ],
    ]);

    DB::transaction(function () use (
        $quote,
        $request,
        $validated
    ): void {
        $lockedQuote = Quote::query()
            ->whereKey($quote->id)
            ->lockForUpdate()
            ->firstOrFail();

    if (!$lockedQuote->canBeApproved()) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'Only valid draft quotations can be approved.',
            ]);
        }

        if ($lockedQuote->isExpiredByDate()) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'This quotation has expired and cannot be approved.',
            ]);
        }

        if (
            (float) ($lockedQuote->amount_paid ?? 0) > 0
            || $lockedQuote->mpesaPayments()->exists()
            || $lockedQuote->paid_at !== null
        ) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'A draft quotation with payment activity cannot be approved.',
            ]);
        }

        $lockedQuote->markAsApproved(
            $request->user()->id,
            $validated['approval_notes'] ?? null
        );
    }, 3);

    return redirect()
        ->route('admin.quotes.show', $quote)
        ->with(
            'success',
            'Quotation approved successfully.'
        );
}

/**
 * Reject a quotation.
 */
public function reject(
    Request $request,
    Quote $quote
): RedirectResponse {
    abort_unless(
        $request->user()?->hasAnyRole([
            'supervisor',
            'lab_manager',
            'super_admin',
        ]),
        403
    );

    $validated = $request->validate([
        'rejection_reason' => [
            'required',
            'string',
            'min:5',
            'max:2000',
        ],
    ]);

    DB::transaction(function () use (
        $quote,
        $validated
    ): void {
        $lockedQuote = Quote::query()
            ->whereKey($quote->id)
            ->lockForUpdate()
            ->firstOrFail();

        if (!$lockedQuote->canBeRejected()) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'Only draft quotations can be rejected.',
            ]);
        }

        if (
            (float) ($lockedQuote->amount_paid ?? 0) > 0
            || $lockedQuote->mpesaPayments()->exists()
            || $lockedQuote->paid_at !== null
        ) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'This quotation has payment activity and cannot be rejected.',
            ]);
        }

        $lockedQuote->markAsRejected(
            $validated['rejection_reason']
        );
    }, 3);

    return redirect()
        ->route('admin.quotes.show', $quote)
        ->with(
            'success',
            'Quotation rejected successfully.'
        );
}
}
