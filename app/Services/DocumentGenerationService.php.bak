<?php

namespace App\Services;

use App\Models\Calibration;
use App\Models\Certificate;
use App\Models\Customer;
use App\Models\CustomerRequest;
use App\Models\JobCard;
use App\Models\KplcEquipmentType;
use App\Models\KplcServiceType;
use App\Models\Quote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\DB;
use App\Services\Payments\MpesaService;


class DocumentGenerationService
{
     public function __construct(
        private readonly MpesaService $mpesaService
    ) {
    }
   /**
 * Generate Customer Request Form
 * Accepts either a Calibration or CustomerRequest object
 */
public function generateRequestForm($data)
{
    $calibration = null;
    $customer = null;
    $serviceTypes = collect();
    $requestNumber = null;

    $mclRepresentative = Auth::user();

    /*
     * Case 1: Calibration model.
     */
    if ($data instanceof Calibration) {
        $calibration = $data->loadMissing([
            'customer',
            'equipment',
        ]);

        $customer = $calibration->customer;

        $requestNumber =
            'KP1/13B.1//3/6RF/' .
            str_pad(
                $calibration->id,
                5,
                '0',
                STR_PAD_LEFT
            );

        if (!empty($calibration->service_types)) {
            $serviceTypes = $this->getServiceTypes(
                $calibration->service_types
            );
        }
    }

    /*
     * Case 2: CustomerRequest-like model.
     */
    elseif (
        is_object($data)
        && isset($data->calibration_id)
    ) {
        $calibration = Calibration::with([
            'customer',
            'equipment',
        ])->find($data->calibration_id);

        $customer = $calibration?->customer;

        $requestNumber =
            $data->request_number
            ?? 'KP1/13B.1//3/6RF/' .
                str_pad(
                    $data->id ?? 0,
                    5,
                    '0',
                    STR_PAD_LEFT
                );

        if (
            $calibration
            && !empty($calibration->service_types)
        ) {
            $serviceTypes = $this->getServiceTypes(
                $calibration->service_types
            );
        }
    }

    /*
     * Case 3: Array or generic object.
     */
    else {
        $calibrationId = is_object($data)
            ? ($data->calibration_id ?? null)
            : ($data['calibration_id'] ?? null);

        if ($calibrationId) {
            $calibration = Calibration::with([
                'customer',
                'equipment',
            ])->find($calibrationId);

            $customer = $calibration?->customer;

            if (
                $calibration
                && !empty($calibration->service_types)
            ) {
                $serviceTypes = $this->getServiceTypes(
                    $calibration->service_types
                );
            }
        }

        if (is_object($data)) {
            $requestNumber =
                $data->request_number
                ?? 'KP1/13B.1//3/6RF/' .
                    str_pad(
                        $data->id ?? 0,
                        5,
                        '0',
                        STR_PAD_LEFT
                    );
        } else {
            $requestNumber =
                $data['request_number']
                ?? 'KP1/13B.1//3/6RF/' .
                    str_pad(
                        $data['id'] ?? 0,
                        5,
                        '0',
                        STR_PAD_LEFT
                    );
        }
    }

    /*
     * Related equipment record.
     */
    $equipmentRecord = $calibration?->equipment;

    /*
     * Prepare a predictable array for the Blade.
     */
    $equipment = [
        'equipment_name' =>
            $calibration?->equipment_name
            ?? 'N/A',

        'equipment_type' =>
            $equipmentRecord?->type
            ?? $equipmentRecord?->name
            ?? 'N/A',

        'serial_number' =>
            $calibration?->serial_number
            ?? $equipmentRecord?->serial_number
            ?? 'N/A',

        'manufacturer' =>
            $calibration?->manufacturer
            ?? $equipmentRecord?->manufacturer
            ?? 'N/A',

        'model' =>
            $calibration?->model
            ?? $equipmentRecord?->model
            ?? 'N/A',

        'accuracy_class' =>
            $calibration?->accuracy_class
            ?? $equipmentRecord?->accuracy_class
            ?? 'N/A',

        'ratings' =>
            $calibration?->ratings
            ?? $equipmentRecord?->ratings
            ?? 'N/A',

        'condition' =>
            $calibration?->equipment_condition
            ?? $equipmentRecord?->condition
            ?? 'N/A',
    ];

    $dataArray = [
        'calibration' => $calibration,
        'customer' => $customer,
        'equipment' => $equipment,
        'serviceTypes' => $serviceTypes,
        'date' => now()->format('Y-m-d'),
        'requestNumber' => $requestNumber,
        'mclRepresentative' => $mclRepresentative,
    ];

 $pdf = PDF::loadView(
    'documents.request-form',
    $dataArray
);

$pdf->setPaper(
    'a4',
    'portrait'
);

$pdf->setOptions([
    'defaultFont' => 'DejaVu Sans',
    'isHtml5ParserEnabled' => true,
    'isRemoteEnabled' => true,
]);

/*
|--------------------------------------------------------------------------
| Build safe request-form filename
|--------------------------------------------------------------------------
*/

$safeRequestNumber = preg_replace(
    '/[^A-Za-z0-9_-]/',
    '_',
    (string) $requestNumber
);

$safeRequestNumber = preg_replace(
    '/_+/',
    '_',
    $safeRequestNumber
);

$safeRequestNumber = trim(
    $safeRequestNumber,
    '_'
);

if ($safeRequestNumber === '') {
    $safeRequestNumber =
        'CAL-' . ($calibration?->id ?? time());
}

$fileName =
    'Request-Form-'
    . $safeRequestNumber;

/*
|--------------------------------------------------------------------------
| Save PDF
|--------------------------------------------------------------------------
*/

$pdfPath = $this->savePDF(
    $pdf,
    $fileName,
    'request-forms'
);

Log::info(
    'Request form PDF save result.',
    [
        'calibration_id' =>
            $calibration?->id,

        'request_number' =>
            $requestNumber,

        'file_name' =>
            $fileName,

        'returned_pdf_path' =>
            $pdfPath,
    ]
);

/*
|--------------------------------------------------------------------------
| Validate returned PDF path
|--------------------------------------------------------------------------
*/

if (blank($pdfPath)) {
    Log::error(
        'Request form PDF path was empty after saving.',
        [
            'calibration_id' =>
                $calibration?->id,

            'request_number' =>
                $requestNumber,
        ]
    );

    throw new \RuntimeException(
        'Request form PDF was generated but no storage path was returned.'
    );
}

/*
|--------------------------------------------------------------------------
| Save path on calibration
|--------------------------------------------------------------------------
*/

if (!$calibration) {
    Log::warning(
        'Request form generated without a calibration model.',
        [
            'request_number' =>
                $requestNumber,

            'pdf_path' =>
                $pdfPath,
        ]
    );

    return $pdf;
}

$calibration->request_form_path =
    $pdfPath;

$calibration->save();

$calibration->refresh();

Log::info(
    'Request form PDF generated and database path saved.',
    [
        'calibration_id' =>
            $calibration->id,

        'request_number' =>
            $requestNumber,

        'pdf_path' =>
            $pdfPath,

        'database_request_form_path' =>
            $calibration->request_form_path,
    ]
);

return $pdf;
}


public function generateQuotation(Calibration $calibration)
{
    $quote = Quote::query()
        ->where('calibration_id', $calibration->id)
        ->where('status', '!=', Quote::STATUS_CANCELLED)
        ->latest('id')
        ->firstOrFail();

    return $this->generateQuotationForQuote($quote);
}

/**
 * Render a PDF for the exact saved quotation, without changing its totals.
 */
public function generateQuotationForQuote(Quote $quote)
{
    $quote->loadMissing(['customer', 'calibration']);
    $calibration = $quote->calibration;

    if (!$calibration) {
        throw new \RuntimeException(
            'This quotation has no linked calibration. Batch quotation PDF rendering is not yet supported.'
        );
    }

    $calibration->loadMissing(['customer', 'equipment']);
    $customer = $quote->customer ?? $calibration->customer;

    if (!$customer) {
        throw new \RuntimeException('No customer is associated with this quotation.');
    }

    // Existing quotations are the source of truth.
    // Never recalculate their financial amounts here.
    $subtotal = (float) $quote->subtotal;
    $taxRate = (float) $quote->tax_rate;
    $tax = (float) $quote->tax_amount;
    $total = (float) $quote->total_amount;

    $discountAmount = (float) ($quote->discount_amount ?? 0);
    $discountPercentage = (float) ($quote->discount_percentage ?? 0);

    $amountPaid = (float) ($quote->amount_paid ?? 0);
    $balanceDue = (float) ($quote->balance_due ?? 0);

    $items = $quote->items_data;

    $serviceTypes = collect();

    if (!empty($calibration->service_types)) {
        $serviceTypes = $this->getServiceTypes(
            $calibration->service_types
        );
    }

    $equipmentRate = 0.0;
    $equipmentTypeName = 'N/A';

    if ($calibration->equipment_type_id) {
        $equipmentType = KplcEquipmentType::find(
            $calibration->equipment_type_id
        );

        $equipmentRate = (float) ($equipmentType?->rate ?? 0);
        $equipmentTypeName = $equipmentType?->name ?? 'N/A';
    }

    // On-site charges come from the saved quotation items.
    $perDiem = 0.0;
    $transport = 0.0;
    $otherCosts = 0.0;

    foreach ($items as $item) {
        $lineTotal = (float) (
            $item['total']
            ?? (
                (float) ($item['quantity'] ?? 1)
                * (float) ($item['unit_price'] ?? 0)
            )
        );

        switch ($item['type'] ?? '') {
            case 'onsite_perdiem':
                $perDiem += $lineTotal;
                break;

            case 'onsite_transport':
                $transport += $lineTotal;
                break;

            case 'onsite_other':
                $otherCosts += $lineTotal;
                break;
        }
    }

    $isOnSite = $calibration->calibration_location === 'on_site';

    $numberOfStaff = max(
        1,
        (int) ($calibration->number_of_staff ?? 1)
    );

    $travelDays = max(
        1,
        (int) ($calibration->travel_days ?? 1)
    );

    $siteDistance = max(
        0,
        (float) ($calibration->site_distance_km ?? 0)
    );

    // Preserve the quotation's original validity date.
    $validUntil = $quote->valid_until
        ?? $quote->created_at?->copy()->addDays(30);

    if (!$validUntil) {
        throw new \RuntimeException(
            'The quotation does not have a valid date.'
        );
    }

    // Preserve the existing payment token.
    // Do not create a new one during PDF generation.
    $paymentUrl = null;
    $paymentQrCode = null;

    if (
        $quote->is_external
        && $quote->status !== Quote::STATUS_CANCELLED
        && filled($quote->payment_token)
    ) {
        $paymentUrl = URL::temporarySignedRoute(
            'payments.quote.show',
            $validUntil->copy()->endOfDay(),
            [
                'quote' => $quote->id,
                'token' => $quote->payment_token,
            ]
        );

        $paymentQrSvg = QrCode::format('svg')
            ->size(180)
            ->margin(1)
            ->errorCorrection('M')
            ->generate($paymentUrl);

        $paymentQrCode = base64_encode($paymentQrSvg);
    }

    $accountReference = $this->mpesaService
        ->buildAccountReference($quote);

    $data = [
        'quote' => $quote,
        'calibration' => $calibration,
        'customer' => $customer,
        'items' => $items,
        'serviceTypes' => $serviceTypes,

        'equipmentRate' => $equipmentRate,
        'equipmentTypeName' => $equipmentTypeName,

        'subtotal' => $subtotal,
        'discountAmount' => $discountAmount,
        'discountPercentage' => $discountPercentage,
        'taxableAmount' => max(0, $subtotal - $discountAmount),
        'taxRate' => $taxRate,
        'tax' => $tax,
        'total' => $total,

        'quoteNumber' => $quote->quote_number,
        'date' => $quote->created_at?->format('Y-m-d')
            ?? now()->format('Y-m-d'),
        'validUntil' => $validUntil->format('Y-m-d'),

        'isOnSite' => $isOnSite,
        'perDiem' => $perDiem,
        'transport' => $transport,
        'otherCosts' => $otherCosts,
        'otherCostsDescription' => $calibration->other_costs_description,
        'numberOfStaff' => $numberOfStaff,
        'travelDays' => $travelDays,
        'siteDistance' => $siteDistance,

        'paymentUrl' => $paymentUrl,
        'paymentQrCode' => $paymentQrCode,
        'paymentStatus' => $quote->mpesa_payment_status,
        'amountPaid' => $amountPaid,
        'balanceDue' => $balanceDue,
        'accountReference' => $accountReference,
    ];

    $pdf = PDF::loadView(
        'documents.quotation',
        $data
    );

    $pdf->setPaper('a4', 'portrait');

    $pdf->setOptions([
        'defaultFont' => 'DejaVu Sans',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
    ]);

    $safeQuoteNumber = preg_replace(
        '/[^A-Za-z0-9._-]/',
        '_',
        (string) $quote->quote_number
    );

    $safeQuoteNumber = trim($safeQuoteNumber, '._-');

    if ($safeQuoteNumber === '') {
        $safeQuoteNumber = 'QT-' . $quote->id;
    }

    $pdfPath = 'quotations/quote-' . $quote->id . '/Quotation-'
        . $safeQuoteNumber . '.pdf';

    if (filled($quote->pdf_path) && Storage::disk('public')->exists($quote->pdf_path)) {
        return $pdf;
    }

    $stored = Storage::disk('public')->put(
        $pdfPath,
        $pdf->output()
    );

    if (!$stored) {
        throw new \RuntimeException(
            'Failed to save quotation PDF.'
        );
    }

    // Only update the PDF path, never quotation totals.
    $quote->forceFill([
        'pdf_path' => $pdfPath,
    ])->save();

    Log::info('Quotation PDF generated.', [
        'quote_id' => $quote->id,
        'quote_number' => $quote->quote_number,
        'tax_rate' => $taxRate,
        'tax_amount' => $tax,
        'total_amount' => $total,
        'pdf_path' => $pdfPath,
    ]);

    return $pdf;
}


/**
 * Generate a quote number with customer initials
 * Format: KP1/13B.1/QT/3/7-{initials}-{sequential}
 */
private function generateQuoteNumberWithCustomerInitials($customer)
{
    // Get customer initials
    $initials = $this->getCustomerInitials($customer);

    // Get the last quote number for this customer to increment
    $lastQuote = Quote::where('quote_number', 'LIKE', 'KP1/13B.1/QT/3/7-' . $initials . '-%')
        ->orderBy('id', 'desc')
        ->first();

    if ($lastQuote) {
        // Extract the number from the end
        preg_match('/-(\d+)$/', $lastQuote->quote_number, $matches);
        $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    return 'KP1/13B.1/QT/3/7-' . $initials . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

/**
 * Get customer initials from company name
 */
private function getCustomerInitials($customer)
{
    if (!$customer) {
        return 'CUST';
    }

    $companyName = $customer->company_name ?? '';

    // Clean and split company name
    $words = preg_split('/[\s\-]+/', trim($companyName));
    $initials = '';

    // Skip common words
    $skipWords = ['and', 'of', 'the', 'for', 'with', 'ltd', 'limited', 'company', 'co', 'inc', 'corporation', 'corp', 'plc', 'llc', 'holdings', 'group'];

    foreach ($words as $word) {
        $cleanWord = preg_replace('/[^a-zA-Z]/', '', $word);
        if (strlen($cleanWord) < 2) {
            continue;
        }
        if (in_array(strtolower($cleanWord), $skipWords)) {
            continue;
        }
        $initials .= strtoupper(substr($cleanWord, 0, 1));
    }

    // If no initials, use first 3 letters
    if (empty($initials)) {
        $cleanName = preg_replace('/[^a-zA-Z]/', '', $companyName);
        $initials = strtoupper(substr($cleanName, 0, 3));
    }

    // If still empty, use 'CUST'
    if (empty($initials)) {
        $initials = 'CUST';
    }

    return $initials;
}
    /**
     * Generate Delivery Note
     */
 public function generateDeliveryNote(
    Calibration $calibration,
    string $type = 'delivery'
) {
    /*
    |--------------------------------------------------------------------------
    | Validate document type
    |--------------------------------------------------------------------------
    */

    $type = strtolower($type);

    if (!in_array(
        $type,
        [
            'delivery',
            'collection',
        ],
        true
    )) {
        $type = 'delivery';
    }

    /*
    |--------------------------------------------------------------------------
    | Load required relationships
    |--------------------------------------------------------------------------
    */

    $calibration->loadMissing([
        'customer',
        'equipment',
        'performedBy',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Customer initials
    |--------------------------------------------------------------------------
    */

    $initials = $this->getCustomerInitials(
        $calibration->customer
    );

    $unique = strtoupper(
        substr(
            uniqid(),
            -6
        )
    );

    /*
    |--------------------------------------------------------------------------
    | Generate document number
    |--------------------------------------------------------------------------
    */

    $prefix =
        $type === 'delivery'
            ? 'DN'
            : 'CN';

    $deliveryNumber =
        'KP1/13B.1/'
        . $prefix
        . '/5/6-'
        . $initials
        . '-'
        . $unique;

    /*
    |--------------------------------------------------------------------------
    | Save document number
    |--------------------------------------------------------------------------
    */

    if ($type === 'delivery') {
        $calibration->delivery_number =
            $deliveryNumber;
    } else {
        $calibration->collection_number =
            $deliveryNumber;
    }

    $calibration->save();

    /*
    |--------------------------------------------------------------------------
    | Equipment type
    |--------------------------------------------------------------------------
    */

    $equipmentTypeName = 'N/A';

    if ($calibration->equipment_type_id) {
        $equipmentType =
            KplcEquipmentType::query()
                ->find(
                    $calibration->equipment_type_id
                );

        $equipmentTypeName =
            $equipmentType?->name
            ?? 'N/A';
    }

    /*
    |--------------------------------------------------------------------------
    | Service types
    |--------------------------------------------------------------------------
    */

    $serviceTypes = collect();

    if (!empty($calibration->service_types)) {
        $serviceTypeIds =
            is_array(
                $calibration->service_types
            )
                ? $calibration->service_types
                : json_decode(
                    $calibration->service_types,
                    true
                );

        if (
            is_array($serviceTypeIds)
            && !empty($serviceTypeIds)
        ) {
            $serviceTypes =
                KplcServiceType::query()
                    ->whereIn(
                        'id',
                        $serviceTypeIds
                    )
                    ->get();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve MCL representative
    |--------------------------------------------------------------------------
    |
    | Priority:
    |
    | 1. Technician assigned to the calibration
    | 2. Logged-in KPLC staff user
    |
    | A customer account must NEVER be displayed as the
    | MCL representative.
    |--------------------------------------------------------------------------
    */

    $loggedInUser = Auth::user();

    $mclRepresentative =
        $calibration->performedBy;

    if (
        !$mclRepresentative
        && $loggedInUser
        && !$loggedInUser->hasRole('customer')
    ) {
        $mclRepresentative =
            $loggedInUser;
    }

    /*
    |--------------------------------------------------------------------------
    | MCL signature
    |--------------------------------------------------------------------------
    */

    $mclSignature =
        $this->getUserSignatureBase64(
            $mclRepresentative
        );

    /*
    |--------------------------------------------------------------------------
    | Equipment
    |--------------------------------------------------------------------------
    */

    $equipment =
        $calibration->equipment;

    /*
    |--------------------------------------------------------------------------
    | Document date
    |--------------------------------------------------------------------------
    */

    $documentDate = now();

    /*
    |--------------------------------------------------------------------------
    | Blade data
    |--------------------------------------------------------------------------
    */

    $data = [
        'calibration' =>
            $calibration,

        'customer' =>
            $calibration->customer,

        'type' =>
            $type,

        'deliveryNumber' =>
            $deliveryNumber,

        'date' =>
            $documentDate->format(
                'Y-m-d'
            ),

        'status' =>
            $type === 'delivery'
                ? 'delivered'
                : 'collected',

        'equipment' => [
            'name' =>
                $calibration->equipment_name
                ?? $equipment?->name
                ?? 'N/A',

            'serial_number' =>
                $calibration->serial_number
                ?? $equipment?->serial_number
                ?? 'N/A',

            'manufacturer' =>
                $calibration->manufacturer
                ?? (
                    is_object(
                        $equipment?->manufacturer
                    )
                        ? $equipment?->manufacturer?->name
                        : $equipment?->manufacturer
                )
                ?? 'N/A',

            'model' =>
                $calibration->model
                ?? $equipment?->model
                ?? 'N/A',

            'type' =>
                $equipmentTypeName,

            'condition' =>
                $calibration->equipment_condition
                ?? $equipment?->condition
                ?? 'Good',

            'accuracy_class' =>
                $calibration->accuracy_class
                ?? $equipment?->accuracy_class
                ?? 'N/A',

            'ratings' =>
                $calibration->ratings
                ?? $equipment?->ratings
                ?? 'N/A',
        ],

        'serviceTypes' =>
            $serviceTypes,

        'remarks' =>
            $calibration->notes,

        /*
        |--------------------------------------------------------------------------
        | MCL representative
        |--------------------------------------------------------------------------
        */

        'mclRepresentative' =>
            $mclRepresentative,

        'mclSignature' =>
            $mclSignature,

        'signatureDate' =>
            $documentDate->format(
                'd/m/Y'
            ),

        /*
         * User who actually generated the document.
         * This is intentionally separate from the representative.
         */
        'generatedBy' =>
            $loggedInUser?->name
            ?? 'System',

        'pageNumber' =>
            1,

        'totalPages' =>
            1,
    ];

    /*
    |--------------------------------------------------------------------------
    | Render PDF
    |--------------------------------------------------------------------------
    */

    $pdf = PDF::loadView(
        'documents.delivery-note',
        $data
    );

    $pdf->setPaper(
        'a4',
        'portrait'
    );

    $pdf->setOptions([
        'defaultFont' =>
            'DejaVu Sans',

        'isHtml5ParserEnabled' =>
            true,

        'isRemoteEnabled' =>
            true,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Safe filename
    |--------------------------------------------------------------------------
    */

    $safeNumber = preg_replace(
        '/[^A-Za-z0-9._-]/',
        '_',
        (string) $deliveryNumber
    );

    $safeNumber = preg_replace(
        '/_+/',
        '_',
        $safeNumber
    );

    $safeNumber = trim(
        $safeNumber,
        '._-'
    );

    $documentName =
        $type === 'delivery'
            ? 'Delivery-Note'
            : 'Collection-Note';

    $fileName =
        $documentName
        . '-'
        . $safeNumber;

    /*
    |--------------------------------------------------------------------------
    | Storage folder
    |--------------------------------------------------------------------------
    */

    $folder =
        $type === 'delivery'
            ? 'delivery-notes'
            : 'collection-notes';

    /*
    |--------------------------------------------------------------------------
    | Save PDF
    |--------------------------------------------------------------------------
    */

    $pdfPath =
        $this->savePDF(
            $pdf,
            $fileName,
            $folder
        );

    /*
    |--------------------------------------------------------------------------
    | Verify stored file
    |--------------------------------------------------------------------------
    */

    if (
        !Storage::disk('public')
            ->exists($pdfPath)
    ) {
        throw new \RuntimeException(
            ucfirst($type)
            . ' note PDF was generated but could not be found in storage.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Save path on calibration
    |--------------------------------------------------------------------------
    */

    if ($type === 'delivery') {
        Calibration::query()
            ->whereKey(
                $calibration->id
            )
            ->update([
                'delivery_note_path' =>
                    $pdfPath,

                'updated_at' =>
                    now(),
            ]);
    } else {
        Calibration::query()
            ->whereKey(
                $calibration->id
            )
            ->update([
                'collection_note_path' =>
                    $pdfPath,

                'updated_at' =>
                    now(),
            ]);
    }

    $calibration->refresh();

    /*
    |--------------------------------------------------------------------------
    | Log
    |--------------------------------------------------------------------------
    */

    Log::info(
        ucfirst($type)
        . ' note PDF generated and stored.',
        [
            'calibration_id' =>
                $calibration->id,

            'document_number' =>
                $deliveryNumber,

            'mcl_representative_id' =>
                $mclRepresentative?->id,

            'mcl_representative_name' =>
                $mclRepresentative?->name,

            'generated_by_id' =>
                $loggedInUser?->id,

            'generated_by_name' =>
                $loggedInUser?->name,

            'pdf_path' =>
                $pdfPath,

            'file_exists' =>
                Storage::disk('public')
                    ->exists(
                        $pdfPath
                    ),
        ]
    );

    return $pdf;
}

/**
 * Check if delivery note already exists
 */
private function deliveryNoteExists(Calibration $calibration, $type = 'delivery')
{
    if ($type === 'delivery') {
        return !empty($calibration->delivery_number);
    } else {
        return !empty($calibration->collection_number);
    }
}

/**
 * Generate delivery note with sequential numbering instead of uniqid
 */
private function generateSequentialDeliveryNumber(Calibration $calibration, $type = 'delivery')
{
    $initials = $this->getCustomerInitials($calibration->customer);
    $prefix = $type === 'delivery' ? 'DN' : 'CN';
    $date = now()->format('ym'); // 2401

    $field = $type === 'delivery' ? 'delivery_number' : 'collection_number';
    $pattern = $prefix . '-' . $initials . '-' . $date . '-%';

    $last = Calibration::where($field, 'LIKE', $pattern)
        ->orderBy('id', 'desc')
        ->first();

    if ($last && $last->{$field}) {
        preg_match('/-(\d+)$/', $last->{$field}, $matches);
        $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    // Medium: DN-ABC-2401-0001
    return $prefix . '-' . $initials . '-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

/**
 * Get the full delivery note history for a customer
 */
public function getDeliveryHistory(Customer $customer)
{
    return Calibration::where('customer_id', $customer->id)
        ->whereNotNull('delivery_number')
        ->orderBy('created_at', 'desc')
        ->get(['id', 'delivery_number', 'collection_number', 'created_at']);
}
        /**
     * Save PDF to storage
     */
    public function savePDF($pdf, $filename, $path = 'documents')
    {
        $fullPath = $path . '/' . $filename . '.pdf';
        $content = $pdf->output();
        Storage::disk('public')->put($fullPath, $content);
        return $fullPath;
    }

    /**
     * Stream PDF to browser
     */
    public function streamPDF($pdf, $filename)
    {
        return $pdf->stream($filename . '.pdf');
    }

    /**
     * Download PDF
     */
    public function downloadPDF($pdf, $filename)
    {
        return $pdf->download($filename . '.pdf');
    }

    /**
 * Generate Job Card
 */
public function generateJobCard(JobCard $jobCard)
{
    /*
     * Load only relationships that are actually defined.
     *
     * Do not eager-load:
     * - equipment.manufacturer
     * - equipment.equipmentType
     *
     * unless those relationship methods exist in Equipment.php.
     */
    $jobCard->loadMissing([
        'calibration.customer',
        'calibration.equipment',
        'customerRequest.calibration.customer',
        'customerRequest.calibration.equipment',
        'allocatedTo',
        'allocatedBy',
    ]);

    /*
     * Resolve the linked calibration.
     */
    $calibration = $jobCard->calibration
        ?? $jobCard->customerRequest?->calibration;

    if (!$calibration) {
        throw new \RuntimeException(
            'No calibration is associated with this job card.'
        );
    }

    /*
     * Ensure the required calibration relationships are loaded.
     */
    $calibration->loadMissing([
        'customer',
        'equipment',
    ]);

    /*
     * Resolve the customer.
     */
    $customer = $calibration->customer
        ?? $jobCard->customer
        ?? null;

    /*
     * Resolve requested service types.
     */
    $serviceTypes = collect();

    if (!empty($calibration->service_types)) {
        $serviceTypes = $this->getServiceTypes(
            $calibration->service_types
        );
    }

    /*
     * Related equipment record.
     */
    $equipmentRecord = $calibration->equipment;

    /*
     * Resolve equipment type.
     *
     * Priority:
     * 1. equipment.type
     * 2. equipment.type_id lookup
     * 3. equipment.name as fallback
     *
     * The name fallback is useful where the equipment record itself
     * represents the equipment category.
     */
    $equipmentTypeName = null;

    if (!empty($equipmentRecord?->type)) {
        $equipmentTypeName = $equipmentRecord->type;
    }

    if (
        empty($equipmentTypeName)
        && !empty($equipmentRecord?->type_id)
    ) {
        $equipmentType = KplcEquipmentType::find(
            $equipmentRecord->type_id
        );

        $equipmentTypeName = $equipmentType?->name;
    }

    /*
     * Optional fallback to calibration equipment-type ID.
     */
    if (
        empty($equipmentTypeName)
        && !empty($calibration->equipment_type_id)
    ) {
        $equipmentType = KplcEquipmentType::find(
            $calibration->equipment_type_id
        );

        $equipmentTypeName = $equipmentType?->name;
    }

    $equipmentTypeName = $equipmentTypeName
        ?? $equipmentRecord?->name
        ?? 'N/A';

    /*
     * Assigned technician.
     */
    $technician = $jobCard->allocatedTo;

    /*
     * Find the latest checked certificate for this calibration.
     */
    $certificate = Certificate::query()
        ->with('checker')
        ->where('calibration_id', $calibration->id)
        ->whereNotNull('checked_by')
        ->whereNotNull('checked_at')
        ->latest('checked_at')
        ->latest('id')
        ->first();

    /*
     * Supervisor/checker.
     */
    $checkedBy = $certificate?->checker;

    /*
     * Resolve checked date safely.
     */
    $checkedAt = null;

    try {
        if (!empty($certificate?->checked_at)) {
            $checkedAt = $certificate->checked_at instanceof \Carbon\Carbon
                ? $certificate->checked_at
                : \Carbon\Carbon::parse(
                    $certificate->checked_at
                );
        }
    } catch (\Throwable $exception) {
        $checkedAt = null;

        \Illuminate\Support\Facades\Log::warning(
            'Unable to parse certificate checked date.',
            [
                'certificate_id' => $certificate?->id,
                'checked_at' => $certificate?->checked_at,
                'error' => $exception->getMessage(),
            ]
        );
    }

    /*
     * Resolve stored signatures.
     */
    $technicianSignature = $this->getUserSignatureBase64(
        $technician
    );

    $checkerSignature = $this->getUserSignatureBase64(
        $checkedBy
    );

    /*
     * Generate job-card number.
     */
    $jobCardNumber =
        $jobCard->job_card_number
        ?? $jobCard->job_number
        ?? 'KP1/13B.1//3/6/JC/' .
            str_pad(
                $jobCard->id,
                5,
                '0',
                STR_PAD_LEFT
            );

    /*
     * Resolve manufacturer safely.
     *
     * The manufacturer may be:
     * - a calibration string,
     * - an equipment string,
     * - or an Equipment relation in some installations.
     */
    $manufacturer = $calibration->manufacturer;

    if (empty($manufacturer)) {
        $equipmentManufacturer =
            $equipmentRecord?->manufacturer;

        if (is_object($equipmentManufacturer)) {
            $manufacturer =
                $equipmentManufacturer->name
                ?? null;
        } else {
            $manufacturer = $equipmentManufacturer;
        }
    }

    /*
     * Prepare predictable equipment values for the Blade.
     */
    $equipment = [
        /*
         * Equipment name comes from calibrations.
         */
        'equipment_name' =>
            $calibration->equipment_name
            ?? $equipmentRecord?->name
            ?? 'N/A',

        /*
         * Equipment type comes from the equipment record.
         */
        'equipment_type' =>
            $equipmentTypeName,

        'serial_number' =>
            $calibration->serial_number
            ?? $equipmentRecord?->serial_number
            ?? 'N/A',

        'manufacturer' =>
            $manufacturer
            ?? 'N/A',

        'model' =>
            $calibration->model
            ?? $equipmentRecord?->model
            ?? 'N/A',

        'accuracy_class' =>
            $calibration->accuracy_class
            ?? $equipmentRecord?->accuracy_class
            ?? 'N/A',

        'ratings' =>
            $calibration->ratings
            ?? $equipmentRecord?->ratings
            ?? 'N/A',

        'condition' =>
            $calibration->equipment_condition
            ?? $equipmentRecord?->condition
            ?? 'N/A',

        'last_calibration_date' =>
            $calibration->last_calibration_date
            ?? $equipmentRecord?->last_calibration_date
            ?? null,
    ];

    /*
     * Data passed to the PDF Blade.
     */
    $data = [
        'jobCard' => $jobCard,
        'calibration' => $calibration,
        'customer' => $customer,

        'jobCardNumber' => $jobCardNumber,
        'date' => now()->format('Y-m-d'),

        'technician' => $technician,
        'technicianSignature' => $technicianSignature,

        'certificate' => $certificate,
        'checkedBy' => $checkedBy,
        'checkedAt' => $checkedAt,
        'checkerSignature' => $checkerSignature,

        'serviceTypes' => $serviceTypes,
        'equipmentTypeName' => $equipmentTypeName,
        'equipment' => $equipment,
    ];

   $pdf = PDF::loadView(
    'documents.job-card',
    $data
);

$pdf->setPaper(
    'a4',
    'portrait'
);

$pdf->setOptions([
    'defaultFont' => 'DejaVu Sans',
    'isHtml5ParserEnabled' => true,
    'isRemoteEnabled' => true,
]);

/*
|--------------------------------------------------------------------------
| Build safe filename
|--------------------------------------------------------------------------
*/

$safeJobCardNumber = preg_replace(
    '/[^A-Za-z0-9._-]/',
    '_',
    (string) $jobCardNumber
);

$safeJobCardNumber = preg_replace(
    '/_+/',
    '_',
    $safeJobCardNumber
);

$safeJobCardNumber = trim(
    $safeJobCardNumber,
    '._-'
);

if ($safeJobCardNumber === '') {
    $safeJobCardNumber =
        'JC-'
        . str_pad(
            (string) $jobCard->id,
            5,
            '0',
            STR_PAD_LEFT
        );
}

$fileName =
    'Job-Card-'
    . $safeJobCardNumber
    . '.pdf';

$pdfPath =
    'job-cards/'
    . $fileName;

/*
|--------------------------------------------------------------------------
| Save PDF to public storage
|--------------------------------------------------------------------------
*/

$stored = Storage::disk('public')->put(
    $pdfPath,
    $pdf->output()
);

if (!$stored) {
    throw new \RuntimeException(
        'Failed to save job card PDF to storage.'
    );
}

/*
|--------------------------------------------------------------------------
| Verify stored file exists
|--------------------------------------------------------------------------
*/

if (
    !Storage::disk('public')->exists(
        $pdfPath
    )
) {
    throw new \RuntimeException(
        'Job card PDF was generated but could not be found in storage.'
    );
}

/*
|--------------------------------------------------------------------------
| Save path
|--------------------------------------------------------------------------
|
| If jobcard_form_path belongs to calibrations, save it there.
|--------------------------------------------------------------------------
*/

$calibration->update([
    'jobcard_form_path' => $pdfPath,
]);

/*
|--------------------------------------------------------------------------
| Optional: also store path on job_cards table
|--------------------------------------------------------------------------
|
| Only use this if the job_cards table has a pdf_path column.
|
*/

// $jobCard->update([
//     'pdf_path' => $pdfPath,
// ]);

/*
|--------------------------------------------------------------------------
| Log result
|--------------------------------------------------------------------------
*/

Log::info(
    'Job card PDF generated and stored.',
    [
        'job_card_id' => $jobCard->id,
        'calibration_id' => $calibration->id,
        'job_card_number' => $jobCardNumber,
        'pdf_path' => $pdfPath,
        'file_exists' =>
            Storage::disk('public')->exists(
                $pdfPath
            ),
    ]
);

return $pdf;
}

private function getUserSignatureBase64($user): ?string
{
    if (
        !$user
        || !(bool) $user->use_auto_signature
        || empty($user->signature_path)
    ) {
        return null;
    }

    $relativePath = ltrim(
        $user->signature_path,
        '/'
    );

    $possiblePaths = [
        storage_path(
            'app/public/' . $relativePath
        ),

        public_path(
            'storage/' . $relativePath
        ),

        public_path(
            $relativePath
        ),
    ];

    foreach ($possiblePaths as $signaturePath) {
        if (!file_exists($signaturePath)) {
            continue;
        }

        $mimeType = mime_content_type($signaturePath)
            ?: 'image/png';

        return
            'data:' . $mimeType . ';base64,' .
            base64_encode(
                file_get_contents($signaturePath)
            );
    }

    return null;
}
/**
 * Get service types with full details
 */
// private function getServiceTypes($serviceTypeIds)
// {
//     if (empty($serviceTypeIds)) {
//         return collect();
//     }

//     // Handle both string (JSON) and array
//     if (is_string($serviceTypeIds)) {
//         $ids = json_decode($serviceTypeIds, true);
//         if (!is_array($ids)) {
//             $ids = [];
//         }
//     } else {
//         $ids = $serviceTypeIds;
//     }

//     if (empty($ids)) {
//         return collect();
//     }

//     return \App\Models\KplcServiceType::whereIn('id', $ids)->get();
// }

/**
 * Get service types with full details
 */
private function getServiceTypes($serviceTypeIds)
{
    if (empty($serviceTypeIds)) {
        return collect();
    }

    // Handle both string (JSON) and array
    if (is_string($serviceTypeIds)) {
        $ids = json_decode($serviceTypeIds, true);
        if (!is_array($ids)) {
            $ids = [];
        }
    } else {
        $ids = $serviceTypeIds;
    }

    if (empty($ids)) {
        return collect();
    }

    return \App\Models\KplcServiceType::whereIn('id', $ids)->get();
}
}
