<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCalibrationRequest;
use App\Http\Requests\Admin\UpdateCalibrationRequest;
use App\Http\Requests\Admin\StoreTestResultRequest;
use App\Models\Calibration;
use App\Models\Customer;
use App\Models\CustomerRequest;
use App\Models\Equipment;
use App\Models\Certificate;
use App\Models\JobCard;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\ReassignmentHistory;
use App\Models\TestResult;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\DocumentGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\KplcServiceType;
use App\Models\KplcEquipmentType;
use Illuminate\Support\Facades\Log;
use App\Models\EquipmentType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CertificateService;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\JsonResponse;
use App\Mail\CalibrationQuotationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Services\CustomerVatService;


class CalibrationController extends Controller
{
     protected CertificateService $certificateService;

    public function __construct(
        CertificateService $certificateService
    ) {
        $this->certificateService = $certificateService;
    }

    /**
     * Display a listing of calibrations.
     */
  public function index(Request $request)
{
    $user = auth()->user();

    $query = Calibration::query()
        ->with([
            'customer',
            'equipment',
            'performedBy',
            'equipmentType',
            'quotes',
        ]);

    if ($user->hasRole('customer')) {
        $customer = $this->resolveCustomerForUser($user);
        $query->where('customer_id', $customer->id);
    }

    /*
     * Determine the effective technician filter before executing
     * the query.
     *
     * Rules:
     * - Explicit technician ID: show that technician.
     * - technician_id=all: show all technicians.
     * - No technician parameter and logged in as lab technician:
     *   show the logged-in technician's calibrations by default.
     * - Admin/supervisor without a parameter: show all.
     */
    if ($request->has('technician_id')) {
        $requestedTechnician = $request->input('technician_id');

        $technicianId = $requestedTechnician === 'all'
            || $requestedTechnician === ''
                ? null
                : (int) $requestedTechnician;
    } elseif ($user->hasRole('lab_technician')) {
        $technicianId = (int) $user->id;
    } else {
        $technicianId = null;
    }

    /*
     * Search.
     */
    if ($request->filled('search')) {
        $search = trim($request->input('search'));

        $query->where(function ($q) use ($search) {
            $q->whereHas('customer', function ($customerQuery) use ($search) {
                $customerQuery->where(
                    'company_name',
                    'LIKE',
                    "%{$search}%"
                );
            })
            ->orWhereHas('equipment', function ($equipmentQuery) use ($search) {
                $equipmentQuery->where(
                    'serial_number',
                    'LIKE',
                    "%{$search}%"
                );
            })
            ->orWhere(
                'equipment_name',
                'LIKE',
                "%{$search}%"
            )
            ->orWhere(
                'serial_number',
                'LIKE',
                "%{$search}%"
            );
        });
    }

    /*
     * Status filter.
     */
    if ($request->filled('status')) {
        $query->where(
            'status',
            $request->input('status')
        );
    }

    /*
     * Customer filter.
     */
    if ($request->filled('customer_id')) {
        $query->where(
            'customer_id',
            (int) $request->input('customer_id')
        );
    }

    /*
     * Technician filter.
     *
     * This must be applied before paginate().
     */
    if ($technicianId !== null) {
        $query->where(
            'performed_by',
            $technicianId
        );
    }

    /*
     * Service-type filter.
     */
    if ($request->filled('service_type_id')) {
        $query->whereJsonContains(
            'service_types',
            (int) $request->input('service_type_id')
        );
    }

    /*
     * Date filters.
     */
    if ($request->filled('date_from')) {
        $query->whereDate(
            'created_at',
            '>=',
            $request->input('date_from')
        );
    }

    if ($request->filled('date_to')) {
        $query->whereDate(
            'created_at',
            '<=',
            $request->input('date_to')
        );
    }

    /*
     * Execute the query only after all filters are applied.
     */
    $calibrations = $query
        ->latest('created_at')
        ->paginate(15)
        ->withQueryString();

    $customers = Customer::query()
        ->active()
        ->orderBy('company_name')
        ->pluck('company_name', 'id');

    $technicians = User::query()
        ->role('lab_technician')
        ->where('is_active', true)
        ->orderBy('name')
        ->pluck('name', 'id');

    $statuses = [
        'pending',
        'assigned',
        'in_progress',
        'testing',
        'completed',
        'certified',
        'rejected',
        'dispatched',
    ];

    $serviceTypes = KplcServiceType::query()
        ->active()
        ->orderBy('serv_code')
        ->get();

    return view(
        'admin.calibrations.index',
        compact(
            'calibrations',
            'customers',
            'technicians',
            'statuses',
            'serviceTypes',
            'technicianId'
        )
    );
}

    /**
     * Show the form for creating a new calibration.
     */
 public function create()
    {
        $user = Auth::user();
        $isCustomer = $user->hasRole('customer');

        $authenticatedCustomer = $isCustomer
            ? $this->resolveCustomerForUser($user)
            : null;

        /*
         * Customers must never receive other customers, internal MCL
         * equipment or staff lists in the response.
         */
        $customers = $isCustomer
            ? collect()
            : Customer::query()
                ->active()
                ->orderBy('company_name')
                ->pluck('company_name', 'id');

        $mclEquipment = collect();
        $technicians = collect();

        if (!$isCustomer) {
            $mclEquipment = Equipment::query()
                ->with(['category', 'manufacturer', 'type'])
                ->where('status', 'active')
                ->whereIn('category_id', function ($query) {
                    $query->select('id')
                        ->from('equipment_categories')
                        ->whereIn('name', [
                            'Calibration Equipment',
                            'Test Equipment',
                            'Reference Standards',
                        ]);
                })
                ->orderBy('name')
                ->get()
                ->mapWithKeys(function (Equipment $item): array {
                    return [
                        $item->id => [
                            'id' => $item->id,
                            'name' => $item->name,
                            'serial_number' => $item->serial_number,
                            'model' => $item->model,
                            'type' => $item->type?->name ?? '—',
                            'category' => $item->category?->name ?? '—',
                            'manufacturer' => $item->manufacturer?->name ?? '—',
                            'accuracy_class' => $item->accuracy_class ?? '—',
                            'ratings' => $item->ratings ?? '—',
                            'calibration_laboratory' =>
                                $item->calibration_laboratory ?? '—',
                            'last_calibration_date' =>
                                $item->last_calibration_date?->format('Y-m-d') ?? '—',
                            'next_calibration_date' =>
                                $item->next_calibration_date?->format('Y-m-d') ?? '—',
                            'status' => $item->status ?? 'active',
                            'condition' => $item->condition ?? 'good',
                        ],
                    ];
                });

            $technicians = User::query()
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', [
                        'lab_technician',
                        'supervisor',
                    ]);
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'department',
                    'staff_number',
                ]);
        }

        $serviceTypes = KplcServiceType::query()
            ->active()
            ->orderBy('serv_code')
            ->get();

        $equipmentTypes = KplcEquipmentType::query()
            ->orderBy('name')
            ->get();

        return view('admin.calibrations.create', [
            'customers' => $customers,
            'authenticatedCustomer' => $authenticatedCustomer,
            'isCustomer' => $isCustomer,
            'isKplcStaff' => !$isCustomer,
            'mclEquipment' => $mclEquipment,
            'technicians' => $technicians,
            'serviceTypes' => $serviceTypes,
            'equipmentTypes' => $equipmentTypes,
        ]);
    }

 /**
 * Store a newly generated quotation.
 *
 * Workflow:
 *
 * External:
 * pending -> quoted
 *
 * Internal KPLC:
 * pending -> quoted
 *
 * Sending/payment is handled separately.
 */
/**
 * Generate and save a draft quotation for a pending calibration.
 *
 * The quote's line items are stored in quotes.items (JSON).
 * On-site charges are excluded from the VAT base to match the existing UI.
 * Confirm this tax treatment with Finance before production deployment.
 */
public function storeQuotation(Request $request, $calibrationId)
{
    $user = Auth::user();

    abort_unless($user && !$user->hasRole('customer'), 403,
        'Customers cannot generate quotations.');

    // Add your staff-specific policy/permission check here if configured.
    $calibration = Calibration::query()
        ->with(['customer', 'equipmentType', 'items'])
        ->findOrFail($calibrationId);

    $customer = $calibration->customer;

    if (!$customer) {
        return back()->withInput()->with('error',
            'No customer is associated with this calibration.');
    }

    if ($calibration->status !== 'pending') {
        return back()->withInput()->with('error',
            'Quotation can only be generated for a pending calibration.');
    }

    if ($this->latestQuotation($calibration)) {
        return back()->withInput()->with('error',
            'A quotation has already been generated for this calibration.');
    }

    $validated = $request->validate([
        'items' => ['required', 'array', 'min:1'],
        'items.*.calibration_item_id' => ['nullable', 'integer', 'exists:calibration_items,id'],
        'items.*.service_type_id' => ['nullable', 'integer', 'exists:kplc_service_types,id'],
        'items.*.equipment_name' => ['nullable', 'string', 'max:255'],
        'items.*.serial_number' => ['nullable', 'string', 'max:255'],
        'items.*.description' => ['required', 'string', 'max:500'],
        'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:999999'],
        'items.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
        'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        'valid_until' => ['nullable', 'date', 'after:today'],
        'terms' => ['nullable', 'string'],
        'notes' => ['nullable', 'string'],
        'number_of_staff' => ['nullable', 'integer', 'min:1'],
        'travel_days' => ['nullable', 'integer', 'min:1'],
        'site_distance_km' => ['nullable', 'numeric', 'min:0'],
        'per_diem_rate' => ['nullable', 'numeric', 'min:0'],
        'transport_cost' => ['nullable', 'numeric', 'min:0'],
        'other_costs' => ['nullable', 'numeric', 'min:0'],
        'other_costs_description' => ['nullable', 'string', 'max:1000'],
    ]);

    $calibrationItems = $calibration->items->keyBy('id');
    $serviceTypes = KplcServiceType::query()->get()->keyBy('id');
    $serviceTypesByCode = $serviceTypes->keyBy('serv_code');
    $equipmentTypes = KplcEquipmentType::query()->get()->keyBy('id');

    // Service selections may contain numeric IDs or codes such as EM002.
    $normalizeServiceIds = static function ($raw) use ($serviceTypesByCode): array {
        if (is_string($raw)) {
            $raw = json_decode($raw, true) ?: [];
        }
        if (!is_array($raw)) {
            return [];
        }

        return collect($raw)->map(static function ($value) use ($serviceTypesByCode) {
            if (is_array($value)) {
                $value = $value['id'] ?? $value['serv_code'] ?? null;
            }
            if (is_numeric($value)) {
                return (int) $value;
            }
            return $serviceTypesByCode->get((string) $value)?->id;
        })->filter(static fn ($id) => $id !== null)
          ->map(static fn ($id) => (int) $id)
          ->unique()->values()->all();
    };
    $seenPairs = [];
    $quoteItems = [];
    $subtotal = 0.0;
    $errors = [];

    foreach ($validated['items'] as $index => $item) {
        $equipmentId = $item['calibration_item_id'] ?? null;
        $serviceId = $item['service_type_id'] ?? null;
        $equipment = $equipmentId !== null
            ? $calibrationItems->get((int) $equipmentId)
            : null;
        $service = $serviceId !== null
            ? $serviceTypes->get((int) $serviceId)
            : null;

        if ($equipmentId !== null && !$equipment) {
            $errors["items.{$index}.calibration_item_id"] =
                'The equipment item does not belong to this calibration.';
            continue;
        }

        if (($equipmentId === null) !== ($serviceId === null)) {
            $errors["items.{$index}.service_type_id"] =
                'Calibration charges require both an equipment item and a service type.';
            continue;
        }

        if ($serviceId !== null && !$service) {
            $errors["items.{$index}.service_type_id"] =
                'The selected service type could not be found.';
            continue;
        }

        if ($equipment && $service) {
            $requestedIds = $normalizeServiceIds($equipment->service_types ?? []);

            if (!in_array((int) $service->id, $requestedIds, true)) {
                $errors["items.{$index}.service_type_id"] =
                    'This service was not requested for the selected equipment.';
                continue;
            }

            $pair = $equipment->id . ':' . $service->id;
            if (isset($seenPairs[$pair])) {
                $errors["items.{$index}.service_type_id"] =
                    'This equipment/service combination has already been added.';
                continue;
            }
            $seenPairs[$pair] = true;
        }

        $quantity = (float) $item['quantity'];

        if ($equipment && $service) {
            $equipmentType = $equipmentTypes->get((int) $equipment->equipment_type_id);
            if (!$equipmentType) {
                $errors["items.{$index}.calibration_item_id"] =
                    'No equipment rate is configured for ' .
                    ($equipment->equipment_name ?: 'the selected equipment') . '.';
                continue;
            }
            // Do not trust browser-submitted prices for requested services.
            $unitPrice = (float) $equipmentType->rate;
        } else {
            // Extra/custom lines may be priced by authorized staff.
            if (!isset($item['unit_price']) || !is_numeric($item['unit_price'])) {
                $errors["items.{$index}.unit_price"] =
                    'A unit price is required for a custom charge.';
                continue;
            }
            $unitPrice = (float) $item['unit_price'];
        }
        $lineTotal = round($quantity * $unitPrice, 2);
        $subtotal = round($subtotal + $lineTotal, 2);

        $quoteItems[] = [
            'calibration_item_id' => $equipment?->id,
            'equipment_type_id' => $equipment?->equipment_type_id,
            'service_type_id' => $service?->id,
            'equipment_name' => $equipment?->equipment_name
                ?? ($item['equipment_name'] ?? 'Additional charge'),
            'serial_number' => $equipment?->serial_number
                ?? ($item['serial_number'] ?? null),
            'service_code' => $service?->serv_code,
            'service_name' => $service?->serv_name,
            'description' => $item['description'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $lineTotal,
            'type' => $equipment && $service ? 'calibration' : 'custom',
        ];
    }

    if ($errors) {
        throw \Illuminate\Validation\ValidationException::withMessages($errors);
    }

    // Do not silently omit any service requested by the customer.
    foreach ($calibration->items as $equipment) {
        foreach ($normalizeServiceIds($equipment->service_types ?? []) as $serviceId) {
            if (!isset($seenPairs[$equipment->id . ':' . $serviceId])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'items' => "Missing quotation line for {$equipment->equipment_name} "
                        . "(serial {$equipment->serial_number}), service ID {$serviceId}.",
                ]);
            }
        }
    }

    $isInternal = $this->isInternalKplcCalibration($calibration);
    $isOnsite = $calibration->calibration_location === 'on_site';
    $numberOfStaff = $isOnsite ? (int) ($validated['number_of_staff'] ?? 1) : 1;
    $travelDays = $isOnsite ? (int) ($validated['travel_days'] ?? 1) : 1;
    $siteDistance = $isOnsite ? (float) ($validated['site_distance_km'] ?? 0) : 0.0;
    $perDiemRate = $isOnsite ? (float) ($validated['per_diem_rate'] ?? 0) : 0.0;
    $transportTotal = $isOnsite ? round((float) ($validated['transport_cost'] ?? 0), 2) : 0.0;
    $otherTotal = $isOnsite ? round((float) ($validated['other_costs'] ?? 0), 2) : 0.0;
    $perDiemTotal = $isOnsite
        ? round($numberOfStaff * $travelDays * $perDiemRate, 2)
        : 0.0;

    if ($perDiemTotal > 0) {
        $quoteItems[] = [
            'description' => "Per Diem ({$numberOfStaff} staff × {$travelDays} day(s))",
            'quantity' => 1, 'unit_price' => $perDiemTotal,
            'total' => $perDiemTotal, 'type' => 'onsite_perdiem',
        ];
    }
    if ($transportTotal > 0) {
        $quoteItems[] = [
            'description' => "Transport (Site distance: {$siteDistance} KM)",
            'quantity' => 1, 'unit_price' => $transportTotal,
            'total' => $transportTotal, 'type' => 'onsite_transport',
        ];
    }
    if ($otherTotal > 0) {
        $quoteItems[] = [
            'description' => ($validated['other_costs_description'] ?? null) ?: 'Other costs',
            'quantity' => 1, 'unit_price' => $otherTotal,
            'total' => $otherTotal, 'type' => 'onsite_other',
        ];
    }

    $discountPercentage = (float) ($validated['discount_percentage'] ?? 0);
    $discountAmount = round($subtotal * $discountPercentage / 100, 2);
    $afterDiscount = round($subtotal - $discountAmount, 2);
    $taxRate = max(0, min(100, (float) app(CustomerVatService::class)->rate($customer)));
    $taxAmount = round($afterDiscount * $taxRate / 100, 2);
    $onsiteCosts = round($perDiemTotal + $transportTotal + $otherTotal, 2);
    $totalAmount = round($afterDiscount + $taxAmount + $onsiteCosts, 2);

    try {
        DB::transaction(function () use (
            $calibration, $customer, $isInternal, $quoteItems, $subtotal,
            $taxRate, $taxAmount, $discountAmount, $discountPercentage,
            $totalAmount, $validated, $numberOfStaff, $travelDays,
            $siteDistance, $perDiemTotal, $transportTotal, $otherTotal
        ) {
            // Serialize competing quotation-generation requests for this calibration.
            $locked = Calibration::query()->whereKey($calibration->id)
                ->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending' ||
                Quote::query()->where('calibration_id', $locked->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'items' => 'A quotation has already been generated or the calibration is no longer pending.',
                ]);
            }

            $quoteNumber = $this->generateQuoteNumber($customer);

            Quote::query()->create([
                'calibration_id' => $locked->id,
                'customer_id' => $locked->customer_id,
                'created_by' => Auth::id(),
                'quote_number' => $quoteNumber,
                'quote_reference' => 'REF-' . strtoupper(Str::random(10)),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'tax_rate' => $taxRate,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $discountPercentage,
                'total_amount' => $totalAmount,
                'amount_paid' => 0,
                'balance_due' => $isInternal ? 0 : $totalAmount,
                'status' => Quote::STATUS_DRAFT,
                'is_external' => !$isInternal,
                'currency' => 'KES',
                'items' => $quoteItems,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? ($isInternal
                    ? 'Internal KPLC calibration costing.'
                    : 'Payment is required before calibration/testing commences.'),
                'valid_until' => $validated['valid_until'] ?? now()->addDays(30),
                'payment_token' => $isInternal ? null : Str::random(64),
                'mpesa_payment_status' => 'unpaid',
                'sent_at' => null,
            ]);

            $updates = [
                'status' => 'quoted',
                'amount_due' => $totalAmount,
                'amount_paid' => 0,
                'requires_payment' => !$isInternal,
                'is_external' => !$isInternal,
                'number_of_staff' => $numberOfStaff,
                'travel_days' => $travelDays,
                'site_distance_km' => $siteDistance,
                'per_diem_cost' => $perDiemTotal,
                'transport_cost' => $transportTotal,
                'other_costs' => $otherTotal,
                'other_costs_description' => $validated['other_costs_description'] ?? null,
            ];
            if (!$isInternal) {
                $updates['payment_status'] = 'pending';
            }
            $locked->update($updates);
            AuditLogService::log('generated_quotation', $locked);
        }, 3);

        return redirect()
            ->route('calibrations.show', $calibration)
            ->with('success', $isInternal
                ? 'Internal KPLC quotation generated successfully. You may now assign a technician.'
                : 'Quotation generated successfully. Send it to the customer to proceed to payment.');
    } catch (\Illuminate\Validation\ValidationException $exception) {
        throw $exception;
    } catch (\Throwable $exception) {
        Log::error('Quotation generation failed.', [
            'calibration_id' => $calibration->id,
            'user_id' => Auth::id(),
            'exception' => $exception,
        ]);

        return back()->withInput()->with('error',
            'The quotation could not be generated. Please contact the administrator if the problem persists.');
    }
}



public function store(StoreCalibrationRequest $request)
{
    $user = Auth::user();
    $isCustomer = $user->hasRole('customer');
    $validated = $request->validated();

    $customer = $isCustomer
        ? $this->resolveCustomerForUser($user)
        : Customer::query()->findOrFail((int) $validated['customer_id']);

    $items = $validated['items'];

    /*
    |--------------------------------------------------------------------------
    | Duplicate serial number checks (per item + across all items)
    |--------------------------------------------------------------------------
    */
    $serialsInSubmission = collect($items)
        ->pluck('serial_number')
        ->map(fn ($s) => trim((string) $s))
        ->filter()
        ->values();

    // Duplicate within the same submission
    if ($serialsInSubmission->count() !== $serialsInSubmission->unique()->count()) {
        return back()
            ->withInput()
            ->withErrors([
                'items' => 'The same serial number appears more than once in this submission.',
            ]);
    }

    // Duplicate against existing active calibrations
    $activeSerial = Calibration::query()
        ->whereIn('serial_number', $serialsInSubmission->all())
        ->whereIn('status', [
            'pending', 'paid', 'quoted', 'assigned', 'in_progress', 'testing',
        ])
        ->pluck('serial_number')
        ->first();

    if ($activeSerial) {
        return back()
            ->withInput()
            ->withErrors([
                'items' => "The equipment with serial number {$activeSerial} already has an active calibration request.",
            ]);
    }

    try {
        $calibration = DB::transaction(function () use (
            $validated,
            $customer,
            $items,
            $isCustomer
        ): Calibration {

            /*
            |--------------------------------------------------------------------------
            | Pull the first item for the header (legacy compatibility)
            |--------------------------------------------------------------------------
            */
            $firstItem = $items[0];

            $headerServiceTypeIds = collect($firstItem['service_types'] ?? [])
                ->filter(fn ($v) => is_numeric($v))
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values()
                ->all();

            $headerValidityRequired = $firstItem['validity_period_required'] ?? 'no';
            $headerValidityPeriod   = $headerValidityRequired === 'yes'
                ? ($firstItem['validity_period'] ?? null)
                : null;
            $headerValidityDate     = $headerValidityRequired === 'yes'
                ? ($firstItem['validity_period_date'] ?? null)
                : null;

            /*
            |--------------------------------------------------------------------------
            | On-site costing
            |--------------------------------------------------------------------------
            */
            $numberOfStaff         = 1;
            $travelDays            = 1;
            $siteDistanceKm        = null;
            $perDiemCost           = null;
            $transportCost         = null;
            $otherCosts            = null;
            $otherCostsDescription = null;

            $calibrationLocation = $isCustomer
                ? 'laboratory'
                : ($validated['calibration_location'] ?? 'laboratory');

            if (!$isCustomer && $calibrationLocation === 'on_site') {
                $numberOfStaff  = max((int) ($validated['number_of_staff'] ?? 1), 1);
                $travelDays     = max((int) ($validated['travel_days'] ?? 1), 1);
                $siteDistanceKm = max((float) ($validated['site_distance_km'] ?? 0), 0);
                $perDiemRate    = max((float) ($validated['per_diem_rate'] ?? 0), 0);
                $transportRate  = max((float) ($validated['transport_rate'] ?? 0), 0);

                $perDiemCost   = $numberOfStaff * $travelDays * $perDiemRate;
                $transportCost = $siteDistanceKm * $transportRate * 2;
                $otherCosts    = max((float) ($validated['other_costs'] ?? 0), 0);
                $otherCostsDescription = $validated['other_costs_description'] ?? null;
            }

            /*
            |--------------------------------------------------------------------------
            | Header attributes (calibrations table)
            |--------------------------------------------------------------------------
            */
            $attributes = [
                'customer_id'      => $customer->id,
                'equipment_name'   => $firstItem['equipment_name'],
                'serial_number'    => $firstItem['serial_number'],
                'manufacturer'     => $firstItem['manufacturer'] ?? null,
                'model'            => $firstItem['model'] ?? null,
                'accuracy_class'   => $firstItem['accuracy_class'] ?? null,
                'ratings'          => $firstItem['ratings'] ?? null,
                'equipment_type_id'=> $firstItem['equipment_type_id'] ?? null,
                'equipment_condition' => $firstItem['equipment_condition'],
                'last_calibration_date' => $firstItem['last_calibration_date'] ?? null,
                'service_types'    => $headerServiceTypeIds,

                // legacy verification fields — sync from first item
                'verification_of_item'    => $firstItem['verification_of_item'] ?? null,
                'review_of_service'       => $firstItem['review_of_service'] ?? null,
                'validity_period_required'=> $headerValidityRequired,
                'validity_period'         => $headerValidityPeriod,
                'validity_period_date'    => $headerValidityDate,

                'notes'  => $validated['notes'] ?? null,
                'status' => 'pending',

                /* Internal allocation fields */
                'equipment_id' => $isCustomer ? null : ($validated['equipment_id'] ?? null),
                'scheduled_date' => $isCustomer ? null : ($validated['scheduled_date'] ?? null),
                'performed_by'   => $isCustomer ? null : ($validated['performed_by'] ?? null),
                'calibration_location' => $calibrationLocation,
                'priority' => $isCustomer ? 'normal' : ($validated['priority'] ?? 'normal'),
                'test_parameters' => $isCustomer ? null : ($validated['test_parameters'] ?? null),

                /* On-site costing */
                'per_diem_cost'           => $perDiemCost,
                'transport_cost'          => $transportCost,
                'other_costs'             => $otherCosts,
                'other_costs_description' => $otherCostsDescription,
                'number_of_staff'         => $numberOfStaff,
                'travel_days'             => $travelDays,
                'site_distance_km'        => $siteDistanceKm,
            ];

            if (Schema::hasColumn('calibrations', 'created_by')) {
                $attributes['created_by'] = Auth::id();
            }
            if (Schema::hasColumn('calibrations', 'request_source')) {
                $attributes['request_source'] = $isCustomer ? 'customer_portal' : 'kplc_staff';
            }

            /*
            |--------------------------------------------------------------------------
            | Create header
            |--------------------------------------------------------------------------
            */
            $calibration = Calibration::query()->create($attributes);

            /*
            |--------------------------------------------------------------------------
            | Create items (full per-item fields)
            |--------------------------------------------------------------------------
            */
            foreach ($items as $index => $item) {
                $itemServiceTypes = collect($item['service_types'] ?? [])
                    ->filter(fn ($id) => is_numeric($id))
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                $calibration->items()->create([
                    'equipment_type_id'        => $item['equipment_type_id'] ?? null,
                    'equipment_name'           => $item['equipment_name'],
                    'serial_number'            => $item['serial_number'],
                    'manufacturer'             => $item['manufacturer'] ?? null,
                    'model'                    => $item['model'] ?? null,
                    'accuracy_class'           => $item['accuracy_class'] ?? null,
                    'ratings'                  => $item['ratings'] ?? null,
                    'equipment_condition'      => $item['equipment_condition'],
                    'last_calibration_date'    => $item['last_calibration_date'] ?? null,
                    'service_types'            => $itemServiceTypes,
                    'status'                   => 'pending',
                    'position'                 => $index,

                    'verification_of_item'     => $item['verification_of_item'] ?? null,
                    'review_of_service'        => $item['review_of_service'] ?? null,
                    'validity_period_required' => $item['validity_period_required'] ?? 'no',
                    'validity_period'          => $item['validity_period'] ?? null,
                    'validity_period_date'     => $item['validity_period_date'] ?? null,

                    'laboratory_equipment_id'  => $item['laboratory_equipment_id'] ?? null,
                ]);
            }

            return $calibration;
        });

        AuditLogService::log(
            $isCustomer ? 'customer_requested_calibration' : 'created_calibration',
            $calibration
        );

        $message = $isCustomer
            ? 'Your calibration request has been submitted successfully. Please await an official quotation from Kenya Power Meter Central Laboratory before making payment or delivering the equipment.'
            : 'Calibration created successfully.';

        return redirect()
            ->route('calibrations.show', $calibration)
            ->with('success', $message);

    } catch (\Throwable $exception) {
        Log::error('Failed to create calibration.', [
            'user_id' => Auth::id(),
            'is_customer' => $isCustomer,
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return back()
            ->withInput()
            ->with('error', 'The calibration request could not be saved. Please review the form and try again.');
    }
}
    /**
     * Resolve the customer record belonging to a customer-role user.
     */
    private function resolveCustomerForUser(User $user): Customer
    {
        $query = Customer::query();

        if (Schema::hasColumn('customers', 'user_id')) {
            $customer = (clone $query)
                ->where('user_id', $user->id)
                ->first();

            if ($customer) {
                return $customer;
            }
        }

        if (Schema::hasColumn('customers', 'email')) {
            $customer = (clone $query)
                ->whereRaw('LOWER(email) = ?', [
                    strtolower((string) $user->email),
                ])
                ->first();

            if ($customer) {
                return $customer;
            }
        }

        $customer = (clone $query)
            ->where('company_name', $user->name)
            ->first();

        abort_unless(
            $customer,
            403,
            'Your login is not linked to a customer account. '
            . 'Please contact the Meter Central Laboratory administrator.'
        );

        return $customer;
    }

    /**
     * Display the specified calibration.
     */
    // public function show(Calibration $calibration)
    // {
    //     $calibration->load(['customer', 'equipment', 'performedBy', 'testResults', 'certificate']);

    // $quote = \App\Models\Quote::where('calibration_id', $calibration->id)->first();

    //     return view('admin.calibrations.show', compact('calibration', 'quote'));
    // }

 public function show($id)
{
    $technicians = User::role(['lab_technician', 'technician'])
        ->orderBy('name')
        ->get();

    $calibration = Calibration::query()
        ->with([
            'customer',
            'equipment',
            'items',
            'testResults',
            'performedBy',
        ])
        ->findOrFail($id);

    $user = Auth::user();

    if ($user->hasRole('customer')) {
        $customer = $this->resolveCustomerForUser($user);
        abort_unless((int) $calibration->customer_id === (int) $customer->id, 403);
    }

    $quote = Quote::where('calibration_id', $calibration->id)->latest('id')->first();

    return view('admin.calibrations.show', compact('calibration', 'technicians', 'quote'));
}

    /**
     * Show the form for editing the specified calibration.
     */
    public function edit(Calibration $calibration)
{
    $customers = Customer::pluck('company_name', 'id');

    // Get technicians (only active users with technician roles)
    $technicians = User::whereHas('roles', function($q) {
        $q->whereIn('name', ['technician', 'lab_technician', 'supervisor']);
    })->where('is_active', 1) // Only active users
      ->get(['id', 'name', 'department', 'staff_number', 'email']);

    // Get reassignment history with relationships
    $reassignmentHistory = $calibration->reassignmentHistories()
        ->with(['fromTechnician', 'toTechnician', 'reassignedBy'])
        ->orderBy('reassigned_at', 'desc')
        ->get();

    // Get equipment types from the kplc_equipment_types table
    $kplcEquipmentTypes = DB::table('kplc_equipment_types')->get();
    $kplcServiceTypes = DB::table('kplc_service_types')->where('status', 1)->get();

    // Get MCL equipment
    $mclEquipment = Equipment::where('status', 'active')
        ->select('id', 'name', 'serial_number', 'model')
        ->get()
        ->mapWithKeys(function($item) {
            $label = $item->name;
            if ($item->serial_number) {
                $label .= ' (SN: ' . $item->serial_number . ')';
            }
            if ($item->model) {
                $label .= ' - ' . $item->model;
            }
            return [$item->id => ['name' => $label, 'serial_number' => $item->serial_number, 'model' => $item->model]];
        })
        ->toArray();

    // Get quotation for this calibration using the quote model
    $quotation = Quote::where('calibration_id', $calibration->id)
        ->where('status', 'approved')
        ->first();

    // If no approved quotation, try to get the latest quotation
    if (!$quotation) {
        $quotation = Quote::where('calibration_id', $calibration->id)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    // Pass quotation to view as a property
    $calibration->quote = $quotation;

    // If quotation exists and amount_due is not set, use quotation amount
    if ($quotation && !$calibration->amount_due) {
        $calibration->amount_due = $quotation->total_amount;
    }

    return view('admin.calibrations.edit', compact(
        'calibration',
        'customers',
        'technicians',
        'kplcEquipmentTypes',
        'kplcServiceTypes',
        'mclEquipment',
        'reassignmentHistory' // Pass reassignment history to view
    ));
}

    /**
     * Update the specified calibration.
     */

public function update(
    Request $request,
    Calibration $calibration
): RedirectResponse {
    $user = Auth::user();

    abort_unless(
        $user && !$user->hasRole('customer'),
        403,
        'Customers are not authorised to edit calibration workflow records.'
    );

    $calibration->loadMissing(['customer', 'performedBy', 'items']);

    $isInternal = $this->isInternalKplcCalibration($calibration);
    $quotation = $this->latestQuotation($calibration);
    $quotationGenerated = $quotation !== null;
    $paymentCompleted = $isInternal ? true : $this->quotationPaymentCompleted($calibration, $quotation);

    /*
    |--------------------------------------------------------------------------
    | Validation — includes ALL per-item fields sent by the blade
    |--------------------------------------------------------------------------
    */
    $validated = $request->validate([
        'customer_id' => ['required', 'integer', 'exists:customers,id'],

        'items' => ['required', 'array', 'min:1'],
        'items.*.id' => ['required', 'integer', 'exists:calibration_items,id'],
        'items.*.equipment_name' => ['required', 'string', 'max:255'],
        'items.*.serial_number' => ['required', 'string', 'max:255'],
        'items.*.manufacturer' => ['nullable', 'string', 'max:255'],
        'items.*.model' => ['nullable', 'string', 'max:255'],
        'items.*.accuracy_class' => ['nullable', 'string', 'max:50'],
        'items.*.ratings' => ['nullable', 'string', 'max:255'],
        'items.*.equipment_type_id' => ['nullable', 'integer', 'exists:kplc_equipment_types,id'],
        'items.*.equipment_condition' => ['required', 'in:Excellent,Good,Fair,Poor,Damaged'],
        'items.*.last_calibration_date' => ['nullable', 'date'],
        'items.*.service_types' => ['nullable', 'array'],
        'items.*.service_types.*' => ['integer', 'exists:kplc_service_types,id'],

        // Per-item verification
        'items.*.verification_of_item' => ['nullable', 'string', 'max:2000'],
        'items.*.review_of_service' => ['nullable', 'string', 'max:3000'],
        'items.*.validity_period_required' => ['nullable', 'in:yes,no'],
        'items.*.validity_period' => ['nullable', 'string', 'max:100'],
        'items.*.validity_period_date' => ['nullable', 'date'],

        // Per-item laboratory equipment
        'items.*.laboratory_equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],

        // Calibration level
        'equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],
        'scheduled_date' => ['nullable', 'date'],
        'performed_by' => ['nullable', 'integer', 'exists:users,id'],
        'calibration_location' => ['required', 'in:laboratory,on_site'],
        'priority' => ['nullable', 'in:normal,high,urgent'],
        'test_parameters' => ['nullable', 'array'],
        'status' => ['required', 'in:pending,quoted,pending_payment,paid,assigned,in_progress,testing,completed,certified,rejected'],
        'reassignment_reason' => ['nullable', 'string', 'min:10', 'max:500'],
    ]);

    /*
    |--------------------------------------------------------------------------
    | Technician & workflow checks (unchanged)
    |--------------------------------------------------------------------------
    */
    $currentTechnicianId = filled($calibration->performed_by) ? (int) $calibration->performed_by : null;
    $newTechnicianId = filled($validated['performed_by'] ?? null) ? (int) $validated['performed_by'] : null;
    $reassignmentReason = trim((string) ($validated['reassignment_reason'] ?? ''));
    $technicianChanged = $currentTechnicianId !== $newTechnicianId;

    $requestedStatus = $request->input('status', $calibration->status);
    $finalStatus = (string) $calibration->status;

    if ($isInternal) {
        $allowed = ['pending', 'quoted', 'assigned', 'in_progress', 'testing', 'completed', 'certified', 'rejected'];
        if (!in_array($requestedStatus, $allowed, true)) {
            return back()->withInput()->with('error', 'Invalid status for internal KPLC calibration.');
        }
        if (in_array($requestedStatus, ['quoted', 'assigned', 'in_progress', 'testing', 'completed', 'certified'], true) && !$quotationGenerated) {
            return back()->withInput()->with('error', 'Generate the internal quotation first.');
        }
        $finalStatus = $requestedStatus;
    }

    if (!$isInternal) {
        $allowed = ['pending', 'quoted', 'pending_payment', 'paid', 'assigned', 'in_progress', 'testing', 'completed', 'certified', 'rejected'];
        if (!in_array($requestedStatus, $allowed, true)) {
            return back()->withInput()->with('error', 'Invalid status for external customer.');
        }
        if ($newTechnicianId !== null && !$paymentCompleted) {
            return back()->withInput()->with('error', 'External customer payment must be completed before assigning a technician.');
        }
        $finalStatus = $requestedStatus;
    }

    if ($newTechnicianId !== null) {
        $newTechnician = User::findOrFail($newTechnicianId);
        if (!$newTechnician->hasAnyRole(['lab_technician', 'technician'])) {
            return back()->withInput()->with('error', 'The selected user is not an authorised laboratory technician.');
        }
    }

    if ($technicianChanged && $currentTechnicianId !== null && $newTechnicianId !== null && $reassignmentReason === '') {
        return back()->withErrors(['reassignment_reason' => 'A reassignment reason is required when changing the technician.'])->withInput();
    }

    DB::beginTransaction();

    try {
        $oldValues = $calibration->getOriginal();

        /*
        |--------------------------------------------------------------------------
        | 1. Update header — including syncing verification from first item
        |--------------------------------------------------------------------------
        */
        $firstItem = $validated['items'][0];

        $headerValidityRequired = $firstItem['validity_period_required'] ?? 'no';

        $calibration->update([
            'customer_id'      => $validated['customer_id'],
            'equipment_id'     => $validated['equipment_id'] ?? null,
            'scheduled_date'   => $validated['scheduled_date'] ?? null,
            'calibration_location' => $validated['calibration_location'],
            'priority'         => $validated['priority'] ?? 'normal',
            'test_parameters'  => collect($validated['test_parameters'] ?? [])
                ->filter(fn ($p) => filled($p['name'] ?? null) || filled($p['nominal_value'] ?? null))
                ->values()
                ->all(),
            'status'         => $finalStatus,
            'is_external'    => !$isInternal,
            'requires_payment' => !$isInternal,
            'payment_status' => $isInternal ? null : ($paymentCompleted ? 'paid' : ($calibration->amount_paid > 0 ? 'partial' : 'pending')),

            // Sync legacy header verification from first item
            'verification_of_item'    => $firstItem['verification_of_item'] ?? null,
            'review_of_service'       => $firstItem['review_of_service'] ?? null,
            'validity_period_required'=> $headerValidityRequired,
            'validity_period'         => $headerValidityRequired === 'yes' ? ($firstItem['validity_period'] ?? null) : null,
            'validity_period_date'    => $headerValidityRequired === 'yes' ? ($firstItem['validity_period_date'] ?? null) : null,

            // Sync header equipment (for reports reading the header)
            'equipment_name'          => $firstItem['equipment_name'],
            'serial_number'           => $firstItem['serial_number'],
            'manufacturer'            => $firstItem['manufacturer'] ?? null,
            'model'                   => $firstItem['model'] ?? null,
            'accuracy_class'          => $firstItem['accuracy_class'] ?? null,
            'ratings'                 => $firstItem['ratings'] ?? null,
            'equipment_type_id'       => $firstItem['equipment_type_id'] ?? null,
            'equipment_condition'     => $firstItem['equipment_condition'],
            'last_calibration_date'   => $firstItem['last_calibration_date'] ?? null,
            'service_types'           => collect($firstItem['service_types'] ?? [])
                ->filter(fn ($v) => is_numeric($v))
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values()
                ->all(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. Update items — ALL per-item fields
        |--------------------------------------------------------------------------
        */
        foreach ($validated['items'] as $itemData) {
            $item = \App\Models\CalibrationItem::findOrFail($itemData['id']);

            $services = collect($itemData['service_types'] ?? [])
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $item->update([
                'equipment_type_id'        => $itemData['equipment_type_id'] ?? null,
                'equipment_name'           => $itemData['equipment_name'],
                'serial_number'            => $itemData['serial_number'],
                'manufacturer'             => $itemData['manufacturer'] ?? null,
                'model'                    => $itemData['model'] ?? null,
                'accuracy_class'           => $itemData['accuracy_class'] ?? null,
                'ratings'                  => $itemData['ratings'] ?? null,
                'equipment_condition'      => $itemData['equipment_condition'],
                'last_calibration_date'    => $itemData['last_calibration_date'] ?? null,
                'service_types'            => $services,

                'verification_of_item'     => $itemData['verification_of_item'] ?? null,
                'review_of_service'        => $itemData['review_of_service'] ?? null,
                'validity_period_required' => $itemData['validity_period_required'] ?? 'no',
                'validity_period'          => $itemData['validity_period'] ?? null,
                'validity_period_date'     => $itemData['validity_period_date'] ?? null,

                'laboratory_equipment_id'  => $itemData['laboratory_equipment_id'] ?? null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Technician assignment (unchanged)
        |--------------------------------------------------------------------------
        */
        if ($technicianChanged) {
            if ($newTechnicianId === null) {
                $statusAfterRemoval = $isInternal
                    ? ($quotationGenerated ? 'quoted' : 'pending')
                    : ($paymentCompleted ? 'paid' : ($quotationGenerated ? 'pending_payment' : 'pending'));
                $calibration->update([
                    'performed_by'     => null,
                    'assigned_at'      => null,
                    'assignment_notes' => null,
                    'status'           => $statusAfterRemoval,
                ]);
            } else {
                $calibration->update([
                    'performed_by' => $newTechnicianId,
                    'assigned_at'  => now(),
                    'status'       => 'assigned',
                ]);

                if ($currentTechnicianId !== null) {
                    try {
                        ReassignmentHistory::create([
                            'calibration_id'      => $calibration->id,
                            'from_technician_id'  => $currentTechnicianId,
                            'to_technician_id'    => $newTechnicianId,
                            'reassigned_by'       => Auth::id(),
                            'reason'              => $reassignmentReason,
                            'reassigned_at'       => now(),
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Could not save reassignment history', ['error' => $e->getMessage()]);
                    }
                }

                $this->handleJobCardAssignment($calibration, $newTechnicianId);
            }
        }

        AuditLogService::log('updated_calibration', $calibration, $oldValues, $calibration->fresh()->getAttributes());
        DB::commit();

        return redirect()
            ->route('calibrations.show', $calibration)
            ->with('success', 'Calibration updated successfully.');

    } catch (\Throwable $exception) {
        DB::rollBack();
        Log::error('Calibration update failed.', [
            'calibration_id' => $calibration->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
        return back()->withInput()->with('error', 'Failed to update calibration: ' . $exception->getMessage());
    }
}

/**
 * Handle job card assignment when technician is assigned
 */
private function handleJobCardAssignment($calibration, $technicianId)
{
    // Find or create job card
    $jobCard = JobCard::where('calibration_id', $calibration->id)->first();

    if (!$jobCard) {
        // Create job card if it doesn't exist
        $jobCardData = [
            'calibration_id' => $calibration->id,
            'customer_id' => $calibration->customer_id,
            'customer_request_id' => $this->getCustomerRequestId($calibration),
            'job_card_number' => JobCard::generateJobCardNumber(),
            'job_number' => JobCard::generateJobCardNumber(),
            'status' => 'allocated',
            'description' => 'Job card for calibration #' . $calibration->id,
            'allocated_to' => $technicianId,
            'allocated_by' => auth()->id(),
            'assigned_at' => now(),
        ];

        $jobCard = JobCard::create($jobCardData);
    } else {
        // Update existing job card using the method
        $jobCard->markAsAllocated($technicianId);
    }

    return $jobCard;
}

/**
 * Get customer request ID for calibration
 */
private function getCustomerRequestId($calibration)
{
    $request = CustomerRequest::where('calibration_id', $calibration->id)->first();
    return $request ? $request->id : null;
}

    /**
     * Approve a calibration.
     */
//    public function approve(
//     Calibration $calibration
// ): RedirectResponse {
//     DB::beginTransaction();

//     try {
//         $calibration->update([
//             'status' => 'completed',
//             'completed_at' => $calibration->completed_at ?? now(),
//         ]);

//        // Check if a certificate already exists
// $certificate = Certificate::where(
//     'calibration_id',
//     $calibration->id
// )->first();

// if (!$certificate) {

//     // Generate using the existing CertificateService
//     $certificate = $this->certificateService
//         ->generateFromCalibration($calibration);

//     // Generate the PDF
//     $this->certificateService
//         ->generatePDF($certificate);

//     // Update certificate status
//     $certificate->update([
//         'status' => CertificateController::STATUS_GENERATED,
//         'generated_by' => auth()->id(),
//         'generated_at' => now(),
//     ]);
// }

//         if ($certificate->status === 'draft') {
//             $certificate->update([
//                 'status' => 'generated',
//             ]);
//         }

//         DB::commit();

//         return redirect()
//             ->route('certificates.show', $certificate)
//             ->with(
//                 'success',
//                 'Calibration approved and certificate generated.'
//             );

//     } catch (\Throwable $e) {
//         DB::rollBack();

//         Log::error(
//             'Calibration approval failed',
//             [
//                 'calibration_id' => $calibration->id,
//                 'error' => $e->getMessage(),
//             ]
//         );

//         return back()->with(
//             'error',
//             'Failed to approve calibration: '
//                 . $e->getMessage()
//         );
//     }
// }

    /**
     * Reject a calibration.
     */
    public function reject(Request $request, Calibration $calibration)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $calibration->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason,
            ]);

            DB::commit();

            return redirect()->route('calibrations.index')
                ->with('warning', 'Calibration rejected: ' . $request->reason);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject calibration: ' . $e->getMessage());
        }
    }

    /**
     * Add test results to calibration.
     */
    public function storeTestResult(StoreTestResultRequest $request, Calibration $calibration)
{
$user = $request->user();


if (
    !$user
    || !$user->can('perform_calibrations')
) {

    return back()->with(
        'error',
        'You do not have permission to perform calibration tests.'
    );
}


if (
    empty($calibration->performed_by)

    || (int) $calibration->performed_by
        !== (int) $user->id
) {

    return back()->with(
        'error',
        'Only the assigned technician can enter test results for this calibration.'
    );
}

if (
    !$calibration
        ->hasEnvironmentalConditions()
) {

    return back()
        ->withInput()
        ->with(
            'error',
            'Record Test Parameters / Environmental Conditions before entering test results.'
        );
}

    DB::beginTransaction();

    try {
        $nominal = (float) $request->nominal_value;
        $measured = (float) $request->measured_value;
        $toleranceMin = (float) $request->tolerance_min;
        $toleranceMax = (float) $request->tolerance_max;

        // Check if measured value is within tolerance range (relative to nominal)
        // For example: nominal=230, tolerance_min=-5, tolerance_max=5
        // Range: 230-5=225 to 230+5=235
        $isWithinTolerance = ($measured >= ($nominal + $toleranceMin)) && ($measured <= ($nominal + $toleranceMax));

        // Calculate error percentage
        $errorPercentage = $nominal != 0 ? (($measured - $nominal) / $nominal) * 100 : 0;

        $result = TestResult::create([
            'calibration_id' => $calibration->id,
            'parameter' => $request->parameter,
            'nominal_value' => $nominal,
            'measured_value' => $measured,
            'tolerance_min' => $toleranceMin,
            'tolerance_max' => $toleranceMax,
            'unit' => $request->unit,
            'is_within_tolerance' => $isWithinTolerance,
            'error_percentage' => $errorPercentage,
            'notes' => $request->notes,
        ]);

        // Get existing test results
        $testResults = $calibration->test_results ?? [];
        $testResults[] = [
            'parameter' => $request->parameter,
            'nominal_value' => $nominal,
            'measured_value' => $measured,
            'tolerance_min' => $toleranceMin,
            'tolerance_max' => $toleranceMax,
            'unit' => $request->unit,
            'is_within_tolerance' => $isWithinTolerance,
            'error_percentage' => $errorPercentage,
            'notes' => $request->notes,
        ];

        // Update calibration status to testing and save test results
        $calibration->update([
            'status' => 'testing',
            'test_results' => $testResults,
        ]);

        DB::commit();

        return redirect()->route('calibrations.show', $calibration)
            ->with('success', 'Test result added successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to add test result: ' . $e->getMessage());
        return back()->with('error', 'Failed to add test result: ' . $e->getMessage())->withInput();
    }
}

    /**
     * Remove the specified calibration.
     */
    public function destroy(Calibration $calibration)
    {
        try {
            $calibration->delete();

            return redirect()->route('calibrations.index')
                ->with('success', 'Calibration deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete calibration: ' . $e->getMessage());
        }
    }

   /**
     * Show bulk upload form
     */
//     public function bulkUpload()
// {
//     $customers = Customer::where('is_active', 1)->pluck('company_name', 'id');
//     $equipmentTypes = KplcEquipmentType::orderBy('name')->get();
//     $serviceTypes = KplcServiceType::where('status', 1)->get();

//     return view('admin.calibrations.bulk-upload', compact('customers', 'equipmentTypes', 'serviceTypes'));
// }

/**
 * Show bulk upload form
 */
public function bulkUpload()
{
    // Get active customers for dropdown
    $customers = Customer::where('is_active', 1)
        ->orderBy('company_name')
        ->pluck('company_name', 'id');

    // Get all equipment types for dropdown
    $equipmentTypes = KplcEquipmentType::orderBy('name')
        ->get(['id', 'name', 'rate']);

    // Get all service types for dropdown
    $serviceTypes = KplcServiceType::where('status', 1)
        ->orderBy('serv_code')
        ->get(['id', 'serv_code', 'serv_name', 'serv_method']);

    // Log for debugging
    \Log::info('Bulk upload page loaded', [
        'customers_count' => $customers->count(),
        'equipment_types_count' => $equipmentTypes->count(),
        'service_types_count' => $serviceTypes->count()
    ]);

    return view('admin.calibrations.bulk-upload', compact('customers', 'equipmentTypes', 'serviceTypes'));
}

 /**
 * Process bulk upload with batch support
 */
/**
 * Process bulk upload with batch support
 */
public function processBulkUpload(Request $request)
{
    // Verify user is authenticated
    if (!auth()->check()) {
        return response()->json([
            'success' => false,
            'message' => 'Session expired. Please refresh the page and try again.'
        ], 401);
    }

    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'csv_file' => 'required|file|mimes:csv,txt|max:20480',
        'batch_size' => 'nullable|integer|min:100|max:10000',
        'service_type_ids' => 'nullable|array',
        'service_type_ids.*' => 'exists:kplc_service_types,id',
        'equipment_type_id' => 'nullable|exists:kplc_equipment_types,id',
    ]);

    // Refresh session to prevent timeout
    session()->put('last_activity', time());

    $batchSize = $request->batch_size ?? 1000;
    $customerId = $request->customer_id;
    $serviceTypeIds = $request->service_type_ids ?? [];
    $defaultEquipmentTypeId = $request->equipment_type_id;

    // Parse CSV file
    $file = $request->file('csv_file');
    $csvData = array_map('str_getcsv', file($file->getPathname()));

    if (empty($csvData) || count($csvData) < 2) {
        return redirect()->back()->with('error', 'CSV file is empty or invalid.');
    }

    // Get headers - keep them as is (case sensitive for display)
    $rawHeaders = array_map('trim', $csvData[0]);

    // Log the headers for debugging
    \Log::info('CSV Headers:', ['headers' => $rawHeaders]);

    // Create a mapping of lowercase header -> original index
    $headerIndexMap = [];
    foreach ($rawHeaders as $index => $header) {
        $normalized = strtolower(trim($header));
        $headerIndexMap[$normalized] = $index;
    }

    // Define required columns (in lowercase for comparison)
    $requiredColumns = ['equipment_name', 'serial_number'];

    // Check if required columns exist (case-insensitive)
    $missingColumns = [];
    foreach ($requiredColumns as $required) {
        $found = false;
        foreach ($headerIndexMap as $key => $index) {
            if ($key === $required) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $missingColumns[] = $required;
        }
    }

    // If missing columns, try to find them with different variations
    if (!empty($missingColumns)) {
        // Try common variations
        $variations = [
            'equipment_name' => ['equipmentname', 'equipment name', 'name'],
            'serial_number' => ['serialnumber', 'serial number', 's/n', 'sn'],
        ];

        foreach ($missingColumns as $key => $column) {
            foreach ($variations[$column] ?? [] as $variation) {
                if (isset($headerIndexMap[$variation])) {
                    // Found a match, remove from missing
                    unset($missingColumns[$key]);
                    // Add the actual column to the map
                    $headerIndexMap[$column] = $headerIndexMap[$variation];
                    break;
                }
            }
        }
    }

    if (!empty($missingColumns)) {
        return redirect()->back()->with('error',
            'Missing required column(s): ' . implode(', ', $missingColumns) .
            '. Found: ' . implode(', ', $rawHeaders)
        );
    }

    // Process data in batches
    $totalRows = count($csvData) - 1;
    $processed = 0;
    $errors = [];
    $successCount = 0;
    $calibrationIds = [];
    $batchNumber = 1;

    // Create a batch ID for tracking
    $batchId = 'BATCH-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

    // Store progress in session for tracking
    session()->put('bulk_upload_progress', [
        'batch_id' => $batchId,
        'total' => $totalRows,
        'processed' => 0,
        'success' => 0,
        'errors' => [],
        'status' => 'processing'
    ]);

    DB::beginTransaction();

    try {
        // Process rows in batches
        for ($i = 1; $i < count($csvData); $i += $batchSize) {
            $batchRows = array_slice($csvData, $i, $batchSize);
            $batchData = [];

            foreach ($batchRows as $rowIndex => $row) {
                // Extract data using the header index map (case-insensitive)
                $equipmentName = $this->getValueFromRow($row, $headerIndexMap, 'equipment_name');
                $serialNumber = $this->getValueFromRow($row, $headerIndexMap, 'serial_number');
                $manufacturer = $this->getValueFromRow($row, $headerIndexMap, 'manufacturer');
                $model = $this->getValueFromRow($row, $headerIndexMap, 'model');
                $accuracyClass = $this->getValueFromRow($row, $headerIndexMap, 'accuracy_class');
                $ratings = $this->getValueFromRow($row, $headerIndexMap, 'ratings');
                $equipmentType = $this->getValueFromRow($row, $headerIndexMap, 'equipment_type');
                $equipmentCondition = $this->getValueFromRow($row, $headerIndexMap, 'equipment_condition');
                $lastCalibrationDate = $this->getValueFromRow($row, $headerIndexMap, 'last_calibration_date');
                $serviceType = $this->getValueFromRow($row, $headerIndexMap, 'service_type');
                $notes = $this->getValueFromRow($row, $headerIndexMap, 'notes');

                // Skip empty rows
                if (empty($equipmentName) && empty($serialNumber)) {
                    continue;
                }

                // Validate required fields
                if (empty($equipmentName)) {
                    $errors[] = "Row " . ($i + $rowIndex) . ": Equipment Name is required.";
                    continue;
                }

                if (empty($serialNumber)) {
                    $errors[] = "Row " . ($i + $rowIndex) . ": Serial Number is required.";
                    continue;
                }

                // Build row data
                $rowData = [
                    'equipment_name' => $equipmentName,
                    'serial_number' => $serialNumber,
                    'manufacturer' => $manufacturer,
                    'model' => $model,
                    'accuracy_class' => $accuracyClass,
                    'ratings' => $ratings,
                    'equipment_type' => $equipmentType,
                    'equipment_condition' => $equipmentCondition,
                    'last_calibration_date' => $lastCalibrationDate,
                    'service_type' => $serviceType,
                    'notes' => $notes,
                ];

                $batchData[] = $rowData;
            }

            if (empty($batchData)) {
                continue;
            }

            // Process each batch
            $result = $this->processBatch($batchData, $customerId, $serviceTypeIds, $defaultEquipmentTypeId, $batchId, $batchNumber);

            $successCount += $result['success'];
            $calibrationIds = array_merge($calibrationIds, $result['calibration_ids']);
            $errors = array_merge($errors, $result['errors']);
            $processed += count($batchData);
            $batchNumber++;

            // Update session progress
            session()->put('bulk_upload_progress', [
                'batch_id' => $batchId,
                'total' => $totalRows,
                'processed' => $processed,
                'success' => $successCount,
                'errors' => $errors,
                'status' => 'processing'
            ]);

            // Refresh session to prevent timeout
            session()->put('last_activity', time());

            // Log batch progress
            \Log::info("Batch {$batchNumber} processed", [
                'batch_id' => $batchId,
                'processed' => $processed,
                'total' => $totalRows,
                'success' => $result['success']
            ]);
        }

        DB::commit();

        // Update session with completion status
        session()->put('bulk_upload_progress', [
            'batch_id' => $batchId,
            'total' => $totalRows,
            'processed' => $processed,
            'success' => $successCount,
            'errors' => $errors,
            'status' => 'completed'
        ]);

        // Generate job cards for the batch
        $this->generateBatchJobCards($calibrationIds, $customerId);

        // Summary message
        $message = "Successfully created {$successCount} calibrations from {$processed} equipment items.";
        if (!empty($errors)) {
            $message .= " " . count($errors) . " errors encountered.";
            session()->flash('import_errors', array_slice($errors, 0, 50));
        }

        if ($successCount > 0) {
            return redirect()->route('calibrations.bulk-upload.summary', ['batch_id' => $batchId])
                ->with('success', $message)
                ->with('calibration_ids', $calibrationIds)
                ->with('batch_id', $batchId);
        }

        return redirect()->back()->with('warning', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Bulk upload failed: ' . $e->getMessage(), [
            'customer_id' => $customerId,
            'trace' => $e->getTraceAsString()
        ]);

        session()->put('bulk_upload_progress', [
            'batch_id' => $batchId,
            'status' => 'failed',
            'error' => $e->getMessage()
        ]);

        return redirect()->back()->with('error', 'Bulk upload failed: ' . $e->getMessage());
    }
}

/**
 * Helper function to get value from CSV row using case-insensitive column names
 */
private function getValueFromRow($row, $headerIndexMap, $columnName)
{
    $normalized = strtolower(trim($columnName));

    // Try exact match
    if (isset($headerIndexMap[$normalized])) {
        $index = $headerIndexMap[$normalized];
        return isset($row[$index]) ? trim($row[$index]) : '';
    }

    // Try variations
    $variations = [
        'equipment_name' => ['equipmentname', 'equipment name', 'name'],
        'serial_number' => ['serialnumber', 'serial number', 's/n', 'sn'],
        'manufacturer' => ['manufacturer', 'make', 'brand'],
        'accuracy_class' => ['accuracyclass', 'accuracy class', 'class'],
        'equipment_type' => ['equipmenttype', 'equipment type', 'type'],
        'equipment_condition' => ['equipmentcondition', 'equipment condition', 'condition'],
        'last_calibration_date' => ['lastcalibrationdate', 'last calibration date', 'calibration date'],
        'service_type' => ['servicetype', 'service type', 'service'],
    ];

    foreach ($variations[$columnName] ?? [] as $variation) {
        if (isset($headerIndexMap[$variation])) {
            $index = $headerIndexMap[$variation];
            return isset($row[$index]) ? trim($row[$index]) : '';
        }
    }

    return '';
}
/**
 * Helper function to get column data case-insensitively
 */
private function getColumnData($row, $normalizedHeaders, $columnName)
{
    $normalized = strtolower(trim($columnName));
    if (isset($normalizedHeaders[$normalized])) {
        $index = $normalizedHeaders[$normalized];
        return isset($row[$index]) ? trim($row[$index]) : '';
    }
    return '';
}

/**
 * Show bulk upload summary
 */
public function bulkUploadSummary(Request $request)
{
    $batchId = $request->batch_id;

    if (!$batchId) {
        return redirect()->route('calibrations.bulk-upload')
            ->with('error', 'No batch ID provided.');
    }

    $calibrations = Calibration::where('batch_id', $batchId)
        ->with(['customer', 'equipmentType'])
        ->get();

    if ($calibrations->isEmpty()) {
        return redirect()->route('calibrations.bulk-upload')
            ->with('error', 'Batch not found.');
    }

    $total = $calibrations->count();
    $pending = $calibrations->where('status', 'pending')->count();
    $quoted = $calibrations->where('status', 'quoted')->count();
    $paid = $calibrations->where('status', 'paid')->count();
    $assigned = $calibrations->where('status', 'assigned')->count();
    $inProgress = $calibrations->where('status', 'in_progress')->count();
    $testing = $calibrations->where('status', 'testing')->count();
    $completed = $calibrations->where('status', 'completed')->count();
    $certified = $calibrations->where('status', 'certified')->count();
    $rejected = $calibrations->where('status', 'rejected')->count();

    $customer = $calibrations->first()?->customer;

    // Get technicians for bulk actions
    $technicians = User::whereHas('roles', function($q) {
        $q->whereIn('name', ['technician', 'lab_technician', 'supervisor']);
    })->pluck('name', 'id');

    return view('calibrations.bulk-summary', compact(
        'calibrations',
        'batchId',
        'total',
        'pending',
        'quoted',
        'paid',
        'assigned',
        'inProgress',
        'testing',
        'completed',
        'certified',
        'rejected',
        'customer',
        'technicians'
    ));
}

/**
 * Bulk generate quotes for a batch
 */
public function bulkGenerateQuotes($batchId)
{
    // Get all calibrations in the batch
    $calibrations = Calibration::where('batch_id', $batchId)
        ->whereIn('status', ['pending', 'quoted'])
        ->get();

    if ($calibrations->isEmpty()) {
        $allCalibrations = Calibration::where('batch_id', $batchId)->get();
        $statusList = $allCalibrations->pluck('status')->unique()->implode(', ');
        return redirect()->back()
            ->with('error', "No pending or quoted calibrations found. Current statuses: {$statusList}");
    }

    DB::beginTransaction();

    try {
        $count = 0;
        $errors = [];
        $totalAmount = 0;
        $items = [];
        $customerId = $calibrations->first()->customer_id;

        // Build a single quote with all equipment
        foreach ($calibrations as $calibration) {
            try {
                $equipmentTypeRate = $calibration->equipment_type_rate ?? 0;

                $items[] = [
                    'calibration_id' => $calibration->id,
                    'equipment_name' => $calibration->equipment_name,
                    'serial_number' => $calibration->serial_number,
                    'manufacturer' => $calibration->manufacturer,
                    'model' => $calibration->model,
                    'rate' => $equipmentTypeRate,
                    'amount' => $equipmentTypeRate,
                    'description' => "Calibration of {$calibration->equipment_name} (SN: {$calibration->serial_number})",
                ];

                $totalAmount += $equipmentTypeRate;
                $count++;

            } catch (\Exception $e) {
                $errors[] = "Failed for calibration #{$calibration->id}: " . $e->getMessage();
            }
        }

        if ($count == 0) {
            return redirect()->back()->with('error', 'No valid calibrations to quote.');
        }

        // Calculate taxes
       // Use the Lab Manager-approved customer VAT rate.
$customer = Customer::findOrFail($customerId);

$taxRate = max(
    0,
    min(
        100,
        (float) app(CustomerVatService::class)->rate($customer)
    )
);

$totalAmount = round($totalAmount, 2);

$taxAmount = round(
    $totalAmount * $taxRate / 100,
    2
);

$grandTotal = round(
    $totalAmount + $taxAmount,
    2
);

        // Generate a single quote number
        $quoteNumber = $this->generateQuoteNumber(
    Customer::findOrFail($customerId)
);

        // Create a single quotation
        $quote = quote::create([
            'calibration_id' => null,
            'customer_id' => $customerId,
            'created_by' => auth()->id(),
            'quote_number' => $quoteNumber,
            'quote_reference' => 'BATCH-' . $batchId,
            'subtotal' => $totalAmount,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'discount_amount' => 0,
            'discount_percentage' => 0,
            'total_amount' => $grandTotal,
            'items' => json_encode($items),
            'notes' => "Batch quotation for {$count} equipment items. Batch ID: {$batchId}",
            'terms' => 'Payment due within 30 days.',
            'valid_until' => now()->addDays(30),
            'status' => 'sent',
            'is_external' => true,
            'currency' => 'KES',
            'amount_paid' => 0,
            'balance_due' => $grandTotal,
            'sent_at' => now(),
        ]);

        // Update all calibrations
        foreach ($calibrations as $calibration) {
            $calibration->update([
                'status' => 'quoted',
                'quote_sent_at' => now(),
                'payment_status' => 'pending',
                'amount_due' => $calibration->equipment_type_rate ?? 0,
                'requires_payment' => true,
            ]);
        }

        DB::commit();

        // Store the quote ID in session for access
        session()->put('last_batch_quote_id', $quote->id);

        return redirect()->route('calibrations.batch.details', $batchId)
            ->with('success', "Batch quotation generated successfully! Quote #: {$quoteNumber}")
            ->with('quote_id', $quote->id)
            ->with('quote_number', $quoteNumber);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Bulk quote generation failed: ' . $e->getMessage(), [
            'batch_id' => $batchId,
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()->with('error', 'Failed to generate batch quotation: ' . $e->getMessage());
    }
}
/**
 * Process a single batch of equipment
 */
private function processBatch($batchData, $customerId, $serviceTypeIds, $defaultEquipmentTypeId, $batchId, $batchNumber)
{
    $success = 0;
    $errors = [];
    $calibrationIds = [];

    foreach ($batchData as $index => $row) {
        try {
            // Determine equipment type
            $equipmentTypeId = $defaultEquipmentTypeId;

            // Try to find equipment type by name if provided
            if (!empty($row['equipment_type'])) {
                $type = KplcEquipmentType::where('name', 'LIKE', '%' . $row['equipment_type'] . '%')->first();
                if ($type) {
                    $equipmentTypeId = $type->id;
                }
            }

            // Determine equipment type from name if not set
            if (!$equipmentTypeId) {
                $equipmentTypeId = $this->detectEquipmentType($row['equipment_name']);
            }

            // Get service types
            $serviceTypeIdsToUse = $serviceTypeIds;
            if (!empty($row['service_type'])) {
                $service = KplcServiceType::where('serv_code', $row['service_type'])->first();
                if ($service) {
                    $serviceTypeIdsToUse = [$service->id];
                }
            }

            // Create calibration - MAKE SURE batch_id IS INCLUDED
            $calibration = Calibration::create([
                'customer_id' => $customerId,
                'equipment_name' => $row['equipment_name'],
                'serial_number' => $row['serial_number'],
                'manufacturer' => $row['manufacturer'] ?? null,
                'model' => $row['model'] ?? null,
                'accuracy_class' => $row['accuracy_class'] ?? null,
                'ratings' => $row['ratings'] ?? null,
                'equipment_type_id' => $equipmentTypeId,
                'equipment_condition' => $row['equipment_condition'] ?? 'Good',
                'last_calibration_date' => !empty($row['last_calibration_date']) ? $row['last_calibration_date'] : null,
                'notes' => $row['notes'] ?? null,
                'status' => 'pending',
                'is_external' => true,
                'requires_payment' => true,
                'calibration_location' => 'laboratory',
                'priority' => 'normal',
                'service_types' => json_encode($serviceTypeIdsToUse),
                'batch_id' => $batchId,  // CRITICAL: This must be set
                'batch_number' => $batchNumber,
                'created_by' => auth()->id(),
            ]);

            $calibrationIds[] = $calibration->id;
            $success++;

        } catch (\Exception $e) {
            $rowNumber = $index + 1;
            $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            \Log::error("Bulk upload row {$rowNumber} failed: " . $e->getMessage(), [
                'row' => $row,
                'customer_id' => $customerId
            ]);
        }
    }

    return [
        'success' => $success,
        'errors' => $errors,
        'calibration_ids' => $calibrationIds,
    ];
}
/**
 * Get bulk upload progress
 */
public function getBulkUploadProgress()
{
    $progress = session('bulk_upload_progress', [
        'status' => 'idle',
        'processed' => 0,
        'total' => 0,
        'success' => 0,
        'errors' => []
    ]);

    return response()->json($progress);
}

    /**
     * Download sample CSV template
     */
    public function downloadTemplate()
{
    $headers = [
        'Equipment Name',
        'Serial Number',
        'Manufacturer',
        'Model',
        'Accuracy Class',
        'Ratings',
        'Equipment Type',
        'Equipment Condition',
        'Last Calibration Date',
        'Service Type',
        'Notes'
    ];

    $sampleRows = [
        ['Single Phase Meter', 'SN2024001', 'Hexing', 'HXE-320', '1.0', '230V, 10-100A, 50Hz', '1 Phase watt hour meter', 'Good', '2024-01-15', 'EM001', 'Routine calibration'],
        ['3 Phase Meter', 'SN2024002', 'Landis+Gyr', 'MK6E', '0.5S', '230V, 10-100A, 50Hz', '3 Phase Whole current watt hour Meter', 'Excellent', '2024-02-01', 'EM001', 'New equipment'],
        ['Current Transformer', 'SN2024003', 'Ritz', 'KSO 1811', '0.2', '100/5A, 50Hz', 'Current Transformer', 'Good', '', 'ET001', 'Calibration required'],
    ];

    $filename = 'bulk_calibration_template.csv';
    $handle = fopen('php://output', 'w');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Write headers
    fputcsv($handle, $headers);

    // Write sample rows
    foreach ($sampleRows as $row) {
        fputcsv($handle, $row);
    }

    fclose($handle);
    exit;
}
/**
 * Assign or reassign calibration to a technician.
 *
 * INTERNAL:
 * quoted -> assigned
 *
 * EXTERNAL:
 * paid -> assigned
 */
public function assign(
    Request $request,
    Calibration $calibration
): RedirectResponse {
    $user = Auth::user();

    abort_unless(
        $user
            && !$user->hasRole('customer')
            && $user->can(
                'assign_calibrations'
            ),
        403,
        'You are not authorised to assign calibrations.'
    );

    $validated = $request->validate([
        'technician_id' => [
            'required',
            'integer',
            'exists:users,id',
        ],

        'assignment_notes' => [
            'nullable',
            'string',
            'max:500',
        ],
    ]);

    $calibration->load([
        'customer',
        'performedBy',
    ]);

    $quotation =
        $this->latestQuotation(
            $calibration
        );

    if (!$quotation) {
        return back()->with(
            'error',
            'Generate the quotation before assigning a technician.'
        );
    }

    $isInternal =
        $this->isInternalKplcCalibration(
            $calibration
        );

    /*
    |--------------------------------------------------------------------------
    | Workflow gate
    |--------------------------------------------------------------------------
    */

    if ($isInternal) {
        if (
            !in_array(
                $calibration->status,
                [
                    'quoted',
                    'assigned',
                ],
                true
            )
        ) {
            return back()->with(
                'error',
                'Internal KPLC work can only be assigned after quotation generation.'
            );
        }
    } else {
        if (
            !$this->quotationPaymentCompleted(
                $calibration,
                $quotation
            )
        ) {
            return back()->with(
                'error',
                'External customer payment must be completed before assigning a technician.'
            );
        }

        if (
            !in_array(
                $calibration->status,
                [
                    'paid',
                    'assigned',
                ],
                true
            )
        ) {
            return back()->with(
                'error',
                'External calibration must be fully paid before technician assignment.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Technician validation
    |--------------------------------------------------------------------------
    */

    $technician = User::query()
        ->findOrFail(
            $validated[
                'technician_id'
            ]
        );

    if (
        !$technician->hasAnyRole([
            'lab_technician',
            'technician',
        ])
    ) {
        return back()->with(
            'error',
            'The selected user is not an authorised laboratory technician.'
        );
    }

    $oldValues =
        $calibration->getOriginal();

    $isReassignment =
        filled(
            $calibration->performed_by
        )
        && (int) $calibration->performed_by
            !== (int) $technician->id;

    DB::beginTransaction();

    try {
        $calibration->update([
            'performed_by' =>
                $technician->id,

            'status' =>
                'assigned',

            'assigned_at' =>
                now(),

            'assignment_notes' =>
                $validated[
                    'assignment_notes'
                ]
                ?? null,
        ]);

        AuditLogService::log(
            $isReassignment
                ? 'reassigned_technician'
                : 'assigned_technician',
            $calibration,
            $oldValues,
            $calibration->getAttributes()
        );

        /*
        |--------------------------------------------------------------------------
        | Job card
        |--------------------------------------------------------------------------
        */

        $jobCard =
            JobCard::query()
                ->where(
                    'calibration_id',
                    $calibration->id
                )
                ->first();

        if ($jobCard) {
            $jobCard->update([
                'allocated_to' =>
                    $technician->id,

                'allocated_by' =>
                    Auth::id(),

                'assigned_at' =>
                    now(),

                'status' =>
                    'allocated',
            ]);
        } else {
            $jobCardNumber =
                JobCard::generateJobCardNumber();

            $attempts = 0;

            while (
                JobCard::query()
                    ->where(
                        'job_number',
                        $jobCardNumber
                    )
                    ->orWhere(
                        'job_card_number',
                        $jobCardNumber
                    )
                    ->exists()
                && $attempts < 10
            ) {
                $jobCardNumber =
                    JobCard::generateJobCardNumber();

                $attempts++;
            }

            JobCard::query()->create([
                'customer_id' =>
                    $calibration->customer_id,

                'calibration_id' =>
                    $calibration->id,

                'job_card_number' =>
                    $jobCardNumber,

                'job_number' =>
                    $jobCardNumber,

                'status' =>
                    'allocated',

                'description' =>
                    'Calibration for '
                    . (
                        $calibration->equipment_name
                        ?? 'Equipment'
                    ),

                'allocated_to' =>
                    $technician->id,

                'allocated_by' =>
                    Auth::id(),

                'assigned_at' =>
                    now(),
            ]);
        }

        activity()
            ->performedOn(
                $calibration
            )
            ->causedBy(
                $user
            )
            ->withProperties([
                'technician_id' =>
                    $technician->id,

                'technician_name' =>
                    $technician->name,

                'is_reassignment' =>
                    $isReassignment,

                'customer_type' =>
                    $isInternal
                        ? 'internal_kplc'
                        : 'external',

                'status' =>
                    'assigned',
            ])
            ->log(
                $isReassignment
                    ? 'Calibration technician reassigned'
                    : 'Calibration technician assigned'
            );

        DB::commit();

        return redirect()
            ->route(
                'calibrations.show',
                $calibration
            )
            ->with(
                'success',
                $isReassignment
                    ? 'Technician reassigned successfully.'
                    : 'Calibration assigned successfully to '
                        . $technician->name
                        . '.'
            );

    } catch (\Throwable $exception) {
        DB::rollBack();

        Log::error(
            'Calibration technician assignment failed.',
            [
                'calibration_id' =>
                    $calibration->id,

                'technician_id' =>
                    $technician->id,

                'message' =>
                    $exception->getMessage(),
            ]
        );

        return back()->with(
            'error',
            'Failed to assign technician: '
            . $exception->getMessage()
        );
    }
}

public function generateRequestForm(Calibration $calibration)
{
    // Load relationships
    $calibration->load(['customer', 'equipment']);

    // Pass to service
    $pdf = app(DocumentGenerationService::class)->generateRequestForm($calibration);

    return $pdf->stream('request-form-' . $calibration->id . '.pdf');
}

/**
 * Generate Delivery Note PDF
 */
// public function generateDeliveryNote(Calibration $calibration, $type = 'delivery')
// {
//     $pdf = app(DocumentGenerationService::class)->generateDeliveryNote($calibration, $type);
//     return $pdf->stream('delivery-note-' . $calibration->id . '.pdf');
// }

public function generateDeliveryNote(Calibration $calibration, $type = 'delivery')
{
    $pdf = app(DocumentGenerationService::class)
        ->generateDeliveryNote($calibration, $type);

    $filename = match ($type) {
        'collection' => 'collection-note-' . $calibration->id . '.pdf',
        default => 'delivery-note-' . $calibration->id . '.pdf',
    };

    return $pdf->stream($filename);
}
/**
 * Generate Job Card PDF
 */
public function generateJobCard(Calibration $calibration)
{
    // Find job card by calibration_id or customer_request_id
    $jobCard = JobCard::where('calibration_id', $calibration->id)->first();

    if (!$jobCard) {
        // Try to find by customer request
        $customerRequest = CustomerRequest::where('calibration_id', $calibration->id)->first();
        if ($customerRequest) {
            $jobCard = JobCard::where('customer_request_id', $customerRequest->id)->first();
        }
    }

    if (!$jobCard) {
        // Create a new job card
        $jobCard = JobCard::create([
            'customer_id' => $calibration->customer_id,
            'calibration_id' => $calibration->id,
            'job_card_number' => JobCard::generateJobCardNumber(),
            'job_number' => JobCard::generateJobCardNumber(),
            'status' => 'pending',
            'description' => 'Calibration for ' . ($calibration->equipment_name ?? 'Equipment'),
            'assigned_at' => now(),
        ]);

        // Assign technician if exists
        if ($calibration->performed_by) {
            $jobCard->allocated_to = $calibration->performed_by;
            $jobCard->allocated_by = auth()->id();
            $jobCard->status = 'allocated';
            $jobCard->save();
        }
    }

    $pdf = app(DocumentGenerationService::class)->generateJobCard($jobCard);
    return $pdf->stream('job-card-' . $jobCard->id . '.pdf');
}

/**
 * Store a test result for a calibration
 */
public function test(Request $request, Calibration $calibration)
{
    $request->validate([
        // Required fields
        'parameter' => 'required|string|max:255',
        'nominal_value' => 'required|numeric',
        'measured_value' => 'required|numeric',
        'tolerance_min' => 'required|numeric',
        'tolerance_max' => 'required|numeric',
        'unit' => 'required|string|max:20',

        // Optional fields
        'test_name' => 'nullable|string|max:255',
        'test_type' => 'nullable|string|max:50',
        'applied_value' => 'nullable|string',
        'power_factor' => 'nullable|string',
        'voltage' => 'nullable|string',
        'frequency' => 'nullable|string',
        'uncertainty' => 'nullable|numeric',
        'result' => 'nullable|in:PASS,FAIL',
        'start_reading' => 'nullable|numeric',
        'stop_reading' => 'nullable|numeric',
        'energy_difference' => 'nullable|numeric',
        'dial_error' => 'nullable|numeric',
        'trial_data' => 'nullable|array',
        'trial_data.*' => 'nullable|numeric',
        'notes' => 'nullable|string',
    ]);

    DB::beginTransaction();

    try {
        // Get the last test order
        $lastOrder = $calibration->testResults()->max('test_order') ?? 0;
        $testOrder = $lastOrder + 1;

        // Calculate error percentage
        $errorPercentage = 0;
        if ($request->nominal_value != 0) {
            $errorPercentage = (($request->measured_value - $request->nominal_value) / $request->nominal_value) * 100;
        }

        // Check if within tolerance
        $isWithinTolerance = ($request->measured_value >= $request->tolerance_min &&
                              $request->measured_value <= $request->tolerance_max);

        // Determine result
        $result = $request->result ?? ($isWithinTolerance ? 'PASS' : 'FAIL');

        // Create test result
        $testResult = new TestResult();
        $testResult->calibration_id = $calibration->id;
        $testResult->parameter = $request->parameter;
        $testResult->nominal_value = $request->nominal_value;
        $testResult->measured_value = $request->measured_value;
        $testResult->tolerance_min = $request->tolerance_min;
        $testResult->tolerance_max = $request->tolerance_max;
        $testResult->unit = $request->unit;
        $testResult->is_within_tolerance = $isWithinTolerance;
        $testResult->error_percentage = $errorPercentage;
        $testResult->test_name = $request->test_name;
        $testResult->test_type = $request->test_type;
        $testResult->applied_value = $request->applied_value;
        $testResult->power_factor = $request->power_factor;
        $testResult->voltage = $request->voltage;
        $testResult->frequency = $request->frequency;
        $testResult->uncertainty = $request->uncertainty;
        $testResult->result = $result;
        $testResult->start_reading = $request->start_reading;
        $testResult->stop_reading = $request->stop_reading;
        $testResult->energy_difference = $request->energy_difference;
        $testResult->dial_error = $request->dial_error;
        $testResult->trial_data = $request->trial_data;
        $testResult->test_order = $testOrder;
        $testResult->notes = $request->notes;
        $testResult->save();

        // Update calibration status
        if (in_array($calibration->status, ['assigned', 'in_progress'])) {
            $calibration->status = 'testing';
            $calibration->save();
        }

        DB::commit();

        return redirect()->route('calibrations.tests.index', $calibration)
            ->with('success', 'Test result added successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Test result error: ' . $e->getMessage());
        return back()->with('error', 'Failed to add test result: ' . $e->getMessage());
    }
}

/**
 * Record payment for an external calibration.
 *
 * External flow:
 * pending_payment -> partial/pending_payment -> paid
 */
public function markPaymentReceived(
    Request $request,
    Calibration $calibration
): RedirectResponse {
    $user = Auth::user();

    abort_unless(
        $user
            && !$user->hasRole('customer'),
        403
    );

    $calibration->loadMissing(
        'customer'
    );

    if (
        $this->isInternalKplcCalibration(
            $calibration
        )
    ) {
        return back()->with(
            'error',
            'Internal KPLC calibrations do not require payment.'
        );
    }

    $quotation =
        $this->latestQuotation(
            $calibration
        );

    if (!$quotation) {
        return back()->with(
            'error',
            'No quotation exists for this calibration.'
        );
    }

    if (
        !in_array(
            $calibration->status,
            [
                'quoted',
                'pending_payment',
            ],
            true
        )
    ) {
        return back()->with(
            'error',
            'Payment can only be recorded after the quotation has been generated.'
        );
    }

    $validated = $request->validate([
        'payment_reference' => [
            'nullable',
            'string',
            'max:255',
        ],

        'payment_method' => [
            'required',
            'string',
            'in:mpesa,bank_transfer,cash,cheque',
        ],

        'amount_paid' => [
            'required',
            'numeric',
            'gt:0',
        ],
    ]);

    $totalAmount =
        (float) $quotation->total_amount;

    $previouslyPaid = max(
        (float) (
            $quotation->amount_paid
            ?? 0
        ),
        (float) (
            $calibration->amount_paid
            ?? 0
        )
    );

    $outstandingBalance = max(
        $totalAmount - $previouslyPaid,
        0
    );

    $amountReceived =
        (float) $validated[
            'amount_paid'
        ];

    if ($outstandingBalance <= 0) {
        return back()->with(
            'error',
            'This quotation has already been fully paid.'
        );
    }

    if (
        $amountReceived
        > $outstandingBalance
    ) {
        return back()
            ->withInput()
            ->with(
                'error',
                'Payment exceeds the outstanding balance of KSh '
                . number_format(
                    $outstandingBalance,
                    2
                )
                . '.'
            );
    }

    $newAmountPaid =
        $previouslyPaid
        + $amountReceived;

    $newBalance = max(
        $totalAmount
        - $newAmountPaid,
        0
    );

    $fullyPaid =
        $newBalance <= 0.00001;

    DB::beginTransaction();

    try {
        $quotation->update([
            'amount_paid' =>
                $newAmountPaid,

            'balance_due' =>
                $newBalance,

            'payment_method' =>
                $validated[
                    'payment_method'
                ],

            'payment_reference' =>
                $validated[
                    'payment_reference'
                ]
                ?? null,

            'mpesa_payment_status' =>
                $fullyPaid
                    ? 'paid'
                    : 'unpaid',

            'paid_at' =>
                $fullyPaid
                    ? now()
                    : null,
        ]);

        $calibrationUpdate = [
            'amount_paid' =>
                $newAmountPaid,

            'payment_status' =>
                $fullyPaid
                    ? 'paid'
                    : 'partial',

            'status' =>
                $fullyPaid
                    ? 'paid'
                    : 'pending_payment',

            'payment_reference' =>
                $validated[
                    'payment_reference'
                ]
                ?? null,

            'payment_method' =>
                $validated[
                    'payment_method'
                ],
        ];

        if (
            $fullyPaid
            && Schema::hasColumn(
                $calibration->getTable(),
                'payment_received_at'
            )
        ) {
            $calibrationUpdate[
                'payment_received_at'
            ] = now();
        }

        $calibration->update(
            $calibrationUpdate
        );

        AuditLogService::log(
            $fullyPaid
                ? 'payment_completed'
                : 'partial_payment_received',
            $calibration
        );

        DB::commit();

        return redirect()
            ->route(
                'calibrations.show',
                $calibration
            )
            ->with(
                'success',
                $fullyPaid
                    ? 'Payment completed successfully. The calibration can now be assigned to a technician.'
                    : 'Partial payment recorded successfully. Outstanding balance: KSh '
                        . number_format(
                            $newBalance,
                            2
                        )
            );

    } catch (\Throwable $exception) {
        DB::rollBack();

        Log::error(
            'Calibration payment update failed.',
            [
                'calibration_id' =>
                    $calibration->id,

                'quotation_id' =>
                    $quotation->id,

                'message' =>
                    $exception->getMessage(),
            ]
        );

        return back()
            ->withInput()
            ->with(
                'error',
                'Failed to record payment: '
                . $exception->getMessage()
            );
    }
}

/**
 * Generate Quotation PDF (without changing status)
 */
public function generateQuotationPDF(Calibration $calibration)
{
    $pdf = app(DocumentGenerationService::class)->generateQuotation($calibration);
    return $pdf->stream('quotation-' . $calibration->id . '.pdf');
}

/**
 * Show quotation form.
 *
 * Both internal KPLC and external customers require a quotation.
 */
public function quotationForm(
    Calibration $calibration
) {
    $user = Auth::user();

    abort_unless(
        $user
            && !$user->hasRole('customer'),
        403,
        'Customers cannot generate quotations.'
    );

    $calibration->load([
        'customer',
        'quotation',
        'equipmentType',
        'testResults',
        'certificates',
    ]);

    /*
     * Quotation is the first commercial/internal-costing step.
     * Technician allocation is NOT required at this point.
     */
    if ($calibration->status !== 'pending') {
        return redirect()
            ->route(
                'calibrations.show',
                $calibration
            )
            ->with(
                'error',
                'A quotation can only be generated while the calibration is pending.'
            );
    }

    $existingQuotation =
        $this->latestQuotation($calibration);

    if ($existingQuotation) {
        return redirect()
            ->route(
                'calibrations.show',
                $calibration
            )
            ->with(
                'error',
                'A quotation has already been generated for this calibration.'
            );
    }


$calibration->loadMissing([
    'customer',
    'items',
    'equipmentType',
]);

$equipmentTypes = KplcEquipmentType::query()
    ->orderBy('name')
    ->get();

$serviceLookup = KplcServiceType::query()
    ->active()
    ->orderBy('serv_code')
    ->get()
    ->keyBy('id');

$quotation = $this->latestQuotation($calibration);

return view('calibrations.quotation-form', compact(
    'calibration',
    'equipmentTypes',
    'serviceLookup',
    'quotation'
));

}
/**
 * Legacy generation endpoint.
 *
 * If an older route still calls generateQuotation(), use the same
 * storeQuotation logic rather than maintaining a second workflow.
 */
public function generateQuotation(
    Request $request,
    Calibration $calibration,
    ?Customer $customer = null
) {
    return $this->storeQuotation(
        $request,
        $calibration->id
    );
}


/**
 * Send an external-customer quotation.
 *
 * Internal KPLC quotations are internal costing documents and are not
 * sent through the external-payment workflow.
 */
public function sendQuotation(
    Calibration $calibration
): RedirectResponse {
    $user = Auth::user();

    abort_unless(
        $user
            && !$user->hasRole('customer'),
        403,
        'Customers cannot send quotations.'
    );

    $calibration->loadMissing(
        'customer'
    );

    if (
        $this->isInternalKplcCalibration(
            $calibration
        )
    ) {
        return back()->with(
            'error',
            'Internal KPLC quotations do not use the customer payment workflow.'
        );
    }

    $quotation =
        $this->latestQuotation(
            $calibration
        );

    if (!$quotation) {
        return back()->with(
            'error',
            'Generate the quotation before sending it.'
        );
    }

    if (
        !in_array(
            $calibration->status,
            [
                'quoted',
                'pending_payment',
            ],
            true
        )
    ) {
        return back()->with(
            'error',
            'The quotation cannot be sent while the calibration status is '
            . $calibration->status
            . '.'
        );
    }

    $customerEmail = trim(
        (string) (
            $calibration->customer?->email
            ?? ''
        )
    );

    if ($customerEmail === '') {
        return back()->with(
            'error',
            'The customer does not have an email address.'
        );
    }

    try {
        Mail::to(
            $customerEmail
        )->send(
            new CalibrationQuotationMail(
                $calibration
            )
        );

        DB::transaction(
            function () use (
                $calibration,
                $quotation
            ): void {
                $quotation->update([
                    'status' =>
                        'sent',

                    'sent_at' =>
                        now(),

                    'is_external' =>
                        true,

                    'balance_due' =>
                        max(
                            (float) $quotation->total_amount
                            - (float) $quotation->amount_paid,
                            0
                        ),
                ]);

                $updateData = [
                    'status' =>
                        'pending_payment',

                    'requires_payment' =>
                        true,

                    'is_external' =>
                        true,

                    'payment_status' =>
                        (
                            (float) $calibration->amount_paid
                            > 0
                        )
                            ? 'partial'
                            : 'pending',
                ];

                if (
                    Schema::hasColumn(
                        $calibration->getTable(),
                        'quote_sent_at'
                    )
                ) {
                    $updateData[
                        'quote_sent_at'
                    ] = now();
                }

                if (
                    Schema::hasColumn(
                        $calibration->getTable(),
                        'quotation_sent_at'
                    )
                ) {
                    $updateData[
                        'quotation_sent_at'
                    ] = now();
                }

                if (
                    Schema::hasColumn(
                        $calibration->getTable(),
                        'quotation_sent_by'
                    )
                ) {
                    $updateData[
                        'quotation_sent_by'
                    ] = Auth::id();
                }

                $calibration->update(
                    $updateData
                );
            }
        );

        AuditLogService::log(
            'sent_quotation',
            $calibration
        );

        return back()->with(
            'success',
            "Quotation sent successfully to {$customerEmail}. Awaiting payment."
        );

    } catch (\Throwable $exception) {
        Log::error(
            'Quotation email failed.',
            [
                'calibration_id' =>
                    $calibration->id,

                'quotation_id' =>
                    $quotation->id,

                'recipient' =>
                    $customerEmail,

                'message' =>
                    $exception->getMessage(),
            ]
        );

        return back()->with(
            'error',
            config('app.debug')
                ? 'Failed to send quotation: '
                    . $exception->getMessage()
                : 'The quotation could not be sent.'
        );
    }
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
 * Legacy quotation-form entry point.
 *
 * Keep this method if an older route still references it.
 */
public function showQuotationForm(
    $calibrationId
) {
    $calibration = Calibration::query()
        ->with([
            'customer',
            'equipment',
            'equipmentType',
        ])
        ->findOrFail($calibrationId);

    return $this->quotationForm(
        $calibration
    );
}


     /**
     * Accept quotation (customer accepts the quote)
     */
    public function acceptQuotation(Calibration $calibration)
    {
        $quotation = Quote::where('calibration_id', $calibration->id)
            ->where('status', 'sent')
            ->first();

        if (!$quotation) {
            return back()->with('error', 'No active quotation found for this calibration.');
        }

        DB::beginTransaction();

        try {
            // Update quotation
            $quotation->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            // Update calibration
            $calibration->update([
                'status' => 'paid',
                'quote_accepted_at' => now(),
                'payment_status' => 'pending',
                'amount_due' => $quotation->total_amount,
            ]);

            DB::commit();

            return redirect()->route('calibrations.show', $calibration)
                ->with('success', 'Quotation accepted! Please complete the payment to proceed.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to accept quotation: ' . $e->getMessage());
        }
    }

     /**
     * Process payment for calibration
     */
    public function processPayment(Request $request, Calibration $calibration)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:mpesa,bank_transfer,cash,cheque,credit_card',
            'payment_reference' => 'required|string|max:100',
            'payment_date' => 'required|date',
        ]);

        $quotation = Quote::where('calibration_id', $calibration->id)
            ->where('status', 'approved')
            ->first();

        if (!$quotation) {
            return back()->with('error', 'No approved quotation found for this calibration.');
        }

        DB::beginTransaction();

        try {
            $amountPaid = $validated['amount'];
            $totalAmount = $quotation->total_amount;
            $currentPaid = $quotation->amount_paid ?? 0;
            $newPaid = $currentPaid + $amountPaid;
            $balance = $totalAmount - $newPaid;

            // Update quotation payment
            $quotation->update([
                'amount_paid' => $newPaid,
                'balance_due' => $balance,
                'status' => $balance <= 0 ? 'paid' : 'partial_paid',
            ]);

            // Update calibration payment
            $calibration->update([
                'amount_paid' => $newPaid,
                'amount_due' => $totalAmount,
                'payment_status' => $balance <= 0 ? 'paid' : 'partial',
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'],
                'payment_date' => $validated['payment_date'],
                'payment_received_at' => now(),
            ]);

            // If fully paid, update status to paid
            if ($balance <= 0) {
                $calibration->update([
                    'status' => 'paid',
                ]);
            }

            DB::commit();

            $message = $balance <= 0
                ? 'Payment completed successfully! Technician can now be assigned.'
                : 'Partial payment received. Remaining balance: KSh ' . number_format($balance, 2);

            return redirect()->route('calibrations.show', $calibration)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

/**
 * Start work on calibration (in_progress)
 */
public function startWork(Calibration $calibration)
{
    if ($calibration->status !== Calibration::STATUS_ASSIGNED) {
        return back()->with('error', 'Calibration must be assigned before starting work.');
    }

    $calibration->update([
        'status' => Calibration::STATUS_IN_PROGRESS,
        'started_at' => now(),
    ]);

    return redirect()->route('calibrations.show', $calibration)
        ->with('success', 'Work started on calibration.');
}


/**
 * Add test results to calibration (POST only)
 */
public function performTest(StoreTestResultRequest $request, Calibration $calibration)
{

    DB::beginTransaction();

    try {
        // Calculate if within tolerance
        $nominal = (float) $request->nominal_value;
        $measured = (float) $request->measured_value;
        $toleranceMin = (float) $request->tolerance_min;
        $toleranceMax = (float) $request->tolerance_max;

        // Check if measured value is within tolerance range
        $isWithinTolerance = ($measured >= $nominal + $toleranceMin) && ($measured <= $nominal + $toleranceMax);

        // Calculate error percentage
        $errorPercentage = $nominal != 0 ? (($measured - $nominal) / $nominal) * 100 : 0;

        $result = TestResult::create([
            'calibration_id' => $calibration->id,
            'parameter' => $request->parameter,
            'nominal_value' => $nominal,
            'measured_value' => $measured,
            'tolerance_min' => $toleranceMin,
            'tolerance_max' => $toleranceMax,
            'unit' => $request->unit,
            'is_within_tolerance' => $isWithinTolerance,
            'error_percentage' => $errorPercentage,
            'notes' => $request->notes,
        ]);

        // Update calibration status to testing
        $testResults = $calibration->test_results ?? [];
        $testResults[] = [
            'parameter' => $request->parameter,
            'nominal_value' => $nominal,
            'measured_value' => $measured,
            'tolerance_min' => $toleranceMin,
            'tolerance_max' => $toleranceMax,
            'unit' => $request->unit,
            'is_within_tolerance' => $isWithinTolerance,
            'error_percentage' => $errorPercentage,
            'notes' => $request->notes,
        ];

        $calibration->update([
            'status' => 'testing',
            'test_results' => $testResults,
        ]);

        DB::commit();

        return redirect()->route('calibrations.show', $calibration)
            ->with('success', 'Test result added successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to add test result: ' . $e->getMessage());
        return back()->with('error', 'Failed to add test result: ' . $e->getMessage())->withInput();
    }
}

/**
 * Complete calibration.
 *
 * Technician moves:
 *
 * testing -> completed
 *
 * Requirements:
 * - perform_calibrations permission
 * - logged-in user must be performed_by
 * - calibration must be in testing
 * - environmental conditions must be recorded
 * - test results must exist
 */

public function complete(Calibration $calibration)
{
    $user = auth()->user();
    $calibration->load('items.testResults');

if ($calibration->items->isEmpty()) {
    return back()->with(
        'error',
        'No equipment items are registered for this calibration.'
    );
}

foreach ($calibration->items as $item) {
    $equipment = ($item->equipment_name ?: 'Equipment')
        . ' (Serial: ' . ($item->serial_number ?: 'N/A') . ')';

    if (!$item->hasEnvironmentalConditions()) {
        return back()->with(
            'error',
            'Environmental conditions are missing for ' . $equipment
        );
    }

    if ($item->testResults->isEmpty()) {
        return back()->with(
            'error',
            'Test results are missing for ' . $equipment
        );
    }

    if ($item->review_status !== 'approved') {
        return back()->with(
            'error',
            $equipment . ' has not been approved by the Lab Manager. '
            . 'Current status: '
            . str_replace('_', ' ', $item->review_status)
        );
    }
}


    /*
    |--------------------------------------------------------------------------
    | 1. Permission
    |--------------------------------------------------------------------------
    */
    if (!$user || !$user->can('perform_calibrations')) {
        return back()->with(
            'error',
            'You do not have permission to complete calibrations.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Assigned technician
    |--------------------------------------------------------------------------
    */
    if (
        empty($calibration->performed_by)
        || (int) $calibration->performed_by !== (int) $user->id
    ) {
        return back()->with(
            'error',
            'Only the assigned technician can complete this calibration.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Calibration status
    |--------------------------------------------------------------------------
    */
    if ($calibration->status !== Calibration::STATUS_TESTING) {
        return back()->with(
            'error',
            'Only calibrations in Testing status can be completed.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Load each equipment item and its own test results
    |--------------------------------------------------------------------------
    */
    $calibration->load([
        'items.testResults',
    ]);

    if ($calibration->items->isEmpty()) {
        return back()->with(
            'error',
            'Cannot complete calibration because no equipment items '
            . 'have been registered.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Validate every equipment item
    |--------------------------------------------------------------------------
    */
    foreach ($calibration->items as $item) {

        $equipmentName = $item->equipment_name ?: 'Equipment';
        $serialNumber = $item->serial_number ?: 'N/A';

        /*
        | Environmental conditions are stored in calibration_items,
        | not in the parent calibrations table.
        */
        $requiredConditions = [
            'temperature_start' => 'Starting temperature',
            'temperature_end' => 'Ending temperature',
            'humidity_start' => 'Starting humidity',
            'humidity_end' => 'Ending humidity',
            'supply_voltage' => 'Supply voltage',
            'supply_frequency' => 'Supply frequency',
        ];

        $missingConditions = [];

        foreach ($requiredConditions as $field => $label) {
            if (
                $item->{$field} === null
                || $item->{$field} === ''
            ) {
                $missingConditions[] = $label;
            }
        }

        if (!empty($missingConditions)) {
            return back()->with(
                'error',
                'Cannot complete calibration. Environmental conditions '
                . 'are missing for '
                . $equipmentName
                . ' (Serial: ' . $serialNumber . '): '
                . implode(', ', $missingConditions)
                . '.'
            );
        }

        /*
        | Each equipment item must have its own test results.
        */
        if ($item->testResults->isEmpty()) {
            return back()->with(
                'error',
                'Cannot complete calibration. No test results '
                . 'have been recorded for '
                . $equipmentName
                . ' (Serial: ' . $serialNumber . ').'
            );
        }

        /*
        | Verify that results belong to this calibration and item.
        */
        foreach ($item->testResults as $result) {
            if (
                (int) $result->calibration_id !== (int) $calibration->id
                || (int) $result->calibration_item_id !== (int) $item->id
            ) {
                return back()->with(
                    'error',
                    'A test result has an invalid equipment or '
                    . 'calibration reference for '
                    . $equipmentName
                    . ' (Serial: ' . $serialNumber . ').'
                );
            }
        }
    }

    $items = $calibration->items()
    ->lockForUpdate()
    ->get();

if ($items->isEmpty()) {
    throw \Illuminate\Validation\ValidationException::withMessages([
        'calibration' => 'No equipment items have been registered.',
    ]);
}

foreach ($items as $item) {
    if ($item->review_status !== 'approved') {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'calibration' =>
                'Equipment '
                . ($item->serial_number ?? $item->id)
                . ' has not been approved by the Lab Manager.',
        ]);
    }

    if (!$item->hasEnvironmentalConditions()) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'calibration' => 'Environmental conditions are incomplete '
                . 'for equipment ' . ($item->serial_number ?? $item->id),
        ]);
    }

    if (!$item->testResults()->exists()) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'calibration' => 'Test results are missing for equipment '
                . ($item->serial_number ?? $item->id),
        ]);
    }

    // if (
    //     !$item->test_report_path
    //     || !\Illuminate\Support\Facades\Storage::disk('local')
    //         ->exists($item->test_report_path)
    // ) {
    //     throw \Illuminate\Validation\ValidationException::withMessages([
    //         'calibration' => 'Test Machine Report is missing for equipment '
    //             . ($item->serial_number ?? $item->id),
    //     ]);
    // }
}

    /*
    |--------------------------------------------------------------------------
    | 6. Complete parent calibration
    |--------------------------------------------------------------------------
    */
    try {

        DB::transaction(function () use ($calibration) {

            $calibration->update([
                'status' => Calibration::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

        });

        Log::info('Calibration completed', [
            'calibration_id' => $calibration->id,
            'equipment_count' => $calibration->items->count(),
            'test_count' => $calibration->items
                ->sum(fn ($item) => $item->testResults->count()),
            'performed_by' => $calibration->performed_by,
            'completed_by' => $user->id,
        ]);

        return redirect()
            ->route('calibrations.show', $calibration)
            ->with(
                'success',
                'Calibration completed successfully. '
                . 'You can now proceed with certificate generation.'
            );

    } catch (\Throwable $e) {

        Log::error('Failed to complete calibration', [
            'calibration_id' => $calibration->id,
            'user_id' => $user->id,
            'error' => $e->getMessage(),
        ]);

        return back()->with(
            'error',
            'Failed to complete calibration. Please contact '
            . 'the system administrator.'
        );
    }
}


/**
 * Bulk generate certificates for a batch
 *
 * @param string $batchId
 * @return \Illuminate\Http\RedirectResponse
 */

/**
 * Display all batches
 */
public function batches(Request $request)
{
    // Get all unique batch IDs
    $batches = Calibration::select('batch_id', 'customer_id', 'created_at')
        ->whereNotNull('batch_id')
        ->with(['customer'])
        ->get()
        ->groupBy('batch_id')
        ->map(function ($items, $batchId) {
            $first = $items->first();
            return [
                'batch_id' => $batchId,
                'customer' => $first->customer->company_name ?? 'N/A',
                'total' => $items->count(),
                'statuses' => $items->groupBy('status')->map->count(),
                'created_at' => $first->created_at,
                'updated_at' => $items->max('updated_at'),
            ];
        })
        ->sortByDesc('created_at');

    // Filter by status if requested
    if ($request->has('status') && $request->status) {
        $batches = $batches->filter(function ($batch) use ($request) {
            return isset($batch['statuses'][$request->status]);
        });
    }

    // Filter by customer if requested
    if ($request->has('customer_id') && $request->customer_id) {
        $batches = $batches->filter(function ($batch) use ($request) {
            return $batch['customer'] == Customer::find($request->customer_id)->company_name;
        });
    }

    $customers = Customer::where('is_active', 1)->pluck('company_name', 'id');
    $statuses = ['pending', 'quoted', 'paid', 'assigned', 'in_progress', 'testing', 'completed', 'certified', 'rejected'];

    return view('calibrations.batches', compact('batches', 'customers', 'statuses'));
}

/**
 * Show batch details
 */
public function batchDetails($batchId)
{
    $calibrations = Calibration::where('batch_id', $batchId)
        ->with(['customer', 'equipmentType', 'performedBy'])
        ->get();

    if ($calibrations->isEmpty()) {
        return redirect()->route('calibrations.batches')
            ->with('error', 'Batch not found.');
    }

    // Get quotation for this batch
    $quotation = Quote::where('quote_reference', 'BATCH-' . $batchId)
        ->orWhere('notes', 'LIKE', '%Batch ID: ' . $batchId . '%')
        ->first();

    // If not found by reference, try to get from session
    if (!$quotation) {
        $quoteId = session('last_batch_quote_id');
        if ($quoteId) {
            $quotation = Quote::find($quoteId);
        }
    }

    $summary = [
        'batch_id' => $batchId,
        'total' => $calibrations->count(),
        'customer' => $calibrations->first()->customer->company_name ?? 'N/A',
        'statuses' => $calibrations->groupBy('status')->map->count(),
        'created_at' => $calibrations->first()->created_at,
        'updated_at' => $calibrations->max('updated_at'),
        'equipment_types' => $calibrations->groupBy('equipment_type_id')->map(function($items) {
            return [
                'count' => $items->count(),
                'name' => $items->first()->equipmentType->name ?? 'Unknown'
            ];
        }),
        'quotation' => $quotation, // Add quotation to summary
    ];

    $technicians = User::whereHas('roles', function($q) {
        $q->whereIn('name', ['technician', 'lab_technician', 'supervisor']);
    })->pluck('name', 'id');

    return view('calibrations.batch-details', compact('calibrations', 'batchId', 'summary', 'technicians'));
}

/**
 * Delete a batch
 */
public function deleteBatch($batchId)
{
    try {
        $calibrations = Calibration::where('batch_id', $batchId)->get();

        if ($calibrations->isEmpty()) {
            return redirect()->route('calibrations.batches')
                ->with('error', 'Batch not found.');
        }

        // Check if any calibrations are in progress
        $inProgress = $calibrations->whereIn('status', ['assigned', 'in_progress', 'testing'])->count();
        if ($inProgress > 0) {
            return redirect()->back()
                ->with('error', "Cannot delete batch. {$inProgress} calibrations are in progress.");
        }

        DB::beginTransaction();
        try {
            // Delete job cards first
            $calibrationIds = $calibrations->pluck('id');
            JobCard::whereIn('calibration_id', $calibrationIds)->delete();

            // Delete calibrations
            Calibration::whereIn('id', $calibrationIds)->delete();

            DB::commit();

            return redirect()->route('calibrations.batches')
                ->with('success', "Batch {$batchId} deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Failed to delete batch: ' . $e->getMessage());
    }
}

/**
 * Generate job cards for a batch
 */
private function generateBatchJobCards($calibrationIds, $customerId)
{
    $jobCards = [];
    $now = now();

    foreach ($calibrationIds as $calibrationId) {
        try {
            $calibration = Calibration::find($calibrationId);
            if (!$calibration) continue;

            // Check if job card already exists
            $jobCard = JobCard::where('calibration_id', $calibrationId)->first();
            if ($jobCard) continue;

            // Generate job card number
            $jobCardNumber = JobCard::generateJobCardNumber();

            $jobCardData = [
                'customer_id' => $customerId,
                'calibration_id' => $calibrationId,
                'job_card_number' => $jobCardNumber,
                'job_number' => $jobCardNumber,
                'status' => 'pending',
                'description' => 'Bulk calibration for ' . ($calibration->equipment_name ?? 'Equipment'),
                'created_by' => auth()->id(),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            JobCard::create($jobCardData);
            $jobCards[] = $jobCardNumber;

        } catch (\Exception $e) {
            \Log::error("Failed to create job card for calibration {$calibrationId}: " . $e->getMessage());
        }
    }

    return $jobCards;
}

/**
 * Detect equipment type from equipment name
 */
private function detectEquipmentType($equipmentName)
{
    $name = strtolower($equipmentName);

    $typeMap = [
        'single phase' => '1 Phase watt hour meter',
        '3 phase' => '3 Phase Whole current watt hour Meter',
        'ct&pt' => '3 Phase CT&PT Connected watt hour Meter',
        'voltmeter' => 'Voltmeters',
        'ammeter' => 'Ammeters',
        'multimeter' => 'Multimeters',
        'megger' => 'Resistance(Meggers) Meters',
        'ct analyzer' => 'CT Analyzer',
        'current transformer' => 'Current Transformer',
        'frequency' => 'Frequency Meters',
        'clamp' => 'Clamp on Meters',
        'resistance' => 'Resistance boxes',
        'panel' => 'Panel Meter',
    ];

    foreach ($typeMap as $keyword => $typeName) {
        if (strpos($name, $keyword) !== false) {
            $type = KplcEquipmentType::where('name', $typeName)->first();
            if ($type) {
                return $type->id;
            }
        }
    }

    // Default to Single Phase Meter if not detected
    $default = KplcEquipmentType::where('name', '1 Phase watt hour meter')->first();
    return $default ? $default->id : null;
}

/**
 * Bulk assign technicians to calibrations in a batch
 */
public function bulkAssignTechnicians(Request $request, $batchId)
{
    $request->validate([
        'technician_id' => 'required|exists:users,id',
    ]);

    // Get calibrations that are eligible for assignment
    $calibrations = Calibration::where('batch_id', $batchId)
        ->whereIn('status', ['pending', 'quoted', 'paid'])
        ->get();

    if ($calibrations->isEmpty()) {
        return redirect()->back()->with('error', 'No assignable calibrations found in this batch.');
    }

    $count = 0;
    $skipped = 0;
    $errors = [];

    DB::beginTransaction();

    try {
        foreach ($calibrations as $calibration) {
            try {
                // Check if payment is required and completed
                if ($calibration->requiresPayment() && !$calibration->isPaymentCompleted()) {
                    $skipped++;
                    continue;
                }

                // Update calibration
                $calibration->update([
                    'performed_by' => $request->technician_id,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                ]);

                // Update or create job card
                $jobCard = JobCard::where('calibration_id', $calibration->id)->first();

                if ($jobCard) {
                    $jobCard->update([
                        'allocated_to' => $request->technician_id,
                        'allocated_by' => auth()->id(),
                        'assigned_at' => now(),
                        'status' => 'allocated',
                    ]);
                } else {
                    $jobCardNumber = JobCard::generateJobCardNumber();
                    JobCard::create([
                        'customer_id' => $calibration->customer_id,
                        'calibration_id' => $calibration->id,
                        'job_card_number' => $jobCardNumber,
                        'job_number' => $jobCardNumber,
                        'status' => 'allocated',
                        'description' => 'Bulk assignment for ' . ($calibration->equipment_name ?? 'Equipment'),
                        'allocated_to' => $request->technician_id,
                        'allocated_by' => auth()->id(),
                        'assigned_at' => now(),
                    ]);
                }

                $count++;
            } catch (\Exception $e) {
                $errors[] = "Failed for calibration #{$calibration->id}: " . $e->getMessage();
            }
        }

        DB::commit();

        $message = "Successfully assigned {$count} calibrations to technician.";
        if ($skipped > 0) {
            $message .= " {$skipped} calibrations skipped (payment not completed).";
        }
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', array_slice($errors, 0, 3));
        }

        return redirect()->back()->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Bulk assignment failed: ' . $e->getMessage());
    }
}

/**
 * Bulk process payment for calibrations in a batch
 */
public function bulkProcessPayment(Request $request, $batchId)
{
    $request->validate([
        'payment_method' => 'required|string',
        'payment_reference' => 'nullable|string',
        'amount' => 'nullable|numeric|min:0',
    ]);

    $calibrations = Calibration::where('batch_id', $batchId)
        ->where('status', 'quoted')
        ->get();

    if ($calibrations->isEmpty()) {
        return redirect()->back()->with('error', 'No quoted calibrations found in this batch.');
    }

    $count = 0;
    $errors = [];
    $totalAmount = $request->amount ?? 0;

    DB::beginTransaction();

    try {
        // If amount is provided, split it across all calibrations
        $amountPerCalibration = 0;
        if ($totalAmount > 0 && $calibrations->count() > 0) {
            $amountPerCalibration = $totalAmount / $calibrations->count();
        }

        foreach ($calibrations as $calibration) {
            try {
                $amountToPay = $amountPerCalibration > 0
                    ? $amountPerCalibration
                    : $calibration->amount_due;

                $calibration->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'amount_paid' => $amountToPay,
                    'amount_due' => $amountToPay,
                    'payment_method' => $request->payment_method,
                    'payment_reference' => $request->payment_reference ?? 'BULK-' . $batchId,
                    'payment_received_at' => now(),
                ]);

                // Update job card
                $jobCard = JobCard::where('calibration_id', $calibration->id)->first();
                if ($jobCard) {
                    $jobCard->update(['status' => 'pending']);
                }

                $count++;
            } catch (\Exception $e) {
                $errors[] = "Failed for calibration #{$calibration->id}: " . $e->getMessage();
            }
        }

        DB::commit();

        $message = "Successfully processed payment for {$count} calibrations.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', array_slice($errors, 0, 3));
        }

        return redirect()->back()->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Bulk payment failed: ' . $e->getMessage());
    }
}

/**
 * Bulk generate certificates for a batch
 */
public function bulkGenerateCertificates(Request $request, $batchId)
{
    $calibrations = Calibration::where('batch_id', $batchId)
        ->whereIn('status', ['completed', 'certified'])
        ->get();

    if ($calibrations->isEmpty()) {
        return redirect()->back()->with('error', 'No completed calibrations found in this batch.');
    }

    $generated = 0;
    $existing = 0;
    $errors = [];

    foreach ($calibrations as $calibration) {
        try {
            // Check if certificate already exists
            if ($calibration->certificate) {
                $existing++;
                continue;
            }

            // Generate certificate using the helper method
            $certificate = $this->generateCertificate($calibration);

            if ($certificate) {
                $generated++;
            } else {
                $errors[] = "Failed to generate certificate for calibration #{$calibration->id}";
            }
        } catch (\Exception $e) {
            $errors[] = "Error for calibration #{$calibration->id}: " . $e->getMessage();
        }
    }

    $message = "";
    if ($generated > 0) {
        $message .= "✅ Generated {$generated} certificates successfully. ";
    }
    if ($existing > 0) {
        $message .= "ℹ️ {$existing} certificates already existed. ";
    }
    if (!empty($errors)) {
        $message .= "⚠️ " . count($errors) . " errors: " . implode(', ', array_slice($errors, 0, 3));
        if (count($errors) > 3) {
            $message .= " (+" . (count($errors) - 3) . " more)";
        }
    }

    if ($generated === 0 && $existing === 0) {
        return redirect()->back()->with('error', 'No certificates were generated. ' . $message);
    }

    return redirect()->back()
        ->with('success', $message)
        ->with('generated_count', $generated)
        ->with('existing_count', $existing);
}

/**
 * Generate a single certificate (helper method for bulk generation)
 */
private function generateCertificate($calibration)
{
    try {
        // Check if certificate already exists
        if ($calibration->certificate) {
            return $calibration->certificate;
        }

        // Only generate if calibration is completed or certified
        if (!in_array($calibration->status, ['completed', 'certified'])) {
            throw new \Exception('Calibration must be completed to generate certificate.');
        }

        // Use the existing certificate service if available
        if (isset($this->certificateService)) {
            $certificate = $this->certificateService->generateFromCalibration($calibration);
            $this->certificateService->generatePDF($certificate);
        } else {
            // Fallback: Create a simple certificate record
            $certificate = \App\Models\Certificate::create([
                'calibration_id' => $calibration->id,
                'customer_id' => $calibration->customer_id,
                'certificate_number' => 'CERT-' . date('Ymd') . '-' . str_pad($calibration->id, 4, '0', STR_PAD_LEFT),
                'status' => 'generated',
                'generated_by' => auth()->id(),
                'generated_at' => now(),
            ]);
        }

        return $certificate;

    } catch (\Exception $e) {
        \Log::error('Certificate generation failed for calibration #' . $calibration->id . ': ' . $e->getMessage());
        return null;
    }
}

 /**
     * Reassign calibration to a different technician
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reassign(Request $request, $id)
    {
        $request->validate([
            'performed_by' => 'required|exists:users,id',
            'reassignment_reason' => 'required|string|min:10|max:500',
        ]);

        $calibration = Calibration::findOrFail($id);
        $oldTechnicianId = $calibration->performed_by;
        $newTechnicianId = $request->performed_by;

        // Check if trying to reassign to the same technician
        if ($oldTechnicianId == $newTechnicianId) {
            return redirect()->back()
                ->with('error', 'Cannot reassign to the same technician. Please select a different technician.')
                ->withInput();
        }

        // Check if the new technician exists
        $newTechnician = User::find($newTechnicianId);
        if (!$newTechnician) {
            return redirect()->back()
                ->with('error', 'Selected user not found.')
                ->withInput();
        }

        // Check if the user has the technician role using Spatie Permission
        if (!$newTechnician->hasRole('technician')) {
            return redirect()->back()
                ->with('error', 'Selected user is not a technician. Please select a valid technician.')
                ->withInput();
        }

        // Check if technician is active (if you have an is_active field)
        if (isset($newTechnician->is_active) && !$newTechnician->is_active) {
            return redirect()->back()
                ->with('error', 'Selected technician is not active.')
                ->withInput();
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Store old technician info for logging
            $oldTechnician = User::find($oldTechnicianId);

            // Update the calibration
            $calibration->update([
                'performed_by' => $newTechnicianId,
                'status' => 'assigned',
                'assigned_at' => now(),
                'reassigned_at' => now(),
                'reassigned_by' => auth()->id(),
                'previous_technician_id' => $oldTechnicianId
            ]);

            // Create reassignment history
            ReassignmentHistory::create([
                'calibration_id' => $calibration->id,
                'from_technician_id' => $oldTechnicianId,
                'to_technician_id' => $newTechnicianId,
                'reassigned_by' => auth()->id(),
                'reason' => $request->reassignment_reason,
                'reassigned_at' => now()
            ]);

            // Update job card if exists
            $jobCard = JobCard::where('calibration_id', $calibration->id)->first();
            if ($jobCard) {
                $jobCard->update([
                    'allocated_to' => $newTechnicianId,
                    'status' => 'allocated',
                    'assigned_at' => now(),
                ]);
            }

            DB::commit();

            // Log the activity
            Log::info('Calibration reassigned', [
                'calibration_id' => $calibration->id,
                'from' => $oldTechnician->name ?? 'Unknown',
                'to' => $newTechnician->name,
                'reassigned_by' => auth()->user()->name,
                'reason' => $request->reassignment_reason,
                'status' => 'assigned'
            ]);

            return redirect()->route('calibrations.show', $calibration->id)
                ->with('success', "Work successfully reassigned from {$oldTechnician->name} to {$newTechnician->name}");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to reassign calibration', [
                'calibration_id' => $calibration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to reassign work: ' . $e->getMessage())
                ->withInput();
        }
    }

/**
 * Process a batch (bulk actions)
 */
public function processBatchAction(Request $request, $batchId)
{
    $request->validate([
        'action' => 'required|in:quote,assign,complete,delete',
        'technician_id' => 'required_if:action,assign|exists:users,id',
    ]);

    $calibrations = Calibration::where('batch_id', $batchId)->get();

    if ($calibrations->isEmpty()) {
        return redirect()->back()->with('error', 'Batch not found.');
    }

    DB::beginTransaction();

    try {
        $count = 0;
        $action = $request->action;

        foreach ($calibrations as $calibration) {
            switch ($action) {
                case 'quote':
                    // Only quote pending calibrations
                    if ($calibration->status === 'pending') {
                        $calibration->update([
                            'status' => 'quoted',
                            'quote_sent_at' => now(),
                        ]);
                        $count++;
                    }
                    break;

                case 'assign':
    // Check if payment is completed before assigning.
    if (
        $calibration->requiresPayment()
        && !$calibration->isPaymentCompleted()
    ) {
        continue 2; // Skip this calibration and continue the outer loop.
    }

    if (
        in_array(
            $calibration->status,
            ['pending', 'paid', 'quoted'],
            true
        )
    ) {
        $calibration->update([
            // Your existing update values...
        ]);
    }

    break;

                case 'complete':
                    if (in_array($calibration->status, ['testing', 'assigned', 'in_progress'])) {
                        $calibration->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                        $count++;
                    }
                    break;

                case 'delete':
                    if (!in_array($calibration->status, ['assigned', 'in_progress', 'testing'])) {
                        JobCard::where('calibration_id', $calibration->id)->delete();
                        $calibration->delete();
                        $count++;
                    }
                    break;
            }
        }

        DB::commit();

        $messages = [
            'quote' => "{$count} calibrations quoted successfully.",
            'assign' => "{$count} calibrations assigned successfully.",
            'complete' => "{$count} calibrations completed successfully.",
            'delete' => "{$count} calibrations deleted successfully.",
        ];

        $message = $messages[$action];
        if ($count == 0) {
            $message = "No calibrations were processed. Check status conditions.";
        }

        return redirect()->route('calibrations.batch.details', $batchId)
            ->with('info', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Failed to process batch: ' . $e->getMessage());
    }
}

/**
 * View batch quotation PDF
 */
public function viewBatchQuotation($batchId): Response|RedirectResponse
{
    $quote = $this->findBatchQuotation($batchId);

    if (!$quote) {
        return redirect()
            ->back()
            ->with('error', 'No quotation found for this batch.');
    }

    $quote->loadMissing([
        'customer',
        'calibration.equipment',
        'creator',
        'approver',
    ]);

    $data = [
        'quotation' => $quote,
        'items' => $quote->items_data,
        'title' => 'Batch Quotation #' . $quote->quote_number,
    ];

    $fileName = 'Batch-Quotation-'
        . ($quote->quote_number ?? $quote->id)
        . '.pdf';

    $pdf = Pdf::loadView(
        'calibrations.batch-quotation-pdf',
        $data
    )->setPaper('a4', 'portrait');

    return $pdf->download($fileName);
}

/**
 * Preview batch quotation PDF (view in browser)
 */
public function previewBatchQuotation($batchId): Response|RedirectResponse
{
    $quote = $this->findBatchQuotation($batchId);

    if (!$quote) {
        return redirect()
            ->back()
            ->with('error', 'No quotation found for this batch.');
    }

    $quote->loadMissing([
        'customer',
        'calibration.equipment',
        'creator',
        'approver',
    ]);

    $data = [
        'quotation' => $quote,
        'items' => $quote->items_data,
        'title' => 'Batch Quotation #' . $quote->quote_number,
    ];

    $fileName = 'Batch-Quotation-'
        . ($quote->quote_number ?? $quote->id)
        . '.pdf';

    $pdf = Pdf::loadView(
        'calibrations.batch-quotation-pdf',
        $data
    )->setPaper('a4', 'portrait');

    return $pdf->stream($fileName);
}

/**
 * Find a quotation associated with a calibration batch.
 */
private function findBatchQuotation($batchId): ?Quote
{
    $quote = Quote::query()
        ->where(function ($query) use ($batchId): void {
            $query
                ->where(
                    'quote_reference',
                    'BATCH-' . $batchId
                )
                ->orWhere(
                    'notes',
                    'like',
                    '%Batch ID: ' . $batchId . '%'
                );
        })
        ->latest('id')
        ->first();

    if ($quote) {
        return $quote;
    }

    $sessionQuoteId = session('last_batch_quote_id');

    if (!$sessionQuoteId) {
        return null;
    }

    return Quote::find($sessionQuoteId);
}

/**
 * Generate Job Card for a batch
 */
public function batchJobCard($batchId)
{
    $calibrations = Calibration::where('batch_id', $batchId)
        ->with(['customer', 'equipmentType', 'performedBy'])
        ->get();

    if ($calibrations->isEmpty()) {
        return redirect()->back()->with('error', 'Batch not found.');
    }

    $customer = $calibrations->first()->customer;
    $totalItems = $calibrations->count();
    $pending = $calibrations->where('status', 'pending')->count();
    $inProgress = $calibrations->whereIn('status', ['assigned', 'in_progress', 'testing'])->count();
    $completed = $calibrations->where('status', 'completed')->count();

    $data = [
        'batch_id' => $batchId,
        'customer' => $customer,
        'calibrations' => $calibrations,
        'total_items' => $totalItems,
        'pending' => $pending,
        'in_progress' => $inProgress,
        'completed' => $completed,
        'generated_at' => now(),
        'generated_by' => auth()->user()->name,
        'title' => 'Batch Job Card - ' . $batchId,
    ];

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calibrations.batch-job-card', $data);
    return $pdf->download('Batch-Job-Card-' . $batchId . '.pdf');
}

/**
 * Generate Request Form for a batch
 */
public function batchRequestForm($batchId)
{
    $calibrations = Calibration::where('batch_id', $batchId)
        ->with(['customer', 'equipmentType'])
        ->get();

    if ($calibrations->isEmpty()) {
        return redirect()->back()->with('error', 'Batch not found.');
    }

    $customer = $calibrations->first()->customer;

    // Group by equipment type
    $groupedByType = $calibrations->groupBy('equipment_type_id')->map(function($items) {
        return [
            'type' => $items->first()->equipmentType->name ?? 'Unknown',
            'count' => $items->count(),
            'items' => $items
        ];
    });

    $data = [
        'batch_id' => $batchId,
        'customer' => $customer,
        'calibrations' => $calibrations,
        'grouped_by_type' => $groupedByType,
        'total_items' => $calibrations->count(),
        'generated_at' => now(),
        'generated_by' => auth()->user()->name,
        'title' => 'Batch Request Form - ' . $batchId,
    ];

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calibrations.batch-request-form', $data);
    return $pdf->download('Batch-Request-Form-' . $batchId . '.pdf');
}

/**
 * Generate Delivery Note for a batch
 */
public function batchDeliveryNote($batchId)
{
    $calibrations = Calibration::where('batch_id', $batchId)
        ->with(['customer', 'equipmentType'])
        ->get();

    if ($calibrations->isEmpty()) {
        return redirect()->back()->with('error', 'Batch not found.');
    }

    $customer = $calibrations->first()->customer;
    $completedItems = $calibrations->where('status', 'completed')->count();

    $data = [
        'batch_id' => $batchId,
        'customer' => $customer,
        'calibrations' => $calibrations,
        'total_items' => $calibrations->count(),
        'completed_items' => $completedItems,
        'generated_at' => now(),
        'generated_by' => auth()->user()->name,
        'title' => 'Batch Delivery Note - ' . $batchId,
        'delivery_type' => 'delivery', // Set delivery type
    ];

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calibrations.batch-delivery-note', $data);
    return $pdf->download('Batch-Delivery-Note-' . $batchId . '.pdf');
}

/**
 * Generate Collection Note for a batch
 */
public function batchCollectionNote($batchId)
{
    $calibrations = Calibration::where('batch_id', $batchId)
        ->with(['customer', 'equipmentType'])
        ->get();

    if ($calibrations->isEmpty()) {
        return redirect()->back()->with('error', 'Batch not found.');
    }

    $customer = $calibrations->first()->customer;

    $data = [
        'batch_id' => $batchId,
        'customer' => $customer,
        'calibrations' => $calibrations,
        'total_items' => $calibrations->count(),
        'generated_at' => now(),
        'generated_by' => auth()->user()->name,
        'title' => 'Batch Collection Note - ' . $batchId,
        'delivery_type' => 'collection', // Set collection type
    ];

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calibrations.batch-delivery-note', $data);
    return $pdf->download('Batch-Collection-Note-' . $batchId . '.pdf');
}

public function lookupEquipmentBySerial(Request $request)
{
    $validated = $request->validate([
        'serial_number' => [
            'required',
            'string',
            'max:255',
        ],
    ]);

    $serialNumber = trim($validated['serial_number']);

    $calibration = Calibration::query()
        ->with([
            'customer:id,company_name',
            'equipmentType:id,name,rate',
        ])
        ->where('serial_number', $serialNumber)
        ->latest('id')
        ->first();

    if (!$calibration) {
        return response()->json([
            'found' => false,
            'message' => 'No existing equipment was found with this serial number.',
        ]);
    }

    /*
     * Some older records contain JSON that was encoded twice.
     * This safely converts both normal and double-encoded JSON.
     */
    $serviceTypes = $calibration->service_types ?? [];

    if (is_string($serviceTypes)) {
        $serviceTypes = json_decode($serviceTypes, true);

        if (is_string($serviceTypes)) {
            $serviceTypes = json_decode($serviceTypes, true);
        }
    }

    $serviceTypes = is_array($serviceTypes)
        ? array_map('intval', $serviceTypes)
        : [];

    return response()->json([
        'found' => true,

        'message' => 'Existing equipment details found and loaded.',

        'equipment' => [
            'calibration_id' => $calibration->id,

            'customer_id' => $calibration->customer_id,

            'customer_name' =>
                $calibration->customer?->company_name,

            'equipment_name' =>
                $calibration->equipment_name,

            'serial_number' =>
                $calibration->serial_number,

            'manufacturer' =>
                $calibration->manufacturer,

            'model' =>
                $calibration->model,

            'accuracy_class' =>
                $calibration->accuracy_class,

            'ratings' =>
                $calibration->ratings,

            'equipment_type_id' =>
                $calibration->equipment_type_id
                ?? (
                    is_numeric($calibration->equipment_type)
                        ? (int) $calibration->equipment_type
                        : null
                ),

            'equipment_type_name' =>
                $calibration->equipmentType?->name,

            'equipment_condition' =>
                $calibration->equipment_condition,

            'last_calibration_date' =>
                optional(
                    $calibration->last_calibration_date
                )->format('Y-m-d'),

            'service_types' =>
                $serviceTypes,
        ],
    ]);
}



public function lookupBySerial(Request $request): JsonResponse
{
    $validated = $request->validate([
        'serial_number' => ['required', 'string', 'max:255'],
        'customer_id' => ['nullable', 'integer'],
    ]);

    $serialNumber = trim($validated['serial_number']);

    try {
        $query = Calibration::query()
            ->whereRaw(
                'LOWER(TRIM(serial_number)) = ?',
                [mb_strtolower($serialNumber)]
            );

        /*
         * Keep this only when the same serial number
         * may belong to different customers.
         */
        if (!empty($validated['customer_id'])) {
            $query->where(
                'customer_id',
                $validated['customer_id']
            );
        }

        /*
         * Get the most recent calibration record
         * for this serial number.
         */
        $calibration = $query
            ->latest('id')
            ->first();

        if (!$calibration) {
            return response()->json([
                'found' => false,
                'message' => "No previous calibration was found for serial number {$serialNumber}.",
                'equipment' => null,
            ]);
        }

        /*
         * Check whether another calibration for this serial
         * number is currently active.
         *
         * Adjust these statuses to match your database.
         */
        $hasActiveCalibration = Calibration::query()
            ->whereRaw(
                'LOWER(TRIM(serial_number)) = ?',
                [mb_strtolower($serialNumber)]
            )
            ->when(
                !empty($validated['customer_id']),
                fn ($query) => $query->where(
                    'customer_id',
                    $validated['customer_id']
                )
            )
            ->whereIn('status', [
                'pending',
                'scheduled',
                'in_progress',
            ])
            ->exists();

        return response()->json([
            'found' => true,
            'message' => 'Previous calibration details were found.',
            'equipment' => [
                'calibration_id' => $calibration->id,
                'name' => $calibration->equipment_name,
                'equipment_name' => $calibration->equipment_name,
                'serial_number' => $calibration->serial_number,
                'manufacturer' => $calibration->manufacturer,
                'model' => $calibration->model,
                'accuracy_class' => $calibration->accuracy_class,
                'ratings' => $calibration->ratings,
                'condition' => $calibration->equipment_condition,
                'equipment_condition' => $calibration->equipment_condition,
                'last_calibration_date' => $calibration->last_calibration_date
                    ? date(
                        'Y-m-d',
                        strtotime($calibration->last_calibration_date)
                    )
                    : null,
                'equipment_type_id' => $calibration->equipment_type_id,
                'has_active_calibration' => $hasActiveCalibration,
            ],
        ]);
    } catch (\Throwable $exception) {
        Log::error('Calibration serial lookup failed', [
            'serial_number' => $serialNumber,
            'customer_id' => $validated['customer_id'] ?? null,
            'error' => $exception->getMessage(),
        ]);

        return response()->json([
            'found' => false,
            'message' => config('app.debug')
                ? $exception->getMessage()
                : 'The serial-number lookup encountered a server error.',
        ], 500);
    }
}

/**
 * Determine whether this calibration belongs to an internal KPLC customer.
 */
private function isInternalKplcCalibration(
    Calibration $calibration
): bool {
    $calibration->loadMissing('customer');

    return (bool) (
        $calibration->customer
        && $calibration->customer->isKplc()
    );
}


/**
 * Get the latest quotation belonging to the calibration.
 */
private function latestQuotation(
    Calibration $calibration
): ?Quote {
    return Quote::query()
        ->where('calibration_id', $calibration->id)
        ->latest('id')
        ->first();
}


/**
 * Determine whether an external calibration has been fully paid.
 */
private function quotationPaymentCompleted(
    Calibration $calibration,
    ?Quote $quotation = null
): bool {
    if ($this->isInternalKplcCalibration($calibration)) {
        return true;
    }

    $quotation ??= $this->latestQuotation($calibration);

    if (!$quotation) {
        return false;
    }

    $totalAmount = (float) (
        $quotation->total_amount
        ?? $calibration->amount_due
        ?? 0
    );

    $amountPaid = max(
        (float) ($quotation->amount_paid ?? 0),
        (float) ($calibration->amount_paid ?? 0)
    );

    return $totalAmount > 0
        && $amountPaid >= $totalAmount;
}

/**
 * Store Test Parameters / Environmental Conditions.
 *
 * Only the technician assigned as performed_by may record
 * these values, and only while the calibration is in Testing.
 */
public function storeTestParameters(
    Request $request,
    Calibration $calibration
) {
    /*
    |--------------------------------------------------------------------------
    | Authenticated User
    |--------------------------------------------------------------------------
    */

    $user = $request->user();


    if (!$user) {
        abort(
            403,
            'Authentication is required.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    |
    | The user must have the existing perform_calibrations permission.
    |
    */

    if (!$user->can('perform_calibrations')) {

        return back()->with(
            'error',
            'You do not have permission to record test parameters.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Technician Role Check
    |--------------------------------------------------------------------------
    */

    if (
        !$user->hasAnyRole([
            'lab_technician',
            'technician',
        ])
    ) {

        return back()->with(
            'error',
            'Only laboratory technicians can record test parameters.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Assigned Technician Check
    |--------------------------------------------------------------------------
    |
    | performed_by is the technician responsible for carrying out
    | this calibration.
    |
    */

    if (
        empty($calibration->performed_by)

        || (int) $calibration->performed_by
            !== (int) $user->id
    ) {

        return back()->with(
            'error',
            'Only the technician assigned to this calibration can record test parameters.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Status Check
    |--------------------------------------------------------------------------
    |
    | Environmental conditions are actual observations taken during
    | testing. They therefore cannot be entered during registration,
    | assignment, quotation or after completion.
    |
    */

    if (
        $calibration->status
        !== Calibration::STATUS_TESTING
    ) {

        return back()->with(
            'error',
            'Test parameters can only be recorded while the calibration status is Testing.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([

        /*
         * Temperature
         */
        'temperature_start' => [
            'required',
            'numeric',
            'between:-50,100',
        ],

        'temperature_end' => [
            'required',
            'numeric',
            'between:-50,100',
        ],


        /*
         * Relative Humidity
         */
        'humidity_start' => [
            'required',
            'numeric',
            'between:0,100',
        ],

        'humidity_end' => [
            'required',
            'numeric',
            'between:0,100',
        ],


        /*
         * Supply Conditions
         */
        'supply_voltage' => [
            'required',
            'numeric',
            'min:0',
            'max:1000',
        ],

        'supply_frequency' => [
            'required',
            'numeric',
            'min:0',
            'max:1000',
        ],

    ]);


    /*
    |--------------------------------------------------------------------------
    | Build Existing test_parameters JSON Structure
    |--------------------------------------------------------------------------
    |
    | We save the values in their dedicated columns for reporting,
    | but also keep test_parameters populated because the application
    | already has this JSON column and may use it in documents/reports.
    |
    */

    $testParameters = [

        [
            'name' =>
                'Temperature',

            'start_value' =>
                (float) $validated[
                    'temperature_start'
                ],

            'end_value' =>
                (float) $validated[
                    'temperature_end'
                ],

            'unit' =>
                '°C',
        ],

        [
            'name' =>
                'Relative Humidity',

            'start_value' =>
                (float) $validated[
                    'humidity_start'
                ],

            'end_value' =>
                (float) $validated[
                    'humidity_end'
                ],

            'unit' =>
                '%',
        ],

        [
            'name' =>
                'Supply Voltage',

            'nominal_value' =>
                (float) $validated[
                    'supply_voltage'
                ],

            'unit' =>
                'V',
        ],

        [
            'name' =>
                'Supply Frequency',

            'nominal_value' =>
                (float) $validated[
                    'supply_frequency'
                ],

            'unit' =>
                'Hz',
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    DB::beginTransaction();


    try {

        $calibration->update([

            /*
             * Temperature
             */
            'temperature_start' =>
                $validated[
                    'temperature_start'
                ],

            'temperature_end' =>
                $validated[
                    'temperature_end'
                ],


            /*
             * Relative Humidity
             */
            'humidity_start' =>
                $validated[
                    'humidity_start'
                ],

            'humidity_end' =>
                $validated[
                    'humidity_end'
                ],


            /*
             * Supply Conditions
             */
            'supply_voltage' =>
                $validated[
                    'supply_voltage'
                ],

            'supply_frequency' =>
                $validated[
                    'supply_frequency'
                ],


            /*
             * JSON representation
             */
            'test_parameters' =>
                $testParameters,


            /*
             * Audit / Traceability
             */
            'test_parameters_recorded_by' =>
                $user->id,

            'test_parameters_recorded_at' =>
                now(),

        ]);


        DB::commit();


        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        Log::info(
            'Calibration environmental conditions recorded',
            [

                'calibration_id' =>
                    $calibration->id,

                'performed_by' =>
                    $calibration->performed_by,

                'recorded_by' =>
                    $user->id,

                'temperature_start' =>
                    $validated[
                        'temperature_start'
                    ],

                'temperature_end' =>
                    $validated[
                        'temperature_end'
                    ],

                'humidity_start' =>
                    $validated[
                        'humidity_start'
                    ],

                'humidity_end' =>
                    $validated[
                        'humidity_end'
                    ],

                'supply_voltage' =>
                    $validated[
                        'supply_voltage'
                    ],

                'supply_frequency' =>
                    $validated[
                        'supply_frequency'
                    ],

            ]
        );


        return back()->with(
            'success',
            'Test Parameters / Environmental Conditions recorded successfully.'
        );


    } catch (\Throwable $e) {

        DB::rollBack();


        Log::error(
            'Failed to record calibration environmental conditions',
            [

                'calibration_id' =>
                    $calibration->id,

                'user_id' =>
                    $user->id,

                'error' =>
                    $e->getMessage(),

                'trace' =>
                    $e->getTraceAsString(),

            ]
        );


        return back()
            ->withInput()
            ->with(
                'error',
                'Failed to save Test Parameters / Environmental Conditions: '
                . $e->getMessage()
            );
    }
}
}
