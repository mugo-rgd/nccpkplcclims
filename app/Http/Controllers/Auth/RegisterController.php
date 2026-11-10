<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRegistrationRequest;
use App\Models\Customer;
use App\Models\CustomerTaxDocument;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Authorized Customer Registrars
    |--------------------------------------------------------------------------
    |
    | Guests may register themselves.
    | Only these authenticated staff roles may register
    | customers on behalf of another person.
    |
    */

    private const STAFF_ROLES = [
        'super_admin',
        'lab_manager',
        'supervisor',
    ];

    private const DOCUMENT_DISK = 'local';

    /*
    |--------------------------------------------------------------------------
    | Display Registration Form
    |--------------------------------------------------------------------------
    */

    public function showRegistrationForm(): View
    {
        $this->authorizeRegistrar();

        return view('auth.register');
    }

    /*
    |--------------------------------------------------------------------------
    | Register Customer
    |--------------------------------------------------------------------------
    */

    public function register(
        CustomerRegistrationRequest $request
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | STEP 1: Identify and authorize registrar
        |--------------------------------------------------------------------------
        */

        $isStaffRegistration = $this->authorizeRegistrar();

        $registrarId = Auth::id();

        $validated = $request->validated();

        $vatRequested = $request->boolean(
            'vat_exemption_requested'
        );

        /*
         * Track stored files so they can be removed
         * if database creation fails.
         */
        $uploadedPaths = [];

        $user = null;

        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | STEP 2: Create customer user account
            |--------------------------------------------------------------------------
            |
            | New customer accounts remain inactive
            | until approved by authorized KPLC staff.
            |
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

            /*
             * Assign Spatie customer role.
             */
            $user->assignRole('customer');

            /*
            |--------------------------------------------------------------------------
            | STEP 3: Create customer profile
            |--------------------------------------------------------------------------
            |
            | Account approval and VAT verification
            | are separate workflows.
            |
            */

            $customer = Customer::create([
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

                /*
                 * Existing tax_id stores KRA PIN.
                 */
                'tax_id' =>
                    $validated['tax_id'],

                'payment_terms' => '30_days',

                'registration_date' => now(),

                /*
                 * Await account approval.
                 */
                'status' => 'pending',

                'is_active' => false,

                /*
                 * VAT exemption is not automatically
                 * approved during registration.
                 */
                'vat_exemption_status' =>
                    $vatRequested
                        ? 'pending'
                        : 'none',

                'customer_vat_rate' => null,

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

                /*
                 * Registration audit information.
                 *
                 * Customer model must cast notes
                 * to array.
                 */
                'notes' => [
                    'registration_source' =>
                        $isStaffRegistration
                            ? 'staff_registration'
                            : 'self_registration',

                    'created_by_user_id' =>
                        $isStaffRegistration
                            ? $registrarId
                            : null,

                    'created_at' =>
                        now()->toDateTimeString(),

                    'customer_type' => 'external',

                    'registration_ip' =>
                        $request->ip(),
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | STEP 4: Store mandatory KRA PIN certificate
            |--------------------------------------------------------------------------
            */

            $this->storeTaxDocument(
                request: $request,
                customer: $customer,
                user: $user,
                documentType: 'kra_pin_certificate',
                uploadedPaths: $uploadedPaths
            );

            /*
            |--------------------------------------------------------------------------
            | STEP 5: Store VAT exemption certificate
            |--------------------------------------------------------------------------
            |
            | Required only when VAT exemption
            | has been requested.
            |
            */

            if ($vatRequested) {

                $this->storeTaxDocument(
                    request: $request,
                    customer: $customer,
                    user: $user,
                    documentType: 'vat_exemption_certificate',
                    uploadedPaths: $uploadedPaths
                );
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 6: Commit registration
            |--------------------------------------------------------------------------
            */

            DB::commit();

        } catch (Throwable $e) {

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            /*
             * Remove uploaded documents if the
             * registration transaction fails.
             */
            foreach ($uploadedPaths as $path) {

                try {
                    Storage::disk(
                        self::DOCUMENT_DISK
                    )->delete($path);

                } catch (Throwable $cleanupException) {

                    Log::warning(
                        'Unable to clean up customer tax document',
                        [
                            'path' => $path,
                            'exception' => $cleanupException,
                        ]
                    );
                }
            }

            Log::error(
                'Customer registration failed',
                [
                    'email' => $request->input('email'),
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
                    'Customer registration failed. '
                    . 'Please try again or contact '
                    . 'the system administrator.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 7: Dispatch registration event
        |--------------------------------------------------------------------------
        |
        | Registration has already been committed.
        | Event listener failures should not cause
        | duplicate customer registrations.
        |
        */

        try {

            event(new Registered($user));

        } catch (Throwable $e) {

            Log::error(
                'Customer registration event failed',
                [
                    'user_id' => $user->id,
                    'exception' => $e,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 8: Staff registration
        |--------------------------------------------------------------------------
        |
        | Preserve the existing staff session.
        | Never authenticate as the new customer.
        |
        */

        if ($isStaffRegistration) {

            $message =
                'Customer registered successfully. '
                . 'The account is awaiting approval.';

            if ($vatRequested) {

                $message .=
                    ' VAT exemption documents are '
                    . 'awaiting Lab Manager verification.';
            }

            return redirect()
                ->route('admin.customers.index')
                ->with('success', $message);
        }

        /*
        |--------------------------------------------------------------------------
        | STEP 9: Public self-registration
        |--------------------------------------------------------------------------
        |
        | Do not automatically log in the customer.
        | Account activation requires approval.
        |
        */

        $message =
            'Registration submitted successfully. '
            . 'Your account is awaiting approval '
            . 'by the Meter Calibration Laboratory.';

        if ($vatRequested) {

            $message .=
                ' Your VAT exemption documents '
                . 'will be reviewed separately.';
        }

        return redirect()
            ->route('login')
            ->with('success', $message);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Customer Tax Document
    |--------------------------------------------------------------------------
    |
    | Files are stored privately on the local disk.
    | File metadata is recorded in the database.
    |
    */

    private function storeTaxDocument(
        CustomerRegistrationRequest $request,
        Customer $customer,
        User $user,
        string $documentType,
        array &$uploadedPaths
    ): void {

        /*
         * Confirm that the uploaded file exists.
         */
        if (!$request->hasFile($documentType)) {

            throw new RuntimeException(
                "Missing required document: {$documentType}"
            );
        }

        $file = $request->file($documentType);

        /*
         * Confirm that the upload is valid.
         */
        if (!$file || !$file->isValid()) {

            throw new RuntimeException(
                "Invalid uploaded document: {$documentType}"
            );
        }

        /*
         * Store document in customer-specific
         * private directory.
         */
        $directory =
            "customer-tax-documents/{$customer->id}";

        $path = $file->store(
            $directory,
            self::DOCUMENT_DISK
        );

        if ($path === false) {

            throw new RuntimeException(
                "Unable to store document: {$documentType}"
            );
        }

        /*
         * Track file for rollback cleanup.
         */
        $uploadedPaths[] = $path;

        /*
         * Save document metadata.
         */
        CustomerTaxDocument::create([
            'customer_id' => $customer->id,

            'document_type' => $documentType,

            'file_path' => $path,

            'original_filename' =>
                $file->getClientOriginalName(),

            'mime_type' =>
                $file->getMimeType(),

            'file_size' =>
                $file->getSize(),

            'verification_status' => 'pending',

            'uploaded_by' => $user->id,

            'verified_by' => null,

            'verified_at' => null,

            'verification_notes' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Authorize Customer Registrar
    |--------------------------------------------------------------------------
    |
    | Guests:
    |   Allowed to self-register.
    |
    | Authorized KPLC staff:
    |   Allowed to register customers.
    |
    | Other authenticated users:
    |   Forbidden.
    |
    */

    private function authorizeRegistrar(): bool
    {
        $registrar = Auth::user();

        /*
         * Guest self-registration.
         */
        if ($registrar === null) {
            return false;
        }

        /*
         * Only authorized staff may create
         * customer accounts while authenticated.
         */
        abort_unless(
            $registrar->hasAnyRole(
                self::STAFF_ROLES
            ),
            403,
            'You are not authorized to register customers.'
        );

        return true;
    }
}
