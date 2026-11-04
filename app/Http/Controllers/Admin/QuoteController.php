<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
    abort_unless(
        $request->user()?->hasAnyRole([
            'super_admin',
            'lab_manager',
        ]),
        403
    );

    $replacement = DB::transaction(function () use (
        $quote,
        $vatService,
        $request
    ) {
        $old = Quote::query()
            ->lockForUpdate()
            ->findOrFail($quote->id);

        if (!in_array($old->status, [
            Quote::STATUS_DRAFT,
            Quote::STATUS_SENT,
            Quote::STATUS_REJECTED,
        ], true)) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'Only draft, sent or rejected quotations can be replaced.',
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

        if (
            (float) $old->amount_paid > 0 ||
            $old->mpesaPayments()->exists()
        ) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'This quotation has payment activity. Finance must reconcile it before replacement.',
            ]);
        }

        $items = $old->items_data;

        if (empty($items)) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'The original quotation has no saved items.',
            ]);
        }

        $customer = $old->customer;

        if (!$customer) {
            throw ValidationException::withMessages([
                'quotation' =>
                    'The quotation has no linked customer.',
            ]);
        }

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
            'created_at',
            'updated_at',
        ]);

        // $new->quote_number =
        //     'QT-REP-' . $old->id . '-'
        //     . strtoupper(Str::random(10));

$new->quote_number = $this->generateQuoteNumber($customer);

        $new->quote_reference =
            'REF-' . strtoupper(Str::random(12));

        $new->items = $items;
        $new->status = Quote::STATUS_DRAFT;
        $new->created_by = $request->user()->id;

        $new->tax_rate = $vatService->rate($customer);

        $new->amount_paid = 0;
        $new->balance_due = 0;
        $new->paid_at = null;
        $new->payment_method = null;
        $new->payment_reference = null;
        $new->payment_token = Str::random(64);
        $new->mpesa_payment_status = 'unpaid';
        $new->pdf_path = null;

        $new->valid_until = today()->addDays(30);

        $new->metadata = array_merge(
            $oldMetadata,
            [
                'replaces_quote_id' => $old->id,
                'replacement_reason' =>
                    'Replacement following cancellation',
                'replacement_created_at' =>
                    now()->toIso8601String(),
            ]
        );

        $new->save();

        // Recalculate using the saved item prices
        // and the newly approved customer VAT rate.
        $new->updateTotals();

        // Preserve original financial figures and PDF.
        $oldMetadata['replaced_by_quote_id'] = $new->id;
        $oldMetadata['cancelled_by'] =
            $request->user()->id;
        $oldMetadata['cancelled_at'] =
            now()->toIso8601String();

        $old->status = Quote::STATUS_CANCELLED;
        $old->metadata = $oldMetadata;
        $old->save();

        return $new;
    });

    return redirect()
        ->route('admin.quotes.show', $replacement)
        ->with(
            'success',
            'Quotation cancelled and replacement draft created successfully.'
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
     * Approve a sent quotation.
     */
    public function approve(
        Request $request,
        Quote $quote
    ): RedirectResponse {
        if ($quote->isApproved()) {
            return back()->with(
                'info',
                'This quotation has already been approved.'
            );
        }

        if (!$quote->canBeApproved()) {
            return back()->with(
                'error',
                'Only a sent quotation can be approved.'
            );
        }

        $validated = $request->validate([
            'approval_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(
            function () use (
                $quote,
                $validated
            ): void {
                $quote->markAsApproved(
                    auth()->id(),
                    $validated['approval_notes']
                        ?? null
                );

                $quote->loadMissing(
                    'calibration'
                );

                if ($quote->calibration) {
                    $quote->calibration->update([
                        'status' =>
                            'quoted',

                        'quote_accepted_at' =>
                            now(),
                    ]);
                }
            }
        );

        return redirect()
            ->route(
                'admin.quotes.show',
                $quote
            )
            ->with(
                'success',
                'Quotation approved successfully.'
            );
    }
}
