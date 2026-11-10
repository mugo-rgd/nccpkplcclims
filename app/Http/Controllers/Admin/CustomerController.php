<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\User;
use App\Models\Calibration;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerAccountCreated;
use Illuminate\Support\Str;
use App\Http\Requests\Admin\CompleteCustomerRegistrationRequest;
use App\Models\CustomerTaxDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    
public function index(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | STEP 1: Validate filters
    |--------------------------------------------------------------------------
    */

    $filters = $request->validate([
        'search' => ['nullable', 'string', 'max:191'],
        'status' => [
            'nullable',
            'in:active,pending,suspended,rejected',
        ],
        'type' => [
            'nullable',
            'in:kplc,external',
        ],
        'date_from' => [
            'nullable',
            'date',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | STEP 2: Build customer query
    |--------------------------------------------------------------------------
    */

    $query = Customer::query()->with('user');

    /*
    |--------------------------------------------------------------------------
    | STEP 3: Search
    |--------------------------------------------------------------------------
    */

    if (!empty($filters['search'])) {

        $search = trim($filters['search']);

        $query->where(function ($q) use ($search) {

            $q->where(
                'company_name',
                'LIKE',
                "%{$search}%"
            )
            ->orWhere(
                'contact_person',
                'LIKE',
                "%{$search}%"
            )
            ->orWhere(
                'phone',
                'LIKE',
                "%{$search}%"
            )
            ->orWhere(
                'email',
                'LIKE',
                "%{$search}%"
            )
            ->orWhere(
                'tax_id',
                'LIKE',
                "%{$search}%"
            );

        });
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 4: Filter by status
    |--------------------------------------------------------------------------
    */

    if (!empty($filters['status'])) {

        $query->where(
            'status',
            $filters['status']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 5: Filter by customer type
    |--------------------------------------------------------------------------
    */

    if (!empty($filters['type'])) {

        if ($filters['type'] === 'kplc') {

            $query->where(function ($q) {

                $q->where(
                    'company_name',
                    'LIKE',
                    '%KPLC%'
                )
                ->orWhere(
                    'company_name',
                    'LIKE',
                    '%Kenya Power%'
                );

            });

        } elseif ($filters['type'] === 'external') {

            $query
                ->where(
                    'company_name',
                    'NOT LIKE',
                    '%KPLC%'
                )
                ->where(
                    'company_name',
                    'NOT LIKE',
                    '%Kenya Power%'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 6: Filter by registration date
    |--------------------------------------------------------------------------
    */

    if (!empty($filters['date_from'])) {

        $query->whereDate(
            'created_at',
            '>=',
            $filters['date_from']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 7: Pagination
    |--------------------------------------------------------------------------
    */

    $customers = $query
        ->orderByDesc('created_at')
        ->paginate(15)
        ->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | STEP 8: Calculate registration eligibility
    |--------------------------------------------------------------------------
    */

    // Determine the current KRA status from the newest document only.
    $customerIds = $customers->getCollection()->pluck('id');

    $latestKraDocuments = CustomerTaxDocument::query()
        ->whereIn('customer_id', $customerIds)
        ->where('document_type', 'kra_pin_certificate')
        ->orderByDesc('id')
        ->get()
        ->unique('customer_id')
        ->keyBy('customer_id');

    $customers->getCollection()->transform(
        function ($customer) use ($latestKraDocuments) {

            $notes = $customer->notes;

            if (is_string($notes)) {
                $notes = json_decode($notes, true) ?: [];
            }

            $notes = is_array($notes) ? $notes : [];

            $isQuickAdd =
                ($notes['registration_source'] ?? null) === 'quick_add';

            $isIncompleteQuickAdd =
                $isQuickAdd
                && (
                    ($notes['registration_incomplete'] ?? true)
                    || !$customer->user_id
                );

            $latestKraDocument = $latestKraDocuments->get($customer->id);
            $latestKraStatus = $latestKraDocument?->verification_status;
            $kraVerified = $latestKraStatus === 'verified';

            $customer->setAttribute('is_quick_add', $isQuickAdd);
            $customer->setAttribute(
                'is_incomplete_quick_add',
                $isIncompleteQuickAdd
            );
            $customer->setAttribute('latest_kra_status', $latestKraStatus);
            $customer->setAttribute('kra_verified', $kraVerified);
            $customer->setAttribute(
                'can_approve_registration',
                $customer->status === 'pending'
                && (
                    !$isQuickAdd
                    || (!$isIncompleteQuickAdd && $kraVerified)
                )
            );

            return $customer;
        }
    );

    /*
    |--------------------------------------------------------------------------
    | STEP 9: Statistics
    |--------------------------------------------------------------------------
    */

    $stats = [
        'total' => Customer::count(),

        'active' => Customer::where(
            'status',
            'active'
        )->count(),

        'pending' => Customer::where(
            'status',
            'pending'
        )->count(),

        'kplc' => Customer::where(function ($q) {

            $q->where(
                'company_name',
                'LIKE',
                '%KPLC%'
            )
            ->orWhere(
                'company_name',
                'LIKE',
                '%Kenya Power%'
            );

        })->count(),
    ];

    /*
    |--------------------------------------------------------------------------
    | STEP 10: Return view
    |--------------------------------------------------------------------------
    */

    $statuses = [
        'active',
        'pending',
        'suspended',
        'rejected',
    ];

    return view(
        'admin.customers.index',
        compact(
            'customers',
            'statuses',
            'stats'
        )
    );
}


    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        $currentUser = Auth::user();
        return view('admin.customers.create', compact('currentUser'));
    }

   
/**
 * Redirect legacy customer creation submissions
 * to the standardized registration workflow.
 *
 * All customer registration must use
 * RegisterController and CustomerRegistrationRequest.
 */
public function store(StoreCustomerRequest $request)
{
    abort_unless(
        auth()->user()?->hasAnyRole([
            'super_admin',
            'lab_manager',
            'supervisor',
        ]),
        403,
        'You are not authorized to register customers.'
    );

    return redirect()
        ->route('register')
        ->with(
            'info',
            'Customer registration has been standardized. '
            . 'Please complete the registration form, '
            . 'including the KRA PIN certificate and '
            . 'any applicable VAT exemption documents.'
        );
}
    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        // Load basic relationships
        $customer->load(['user']);

        // Get calibrations with relationships - use 'equipment' not 'mclEquipment'
        $calibrations = $customer->calibrations()
            ->with([
                'performedBy',      // Technician who performed it
                'equipment',        // MCL Equipment used for testing
                'testResults',      // Test results
                'certificate',      // Certificate if generated
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get certificates through calibrations
        $certificates = Certificate::whereHas('calibration', function($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })->with([
            'calibration',
            'checkedBy',
            'approvedBy'
        ])->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_calibrations' => $calibrations->count(),
            'pending_calibrations' => $calibrations->where('status', 'pending')->count(),
            'in_progress_calibrations' => $calibrations->whereIn('status', ['assigned', 'in_progress', 'testing'])->count(),
            'completed_calibrations' => $calibrations->whereIn('status', ['completed', 'certified'])->count(),
            'total_certificates' => $certificates->count(),
            'approved_certificates' => $certificates->where('status', 'approved')->count(),
            'pending_certificates' => $certificates->where('status', 'pending')->count(),
            'total_equipment' => $calibrations->pluck('equipment_name')->unique()->count(),
        ];

        // Get recent calibrations (limit 5)
        $recentCalibrations = $customer->calibrations()
            ->with([
                'performedBy',
                'equipment',
                'testResults',
            ])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.customers.show', compact(
            'customer',
            'stats',
            'recentCalibrations',
            'calibrations',
            'certificates'
        ));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Update notes with edit info
            $notes = $customer->notes ?? [];
            if (is_string($notes)) {
                $notes = json_decode($notes, true) ?? [];
            }

            $notes['updated_by'] = auth()->user()->name;
            $notes['updated_by_staff_no'] = auth()->user()->staff_number ?? 'N/A';
            $notes['updated_at_lab'] = now()->toDateTimeString();

            $data['notes'] = json_encode($notes);

            $customer->update($data);

            // Update associated user
            if ($customer->user && $request->filled('email')) {
                $customer->user->update([
                    'name' => $request->contact_person,
                    'email' => $request->email,
                ]);
            }

            DB::commit();

            return redirect()->route('customers.index')
                ->with('success', 'Customer updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update customer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        try {
            // Check if customer has any active calibrations
            $activeCalibrations = $customer->calibrations()
                ->whereIn('status', ['pending', 'in_progress', 'testing'])
                ->count();

            if ($activeCalibrations > 0) {
                return back()->with('error', 'Cannot delete customer with active calibrations.');
            }

            // Soft delete the customer
            $customer->delete();

            // Optionally disable the user
            if ($customer->user) {
                $customer->user->update(['is_active' => false]);
            }

            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }
    }

   
/**
 * Quick add customer from calibration/request form.
 *
 * Creates a provisional customer record.
 * Does not create a login account.
 * Customer must be approved before activation.
 */

public function quickAdd(Request $request)
{
    abort_unless(
        auth()->user()?->hasAnyRole([
            'super_admin',
            'lab_manager',
            'supervisor',
        ]),
        403,
        'You are not authorized to add customers.'
    );

    $validated = $request->validate([
        'company_name' => [
            'required', 'string', 'max:191',
        ],
        'contact_person' => [
            'required', 'string', 'max:191',
        ],
        'phone' => [
            'required', 'string', 'max:20',
        ],
        'email' => [
            'nullable', 'email', 'max:191',
        ],
        'address' => [
            'required', 'string',
        ],
    ]);

    try {
        $customer = DB::transaction(function () use (
            $request,
            $validated
        ) {
            $staff = auth()->user();

            $notes = [
                'registration_source' => 'quick_add',
                'created_by_user_id' => $staff->id,
                'created_by' => $staff->name,
                'created_by_staff_no' =>
                    $staff->staff_number ?? 'N/A',
                'created_at_lab' =>
                    now()->toDateTimeString(),
                'created_from' => 'MCL - Quick Add',
                'registration_ip' => $request->ip(),
                'registration_incomplete' => true,
            ];

            return Customer::create([
                'user_id' => null,
                'company_name' =>
                    $validated['company_name'],
                'contact_person' =>
                    $validated['contact_person'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'],
                'registration_date' => now(),
                'status' => 'pending',
                'is_active' => false,
                'vat_exemption_status' => 'none',
                'customer_vat_rate' => null,
                'notes' => $notes,
            ]);
        });

        $message = 'Customer added successfully. '
            . 'Registration completion and '
            . 'approval are still required.';

        // Preserve JSON responses for AJAX/API requests.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'id' => $customer->id,
                'name' => $customer->company_name,
                'contact' => $customer->contact_person,
                'phone' => $customer->phone,
                'status' => $customer->status,
                'is_active' => false,
                'requires_approval' => true,
                'message' => $message,
            ], 201);
        }

        // Normal HTML form: return to Customers page.
        return redirect()
            ->route('admin.customers.index')
            ->with('success', $message);

    } catch (\Throwable $e) {
        \Log::error('Quick customer creation failed', [
            'staff_id' => auth()->id(),
            'exception' => $e,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to add customer. Please try again.',
            ], 500);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with(
                'error',
                'Unable to add customer. Please try again.'
            );
    }
}


    /**
     * Get customer details for AJAX
     */
    public function getDetails(Customer $customer)
    {
        return response()->json([
            'id' => $customer->id,
            'company_name' => $customer->company_name,
            'contact_person' => $customer->contact_person,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->address,
            'tax_id' => $customer->tax_id,
        ]);
    }

    /**
     * Export customers to Excel
     */
    public function export(Request $request)
    {
        // Implementation for export
        $customers = Customer::all();
        // ... export logic
    }

    /** Account approval is separate from VAT verification. */
 
/**
 * Approve customer registration.
 *
 * Customer account approval is independent
 * of VAT exemption verification.
 */



public function approve(Request $request, Customer $customer)
{
    /*
    |--------------------------------------------------------------------------
    | STEP 1: Authorization
    |--------------------------------------------------------------------------
    */

    abort_unless(
        Auth::user()?->hasAnyRole([
            'supervisor',
            'lab_manager',
        ]),
        403,
        'You are not authorized to approve customer registrations.'
    );

    /*
    |--------------------------------------------------------------------------
    | STEP 2: Validate approval request
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([
        'approval_notes' => [
            'nullable',
            'string',
            'max:2000',
        ],
        'approval_confirmed' => [
            'required',
            'accepted',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | STEP 3: Begin approval transaction
    |--------------------------------------------------------------------------
    */

    DB::transaction(function () use ($customer, $validated) {

        $customer = Customer::query()
            ->whereKey($customer->id)
            ->lockForUpdate()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | STEP 4: Check registration status
        |--------------------------------------------------------------------------
        */

        if ($customer->status !== 'pending') {
            throw ValidationException::withMessages([
                'customer' =>
                    'Only pending customer registrations '
                    . 'can be approved.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 5: Read registration notes
        |--------------------------------------------------------------------------
        */

        $notes = $customer->notes;

        if (is_string($notes)) {
            $notes = json_decode($notes, true) ?: [];
        }

        $notes = is_array($notes) ? $notes : [];

        $isQuickAdd =
            ($notes['registration_source'] ?? null)
                === 'quick_add';

        /*
        |--------------------------------------------------------------------------
        | STEP 6: Validate Quick Add completion
        |--------------------------------------------------------------------------
        */

        if ($isQuickAdd) {

            if ($notes['registration_incomplete'] ?? true) {
                throw ValidationException::withMessages([
                    'customer' =>
                        'This customer was created through Quick Add. '
                        . 'Complete registration before approval.',
                ]);
            }

            if (!$customer->user_id) {
                throw ValidationException::withMessages([
                    'customer' =>
                        'The customer registration has no linked '
                        . 'login account. Complete registration first.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 7: Verify latest KRA PIN certificate
            |--------------------------------------------------------------------------
            */

            $latestKraDocument = CustomerTaxDocument::query()
                ->where('customer_id', $customer->id)
                ->where(
                    'document_type',
                    'kra_pin_certificate'
                )
                ->orderByDesc('id')
                ->first();

            if (!$latestKraDocument) {
                throw ValidationException::withMessages([
                    'customer' =>
                        'A KRA PIN certificate must be uploaded '
                        . 'before customer approval.',
                ]);
            }

            if (
                $latestKraDocument->verification_status
                !== 'verified'
            ) {
                throw ValidationException::withMessages([
                    'customer' =>
                        'The latest KRA PIN certificate must '
                        . 'be verified before approving this customer.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 8: Validate linked login account
        |--------------------------------------------------------------------------
        */

        $linkedUser = null;

        if ($customer->user_id) {

            $linkedUser = User::query()
                ->whereKey($customer->user_id)
                ->lockForUpdate()
                ->first();

            if (!$linkedUser) {
                throw ValidationException::withMessages([
                    'customer' =>
                        'The linked customer login account '
                        . 'could not be found.',
                ]);
            }

            if (
                $isQuickAdd
                && !$linkedUser->hasRole('customer')
            ) {
                throw ValidationException::withMessages([
                    'customer' =>
                        'The linked login account does not '
                        . 'have the required customer role.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 9: Record approval audit
        |--------------------------------------------------------------------------
        */

        $notes['account_review'] = [
            'decision' => 'approved',
            'approval_notes' =>
                trim($validated['approval_notes'] ?? ''),
            'reviewed_by' => Auth::id(),
            'reviewer_name' => Auth::user()->name,
            'reviewed_at' => now()->toIso8601String(),
        ];

        /*
        |--------------------------------------------------------------------------
        | STEP 10: Activate customer record
        |--------------------------------------------------------------------------
        */

        $customer->update([
            'status' => 'active',
            'is_active' => true,
            'notes' => $notes,
        ]);

        /*
        |--------------------------------------------------------------------------
        | STEP 11: Activate linked customer login
        |--------------------------------------------------------------------------
        */

        if ($linkedUser) {
            $linkedUser->update([
                'is_active' => true,
            ]);
        }

    }, 3);

    /*
    |--------------------------------------------------------------------------
    | STEP 12: Redirect
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route('admin.customers.index')
        ->with(
            'success',
            'Customer registration approved successfully. '
            . 'The customer account is now active.'
        );
}

    
/**
 * Reject customer registration.
 */
public function reject(Request $request, Customer $customer)
{
    abort_unless(
        Auth::user()?->hasAnyRole([
            'supervisor',
            'lab_manager',
        ]),
        403
    );

    $validated = $request->validate([
        'rejection_reason' => [
            'required',
            'string',
            'min:10',
            'max:2000',
        ],
    ]);

    DB::transaction(function () use ($customer, $validated) {

        $customer = Customer::query()
            ->lockForUpdate()
            ->findOrFail($customer->id);

        if ($customer->status !== 'pending') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'customer' =>
                    'Only pending customer registrations can be rejected.',
            ]);
        }

        $notes = $customer->notes;

        if (is_string($notes)) {
            $notes = json_decode($notes, true) ?: [];
        }

        $notes = is_array($notes) ? $notes : [];

        $notes['account_review'] = [
            'decision' => 'rejected',
            'reason' => $validated['rejection_reason'],
            'reviewed_by' => Auth::id(),
            'reviewer_name' => Auth::user()->name,
            'reviewed_at' => now()->toIso8601String(),
        ];

        $customer->update([
            'status' => 'rejected',
            'is_active' => false,
            'notes' => $notes,
        ]);

        if ($customer->user_id) {
            User::whereKey($customer->user_id)
                ->update([
                    'is_active' => false,
                ]);
        }
    });

    return redirect()
        ->route('admin.customers.index')
        ->with(
            'success',
            'Customer registration rejected. '
            . 'The linked customer login account remains inactive.'
        );
}


    /** Only a lab manager can verify a requested VAT exemption. */
    public function approveVat(Request $request, Customer $customer)
{
    /*
    |--------------------------------------------------------------------------
    | STEP 1: Authorization
    |--------------------------------------------------------------------------
    */

    abort_unless(
        auth()->user()?->hasRole('lab_manager'),
        403,
        'Only the Lab Manager can approve VAT exemptions.'
    );

    /*
    |--------------------------------------------------------------------------
    | STEP 2: Validate approval request
    |--------------------------------------------------------------------------
    */

    $data = $request->validate([
        'approved_vat_rate' => [
            'required',
            'numeric',
            'between:0,100',
        ],
        'verification_notes' => [
            'nullable',
            'string',
            'max:2000',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | STEP 3: Approve within database transaction
    |--------------------------------------------------------------------------
    */

    DB::transaction(function () use ($customer, $data) {

        /*
        |--------------------------------------------------------------------------
        | STEP 4: Lock customer record
        |--------------------------------------------------------------------------
        */

        $customer = Customer::query()
            ->whereKey($customer->id)
            ->lockForUpdate()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | STEP 5: Confirm pending VAT request
        |--------------------------------------------------------------------------
        */

        if ($customer->vat_exemption_status !== 'pending') {
            throw ValidationException::withMessages([
                'approved_vat_rate' =>
                    'VAT exemption is not pending review.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 6: Mandatory KRA PIN certificate verification
        |--------------------------------------------------------------------------
        */

        $latestKraDocument = $customer->taxDocuments()
            ->where('document_type', 'kra_pin_certificate')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if (!$latestKraDocument) {
            throw ValidationException::withMessages([
                'approved_vat_rate' =>
                    'VAT exemption cannot be approved because '
                    . 'the customer has not uploaded a KRA PIN certificate.',
            ]);
        }

        if ($latestKraDocument->verification_status !== 'verified') {
            throw ValidationException::withMessages([
                'approved_vat_rate' =>
                    'VAT exemption cannot be approved until '
                    . 'the latest KRA PIN certificate has been verified.',
            ]);
        }

        if (
            blank($latestKraDocument->file_path) ||
            !Storage::disk('local')->exists(
                $latestKraDocument->file_path
            )
        ) {
            throw ValidationException::withMessages([
                'approved_vat_rate' =>
                    'The verified KRA PIN certificate file is missing. '
                    . 'Please resolve the document issue before VAT approval.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 7: Validate VAT exemption certificate details
        |--------------------------------------------------------------------------
        */

        if (
            blank($customer->vat_exemption_certificate_number) ||
            blank($customer->vat_exemption_expiry_date)
        ) {
            throw ValidationException::withMessages([
                'approved_vat_rate' =>
                    'A VAT exemption certificate number and '
                    . 'expiry date are required.',
            ]);
        }

        // A certificate expiring today remains valid today.
        if (
            $customer->vat_exemption_expiry_date
                ->startOfDay()
                ->lt(today())
        ) {
            throw ValidationException::withMessages([
                'approved_vat_rate' =>
                    'The VAT exemption certificate has expired. '
                    . 'A valid certificate is required.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 8: Retrieve latest VAT exemption document
        |--------------------------------------------------------------------------
        */

        $vatDocument = $customer->taxDocuments()
            ->where('document_type', 'vat_exemption_certificate')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if (!$vatDocument || blank($vatDocument->file_path)) {
            throw ValidationException::withMessages([
                'approved_vat_rate' =>
                    'The VAT exemption certificate upload is missing.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 9: Confirm document exists in private storage
        |--------------------------------------------------------------------------
        */

        if (
            !Storage::disk('local')->exists(
                $vatDocument->file_path
            )
        ) {
            throw ValidationException::withMessages([
                'approved_vat_rate' =>
                    'The uploaded VAT exemption certificate '
                    . 'could not be found in storage.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 10: Confirm latest VAT document is pending
        |--------------------------------------------------------------------------
        */

        if ($vatDocument->verification_status !== 'pending') {
            throw ValidationException::withMessages([
                'approved_vat_rate' =>
                    'Only a pending VAT exemption certificate '
                    . 'can be approved.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 11: Record VAT document verification
        |--------------------------------------------------------------------------
        */

        $vatDocument->update([
            'verification_status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_notes' =>
                trim($data['verification_notes'] ?? '') ?: null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | STEP 12: Approve customer VAT exemption
        |--------------------------------------------------------------------------
        */

        $customer->update([
            'vat_exemption_status' => 'approved',
            'is_tax_exempt' => true,
            'customer_vat_rate' => $data['approved_vat_rate'],
            'vat_rate' => $data['approved_vat_rate'],
            'vat_verified_by' => auth()->id(),
            'vat_verified_at' => now(),
            'vat_verification_notes' =>
                trim($data['verification_notes'] ?? '') ?: null,
        ]);

    }, 3);

    /*
    |--------------------------------------------------------------------------
    | STEP 13: Return success response
    |--------------------------------------------------------------------------
    */

    return back()->with(
        'success',
        'VAT exemption certificate verified and approved '
        . 'successfully by the Lab Manager. '
        . 'The approved VAT rate is now applicable.'
    );
}
    public function rejectVat(Request $request, Customer $customer)
{
    /*
    |--------------------------------------------------------------------------
    | STEP 1: Authorization
    |--------------------------------------------------------------------------
    */

    abort_unless(
        auth()->user()?->hasRole('lab_manager'),
        403,
        'Only the Lab Manager can reject VAT exemption requests.'
    );

    /*
    |--------------------------------------------------------------------------
    | STEP 2: Validate rejection notes
    |--------------------------------------------------------------------------
    */

    $data = $request->validate([
        'verification_notes' => [
            'required',
            'string',
            'min:10',
            'max:2000',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | STEP 3: Reject VAT exemption within transaction
    |--------------------------------------------------------------------------
    */

    DB::transaction(function () use ($customer, $data) {

        // Lock customer to prevent concurrent VAT decisions.
        $customer = Customer::query()
            ->whereKey($customer->id)
            ->lockForUpdate()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | STEP 4: Confirm VAT request is pending
        |--------------------------------------------------------------------------
        */

        if ($customer->vat_exemption_status !== 'pending') {
            throw ValidationException::withMessages([
                'verification_notes' =>
                    'VAT exemption is not pending review.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 5: Retrieve latest VAT exemption certificate
        |--------------------------------------------------------------------------
        */

        $vatDocument = $customer->taxDocuments()
            ->where(
                'document_type',
                'vat_exemption_certificate'
            )
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if (!$vatDocument) {
            throw ValidationException::withMessages([
                'verification_notes' =>
                    'No VAT exemption certificate was found for this customer.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 6: Ensure latest certificate is pending
        |--------------------------------------------------------------------------
        */

        if ($vatDocument->verification_status !== 'pending') {
            throw ValidationException::withMessages([
                'verification_notes' =>
                    'Only a pending VAT exemption certificate can be rejected.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 7: Reject latest VAT exemption certificate
        |--------------------------------------------------------------------------
        */

        $vatDocument->update([
            'verification_status' => 'rejected',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_notes' =>
                trim($data['verification_notes']),
        ]);

        /*
        |--------------------------------------------------------------------------
        | STEP 8: Update customer VAT status
        |--------------------------------------------------------------------------
        */

        $customer->update([
            'vat_exemption_status' => 'rejected',
            'is_tax_exempt' => false,
            'customer_vat_rate' => null,
            'vat_rate' => null,
            'vat_verified_by' => auth()->id(),
            'vat_verified_at' => now(),
            'vat_verification_notes' =>
                trim($data['verification_notes']),
        ]);

    }, 3);

    /*
    |--------------------------------------------------------------------------
    | STEP 9: Return response
    |--------------------------------------------------------------------------
    */

    return back()->with(
        'success',
        'VAT exemption request rejected. '
        . 'The latest VAT exemption certificate has been marked as rejected. '
        . 'Customer account approval status remains unchanged.'
    );
}

    
/**
 * Show completion form for a provisional
 * Quick Add customer.
 */
public function completeRegistrationForm(
    Customer $customer
): View {
    $this->ensureQuickAddRegistration($customer);

    return view(
        'admin.customers.complete-registration',
        compact('customer')
    );
}

/**
 * Complete an existing Quick Add customer
 * registration without creating duplicates.
 */
public function completeRegistration(
    CompleteCustomerRegistrationRequest $request,
    Customer $customer
): RedirectResponse {

    $validated = $request->validated();

    $vatRequested = $request->boolean(
        'vat_exemption_requested'
    );

    $uploadedPaths = [];

    $registrarId = Auth::id();

    try {

        DB::beginTransaction();

        /*
         * Lock the existing customer to prevent
         * simultaneous completion requests.
         */
        $customer = Customer::query()
            ->lockForUpdate()
            ->findOrFail($customer->id);

        /*
         * Recheck eligibility after locking.
         */
        $this->ensureQuickAddRegistration($customer);

        /*
         * Create a new inactive customer user.
         */
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' =>
                $validated['phone_number'],
            'password' => Hash::make(
                $validated['password']
            ),
            'role' => 'customer',
            'is_active' => false,
            'status' => true,
            'designation' => 'Customer',
            'department' => 'Customer',
        ]);

        $user->assignRole('customer');

        /*
         * Preserve Quick Add audit information.
         */
        $notes = $this->customerNotes($customer);

        $notes['registration_incomplete'] = false;

        $notes['registration_completed_by'] =
            $registrarId;

        $notes['registration_completed_at'] =
            now()->toIso8601String();

        $notes['registration_completion_source'] =
            'staff_completion';

        /*
         * Update the existing customer record.
         * Do not call Customer::create().
         */
        $customer->update([
            'user_id' => $user->id,

            'company_name' =>
                $validated['company_name'],

            'contact_person' =>
                $validated['contact_person'],

            'phone' =>
                $validated['company_phone'],

            'email' =>
                $validated['company_email']
                    ?: $validated['email'],

            'address' =>
                $validated['address'],

            'tax_id' =>
                $validated['tax_id'],

            'status' => 'pending',

            'is_active' => false,

            'vat_exemption_status' =>
                $vatRequested
                    ? 'pending'
                    : 'none',

            'is_tax_exempt' => false,

            'customer_vat_rate' => null,

            'vat_rate' => null,

            'vat_exemption_certificate_number' =>
                $vatRequested
                    ? $validated[
                        'vat_exemption_certificate_number'
                    ]
                    : null,

            'vat_exemption_expiry_date' =>
                $vatRequested
                    ? $validated[
                        'vat_exemption_expiry_date'
                    ]
                    : null,

            'vat_verified_by' => null,

            'vat_verified_at' => null,

            'vat_verification_notes' => null,

            'notes' => $notes,
        ]);

        /*
         * Mandatory KRA PIN certificate.
         */
        $this->saveCompletionDocument(
            request: $request,
            customer: $customer,
            documentType: 'kra_pin_certificate',
            uploadedBy: $registrarId,
            uploadedPaths: $uploadedPaths
        );

        /*
         * Optional VAT exemption workflow.
         */
        if ($vatRequested) {

            $this->saveCompletionDocument(
                request: $request,
                customer: $customer,
                documentType: 'vat_exemption_certificate',
                uploadedBy: $registrarId,
                uploadedPaths: $uploadedPaths
            );
        }

        DB::commit();

    } catch (ValidationException $e) {

        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        foreach ($uploadedPaths as $path) {
            Storage::disk('local')->delete($path);
        }

        throw $e;

    } catch (Throwable $e) {

        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        foreach ($uploadedPaths as $path) {
            try {
                Storage::disk('local')->delete($path);
            } catch (Throwable $cleanupException) {
                Log::warning(
                    'Customer document cleanup failed',
                    [
                        'path' => $path,
                        'exception' => $cleanupException,
                    ]
                );
            }
        }

        Log::error(
            'Quick Add registration completion failed',
            [
                'customer_id' => $customer->id,
                'registrar_id' => $registrarId,
                'exception' => $e,
            ]
        );

        return back()
            ->withInput(
                $request->except([
                    'password',
                    'password_confirmation',
                    'kra_pin_certificate',
                    'vat_exemption_certificate',
                ])
            )
            ->with(
                'error',
                'Unable to complete registration. '
                . 'Please try again.'
            );
    }

    /*
     * The staff session remains unchanged.
     * The new customer is not logged in.
     */
    return redirect()
        ->route('admin.customers.index')
        ->with(
            'success',
            'Customer registration completed successfully. '
            . 'The login account has been created but '
            . 'remains inactive until account approval.'
        );
}

/**
 * Ensure the customer is eligible for
 * Quick Add registration completion.
 */
private function ensureQuickAddRegistration(
    Customer $customer
): void {

    abort_unless(
        Auth::user()?->hasAnyRole([
            'super_admin',
            'lab_manager',
            'supervisor',
        ]),
        403
    );

    $notes = $this->customerNotes($customer);

    if (
        $customer->status !== 'pending'
        || $customer->user_id !== null
        || ($notes['registration_source'] ?? null) !== 'quick_add'
        || !($notes['registration_incomplete'] ?? false)
    ) {
        throw ValidationException::withMessages([
            'customer' =>
                'This customer is not an incomplete '
                . 'Quick Add registration.',
        ]);
    }
}

/**
 * Normalize customer notes.
 */
private function customerNotes(
    Customer $customer
): array {

    $notes = $customer->notes;

    if (is_string($notes)) {
        $notes = json_decode($notes, true) ?: [];
    }

    return is_array($notes) ? $notes : [];
}

/**
 * Save a tax document privately and record
 * its verification metadata.
 */

private function saveCompletionDocument(
    CompleteCustomerRegistrationRequest $request,
    Customer $customer,
    string $documentType,
    int $uploadedBy,
    array &$uploadedPaths
): void {

    if (!$request->hasFile($documentType)) {
        throw ValidationException::withMessages([
            $documentType =>
                "The {$documentType} document is required.",
        ]);
    }

    $file = $request->file($documentType);

    if (!$file || !$file->isValid()) {
        throw ValidationException::withMessages([
            $documentType =>
                "The uploaded {$documentType} document is invalid.",
        ]);
    }

    // Prevent duplicate registration documents.
    if (
        CustomerTaxDocument::where(
            'customer_id',
            $customer->id
        )
        ->where('document_type', $documentType)
        ->exists()
    ) {
        throw ValidationException::withMessages([
            $documentType =>
                'A document of this type has already been uploaded.',
        ]);
    }

    $path = $file->store(
        "customer-tax-documents/{$customer->id}",
        'local'
    );

    if ($path === false) {
        throw new \RuntimeException(
            "Failed to store document: {$documentType}"
        );
    }

    $uploadedPaths[] = $path;

    CustomerTaxDocument::create([
        'customer_id' => $customer->id,
        'document_type' => $documentType,
        'file_path' => $path,
        'original_filename' =>
            $file->getClientOriginalName(),
        'mime_type' => $file->getMimeType(),
        'file_size' => $file->getSize(),
        'verification_status' => 'pending',
        'uploaded_by' => $uploadedBy,
        'verified_by' => null,
        'verified_at' => null,
        'verification_notes' => null,
    ]);
}



public function verifyKraPinDocument(
    Request $request,
    Customer $customer
): RedirectResponse {

    /*
    |--------------------------------------------------------------------------
    | STEP 1: Authorization
    |--------------------------------------------------------------------------
    */

    abort_unless(
        Auth::user()?->hasAnyRole([
            'supervisor',
            'lab_manager',
        ]),
        403,
        'You are not authorized to verify KRA PIN certificates.'
    );

    /*
    |--------------------------------------------------------------------------
    | STEP 2: Validate request
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([
        'document_id' => [
            'required',
            'integer',
            'exists:customer_tax_documents,id',
        ],
        'verification_notes' => [
            'nullable',
            'string',
            'max:2000',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | STEP 3: Verify within transaction
    |--------------------------------------------------------------------------
    */

    DB::transaction(function () use ($customer, $validated) {

        /*
         * Lock the customer first.
         *
         * Replacement, verification, rejection and approval
         * should use the same customer-first locking order.
         */

        $lockedCustomer = Customer::query()
            ->whereKey($customer->id)
            ->lockForUpdate()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | STEP 4: Retrieve latest KRA PIN certificate
        |--------------------------------------------------------------------------
        */

        $latestDocument = CustomerTaxDocument::query()
            ->where(
                'customer_id',
                $lockedCustomer->id
            )
            ->where(
                'document_type',
                'kra_pin_certificate'
            )
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if (!$latestDocument) {
            throw ValidationException::withMessages([
                'document_id' =>
                    'No KRA PIN certificate was found '
                    . 'for this customer.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 5: Prevent verification of superseded documents
        |--------------------------------------------------------------------------
        */

        if (
            (int) $latestDocument->id
            !== (int) $validated['document_id']
        ) {
            throw ValidationException::withMessages([
                'document_id' =>
                    'This KRA PIN certificate is no longer '
                    . 'the latest document. Review the most '
                    . 'recently uploaded certificate.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 6: Verify document status
        |--------------------------------------------------------------------------
        */

        if (
            $latestDocument->verification_status
            !== 'pending'
        ) {
            throw ValidationException::withMessages([
                'document_id' =>
                    'Only pending KRA PIN certificates '
                    . 'can be verified.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 7: Record verification
        |--------------------------------------------------------------------------
        */

        $latestDocument->update([
            'verification_status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' =>
                trim(
                    $validated['verification_notes'] ?? ''
                ) ?: null,
        ]);

    }, 3);

    /*
    |--------------------------------------------------------------------------
    | STEP 8: Redirect to customer details
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route('customers.show', $customer)
        ->with(
            'success',
            'The latest KRA PIN certificate has been '
            . 'verified successfully.'
        );
}



public function rejectKraPinDocument(
    Request $request,
    Customer $customer
): RedirectResponse {

    /*
    |--------------------------------------------------------------------------
    | STEP 1: Authorization
    |--------------------------------------------------------------------------
    */

    abort_unless(
        Auth::user()?->hasAnyRole([
            'supervisor',
            'lab_manager',
        ]),
        403,
        'You are not authorized to reject KRA PIN certificates.'
    );

    /*
    |--------------------------------------------------------------------------
    | STEP 2: Validate rejection request
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([
        'document_id' => [
            'required',
            'integer',
            'exists:customer_tax_documents,id',
        ],
        'verification_notes' => [
            'required',
            'string',
            'min:10',
            'max:2000',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | STEP 3: Reject within transaction
    |--------------------------------------------------------------------------
    */

    DB::transaction(function () use ($customer, $validated) {

        /*
         * Lock customer first to coordinate with
         * document replacement and account approval.
         */

        $lockedCustomer = Customer::query()
            ->whereKey($customer->id)
            ->lockForUpdate()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | STEP 4: Retrieve latest KRA PIN certificate
        |--------------------------------------------------------------------------
        */

        $latestDocument = CustomerTaxDocument::query()
            ->where(
                'customer_id',
                $lockedCustomer->id
            )
            ->where(
                'document_type',
                'kra_pin_certificate'
            )
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if (!$latestDocument) {
            throw ValidationException::withMessages([
                'document_id' =>
                    'No KRA PIN certificate was found '
                    . 'for this customer.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 5: Reject outdated document requests
        |--------------------------------------------------------------------------
        */

        if (
            (int) $latestDocument->id
            !== (int) $validated['document_id']
        ) {
            throw ValidationException::withMessages([
                'document_id' =>
                    'This document has been superseded '
                    . 'by a newer KRA PIN certificate. '
                    . 'Review the latest document instead.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 6: Check pending status
        |--------------------------------------------------------------------------
        */

        if (
            $latestDocument->verification_status
            !== 'pending'
        ) {
            throw ValidationException::withMessages([
                'document_id' =>
                    'Only pending KRA PIN certificates '
                    . 'can be rejected.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 7: Record rejection
        |--------------------------------------------------------------------------
        */

        $latestDocument->update([
            'verification_status' => 'rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' =>
                trim($validated['verification_notes']),
        ]);

    }, 3);

    /*
    |--------------------------------------------------------------------------
    | STEP 8: Redirect
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route('customers.show', $customer)
        ->with(
            'success',
            'The KRA PIN certificate has been rejected. '
            . 'A replacement certificate can now be uploaded.'
        );
}



public function viewKraPinDocument(
    Customer $customer,
    CustomerTaxDocument $document
) {
    abort_unless(
        Auth::user()?->hasAnyRole([
            'super_admin',
            'supervisor',
            'lab_manager',
        ]),
        403
    );

    abort_unless(
        (int) $document->customer_id === (int) $customer->id
        && $document->document_type === 'kra_pin_certificate',
        404
    );

    abort_unless(
        Storage::disk('local')->exists($document->file_path),
        404,
        'Document file not found.'
    );

    return Storage::disk('local')->download(
        $document->file_path,
        $document->original_filename
    );
}

public function replaceKraPinDocument(
    Request $request,
    Customer $customer
): RedirectResponse {

    abort_unless(
        Auth::user()?->hasAnyRole([
            'super_admin',
            'supervisor',
            'lab_manager',
        ]),
        403,
        'You are not authorized to replace KRA documents.'
    );

    $validated = $request->validate([
        'kra_pin_certificate' => [
            'required',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120',
        ],
        'replacement_reason' => [
            'required',
            'string',
            'min:10',
            'max:2000',
        ],
    ]);

    $uploadedPath = null;

    try {
        DB::transaction(function () use (
            $request,
            $customer,
            $validated,
            &$uploadedPath
        ) {
            $customer = Customer::query()
                ->lockForUpdate()
                ->findOrFail($customer->id);

            if ($customer->status !== 'pending') {
                throw ValidationException::withMessages([
                    'customer' =>
                        'KRA document replacement is only available '
                        . 'for pending customer registrations.',
                ]);
            }

            $latestDocument = CustomerTaxDocument::query()
                ->where('customer_id', $customer->id)
                ->where(
                    'document_type',
                    'kra_pin_certificate'
                )
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (
                !$latestDocument
                || $latestDocument->verification_status !== 'rejected'
            ) {
                throw ValidationException::withMessages([
                    'kra_pin_certificate' =>
                        'Only a rejected KRA PIN certificate '
                        . 'can be replaced.',
                ]);
            }

            $file = $request->file('kra_pin_certificate');

            if (!$file || !$file->isValid()) {
                throw ValidationException::withMessages([
                    'kra_pin_certificate' =>
                        'The uploaded KRA PIN certificate is invalid.',
                ]);
            }

            $uploadedPath = $file->store(
                "customer-tax-documents/{$customer->id}",
                'local'
            );

            if (!$uploadedPath) {
                throw new \RuntimeException(
                    'Unable to store replacement certificate.'
                );
            }

            CustomerTaxDocument::create([
                'customer_id' => $customer->id,
                'document_type' => 'kra_pin_certificate',
                'file_path' => $uploadedPath,
                'original_filename' =>
                    $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'verification_status' => 'pending',
                'uploaded_by' => Auth::id(),
                'verified_by' => null,
                'verified_at' => null,
                'verification_notes' => null,
            ]);

            $notes = $this->customerNotes($customer);

            $notes['kra_document_replacement'] = [
                'previous_document_id' => $latestDocument->id,
                'reason' => $validated['replacement_reason'],
                'replaced_by' => Auth::id(),
                'replaced_at' => now()->toIso8601String(),
            ];

            $customer->update([
                'notes' => $notes,
            ]);
        });

    } catch (ValidationException $e) {
        if ($uploadedPath) {
            Storage::disk('local')->delete($uploadedPath);
        }

        throw $e;

    } catch (Throwable $e) {
        if ($uploadedPath) {
            Storage::disk('local')->delete($uploadedPath);
        }

        Log::error('KRA certificate replacement failed', [
            'customer_id' => $customer->id,
            'staff_id' => Auth::id(),
            'exception' => $e,
        ]);

        return back()
            ->withInput($request->except('kra_pin_certificate'))
            ->with(
                'error',
                'Unable to replace the KRA PIN certificate.'
            );
    }

    return redirect()
        ->route('customers.show', $customer)
        ->with(
            'success',
            'Replacement KRA PIN certificate uploaded. '
            . 'The document is pending verification.'
        );
}


}
