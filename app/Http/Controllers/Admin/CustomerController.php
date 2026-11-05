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

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = Customer::with('user');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('tax_id', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type (KPLC vs External)
        if ($request->filled('type')) {
            if ($request->type === 'kplc') {
                $query->where(function ($q) {
                    $q->where('company_name', 'LIKE', '%KPLC%')
                      ->orWhere('company_name', 'LIKE', '%Kenya Power%');
                });
            } elseif ($request->type === 'external') {
                $query->where('company_name', 'NOT LIKE', '%KPLC%')
                    ->where('company_name', 'NOT LIKE', '%Kenya Power%');
            }
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(15);
        $statuses = ['active', 'pending', 'suspended', 'rejected'];

        return view('admin.customers.index', compact('customers', 'statuses'));
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
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request)
    {
        DB::beginTransaction();

        try {
            // Check if user already exists with this email
            $user = null;
            if ($request->filled('email')) {
                $user = User::where('email', $request->email)->first();

                if (!$user) {
                    // Create user account
                   $tempPassword = Str::password(12);

                    $user = User::create([
                        'name' => $request->contact_person,
                        'email' => $request->email,
                        'password' => Hash::make($tempPassword),
                        'role' => 'customer',
                        'is_active' => true,
                        'status' => 1,
                        'created_by' => auth()->id(),
                    ]);
                    $user->assignRole('customer');
                }
            }

            // Prepare notes with staff info
            $notes = $request->notes ?? [];
            if (is_string($notes)) {
                $notes = json_decode($notes, true) ?? [];
            }

            // Add creation metadata
            $notes['created_by'] = auth()->user()->name;
            $notes['created_by_staff_no'] = auth()->user()->staff_number ?? 'N/A';
            $notes['created_at_lab'] = now()->toDateTimeString();
            $notes['created_from'] = 'MCL';

            // Check if customer is KPLC
            $isKplc = false;
            if (stripos($request->company_name, 'KPLC') !== false ||
                stripos($request->company_name, 'Kenya Power') !== false) {
                $isKplc = true;
                $notes['customer_type'] = 'kplc';
            } else {
                $notes['customer_type'] = 'external';
            }

            // Create customer
            $customer = Customer::create([
                'user_id' => $user ? $user->id : null,
                'company_name' => $request->company_name,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'tax_id' => $request->tax_id,
                'credit_limit' => $request->credit_limit,
                'payment_terms' => $request->payment_terms ?? '30_days',
                'registration_date' => now(),
                'status' => 'pending',
                'is_active' => true,
                'notes' => json_encode($notes),
            ]);

            DB::commit();

            if (
    $user &&
    !empty($user->email) &&
    isset($tempPassword)
) {
    try {

        Mail::to($user->email)
            ->send(
                new CustomerAccountCreated(
                    $customer,
                    $user,
                    $tempPassword
                )
            );

    } catch (\Exception $e) {

        \Log::error(
            'Failed to send customer welcome email: '
            . $e->getMessage()
        );

    }
}

            $message = $isKplc ? 'KPLC customer' : 'External customer';

            return redirect()->route('customers.index')
                ->with('success', "{$message} created successfully!")
                ->with('alert', 'Customer: ' . $customer->company_name)
                ->with('customer_id', $customer->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create customer: ' . $e->getMessage())
                ->withInput();
        }
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
     * Quick add customer from request form
     */
    public function quickAdd(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:191',
            'contact_person' => 'required|string|max:191',
            'phone' => 'required|string|max:191',
            'email' => 'nullable|email|max:191',
            'address' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $customer = Customer::create([
                'company_name' => $request->company_name,
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'registration_date' => now(),
                'status' => 'active',
                'is_active' => true,
                'notes' => json_encode([
                    'created_by' => auth()->user()->name,
                    'created_by_staff_no' => auth()->user()->staff_number ?? 'N/A',
                    'created_at_lab' => now()->toDateTimeString(),
                    'created_from' => 'MCL - Quick Add',
                ]),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'id' => $customer->id,
                'name' => $customer->company_name,
                'contact' => $customer->contact_person,
                'phone' => $customer->phone,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
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
  public function approve(Request $request, Customer $customer)
{
    /*
    |--------------------------------------------------------------------------
    | STEP 1: Authorization
    |--------------------------------------------------------------------------
    | Only Supervisor and Lab Manager can approve customer registrations.
    */
    abort_unless(
        auth()->user()?->hasAnyRole([
            'supervisor',
            'lab_manager',
        ]),
        403,
        'You are not authorized to approve customer registrations.'
    );

    /*
    |--------------------------------------------------------------------------
    | STEP 2: Validate approval form
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
    | STEP 3: Verify registration status
    |--------------------------------------------------------------------------
    */
    if ($customer->status !== 'pending') {
        return back()->with(
            'error',
            'Only pending customer registrations can be approved.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 4: Approve customer and record audit details
    |--------------------------------------------------------------------------
    */
    DB::transaction(function () use ($customer, $validated) {

        $customer = Customer::query()
            ->lockForUpdate()
            ->findOrFail($customer->id);

        if ($customer->status !== 'pending') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'customer' => 'This customer registration has already been reviewed.',
            ]);
        }

        $notes = $customer->notes;

        if (is_string($notes)) {
            $notes = json_decode($notes, true) ?: [];
        }

        $notes = is_array($notes) ? $notes : [];

        $notes['account_review'] = [
            'decision' => 'approved',

            'approval_notes' =>
                trim($validated['approval_notes'] ?? ''),

            'reviewed_by' => auth()->id(),

            'reviewer_name' => auth()->user()->name,

            'reviewed_at' => now()->toIso8601String(),
        ];

        $customer->update([
            'status' => 'active',
            'is_active' => true,
            'notes' => $notes,
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | STEP 5: Return to customer management
    |--------------------------------------------------------------------------
    | VAT approval remains independent of customer account approval.
    */
    return redirect()
        ->route('admin.customers.index')
        ->with(
            'success',
            'Customer account approved successfully. '
            . 'VAT exemption, if requested, remains pending '
            . 'separate Lab Manager verification.'
        );
}

    public function reject(Request $request, Customer $customer)
    {
        abort_unless(auth()->user()?->hasAnyRole(['supervisor', 'lab_manager']), 403);
        $data = $request->validate(['rejection_reason' => 'required|string|min:10|max:2000']);
        if ($customer->status !== 'pending') {
            return back()->with('error', 'Only pending registrations can be rejected.');
        }
        DB::transaction(function () use ($customer, $data) {
            $notes = is_array($customer->notes) ? $customer->notes : [];
            $notes['account_review'] = [
                'decision' => 'rejected', 'reason' => $data['rejection_reason'],
                'reviewed_by' => auth()->id(), 'reviewed_at' => now()->toIso8601String(),
            ];
            $customer->update(['status' => 'rejected', 'is_active' => false, 'notes' => $notes]);
        });
        return back()->with('success', 'Customer registration rejected.');
    }

    /** Only a lab manager can verify a requested VAT exemption. */
    public function approveVat(Request $request, Customer $customer)
    {
        abort_unless(auth()->user()?->hasRole('lab_manager'), 403);
        $data = $request->validate([
            'approved_vat_rate' => 'required|numeric|between:0,100',
            'verification_notes' => 'nullable|string|max:2000',
        ]);
        if ($customer->vat_exemption_status !== 'pending') {
            return back()->with('error', 'VAT exemption is not pending review.');
        }
        if (!$customer->vat_exemption_certificate_number || !$customer->vat_exemption_expiry_date ||
            $customer->vat_exemption_expiry_date->isPast()) {
            return back()->with('error', 'A valid exemption certificate number and unexpired date are required.');
        }
        $vatDocument = $customer->taxDocuments()->where('document_type', 'vat_exemption_certificate')->first();
        if (!$vatDocument || !$vatDocument->file_path) {
            return back()->with('error', 'VAT exemption certificate upload is missing.');
        }
        DB::transaction(function () use ($customer, $vatDocument, $data) {
            $customer->update([
                'vat_exemption_status' => 'approved',
                'is_tax_exempt' => true,
                'customer_vat_rate' => $data['approved_vat_rate'],
                'vat_rate' => $data['approved_vat_rate'],
                'vat_verified_by' => auth()->id(),
                'vat_verified_at' => now(),
                'vat_verification_notes' => $data['verification_notes'] ?? null,
            ]);
            $vatDocument->update([
                'verification_status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verification_notes' => $data['verification_notes'] ?? null,
            ]);
        });
        return back()->with('success', 'VAT exemption verified and approved by Lab Manager.');
    }

    public function rejectVat(Request $request, Customer $customer)
    {
        abort_unless(auth()->user()?->hasRole('lab_manager'), 403);
        $data = $request->validate(['verification_notes' => 'required|string|min:10|max:2000']);
        if ($customer->vat_exemption_status !== 'pending') {
            return back()->with('error', 'VAT exemption is not pending review.');
        }
        DB::transaction(function () use ($customer, $data) {
            $customer->update([
                'vat_exemption_status' => 'rejected',
                'is_tax_exempt' => false,
                'customer_vat_rate' => null,
                'vat_rate' => null,
                'vat_verified_by' => auth()->id(),
                'vat_verified_at' => now(),
                'vat_verification_notes' => $data['verification_notes'],
            ]);
            $customer->taxDocuments()->where('document_type', 'vat_exemption_certificate')
                ->update([
                    'verification_status' => 'rejected',
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                    'verification_notes' => $data['verification_notes'],
                ]);
        });
        return back()->with('success', 'VAT exemption request rejected. Customer account decision is unchanged.');
    }
}
