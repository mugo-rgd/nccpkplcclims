<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRegistrationRequest;
use App\Models\Customer;
use App\Models\CustomerTaxDocument;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RegisterController extends Controller
{
    /**
     * Display customer registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Register customer and submit tax documents.
     */
   public function register(CustomerRegistrationRequest $request)
{
    $validated = $request->validated();

    /*
     * Identify who is performing the registration
     * BEFORE creating the new customer account.
     */
    $registrar = Auth::user();

    $isStaffRegistration = $registrar !== null
        && $registrar->hasAnyRole([
            'super_admin',
            'lab_manager',
            'supervisor',
        ]);

    $vatRequested = $request->boolean(
        'vat_exemption_requested'
    );

    $uploadedPaths = [];


        try {
            DB::beginTransaction();

            /*
             * STEP 1: Create customer user.
             */
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make(
                    $validated['password']
                ),
                'role' => 'customer',
                'is_active' => true,
                'status' => true,
                'designation' => 'Customer',
                'department' => 'Customer',
            ]);

            /*
             * Assign Spatie role.
             * The customer role must exist in roles table.
             */
            $user->assignRole('customer');

            /*
             * STEP 2: Create customer record.
             *
             * Customer account approval and VAT
             * verification are independent processes.
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
                    ?? $validated['email'],

                'address' =>
                    $validated['address'],

                // Existing tax_id stores KRA PIN.
                'tax_id' =>
                    $validated['tax_id'],

                'payment_terms' => '30_days',
                'registration_date' => now(),

                // Await customer account approval.
                'status' => 'pending',
                'is_active' => true,

                // Customer cannot approve their VAT.
                'vat_exemption_status' =>
                    $vatRequested ? 'pending' : 'none',

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

                'notes' => [
                    'created_by' => 'Self Registration',
                    'created_at' => now()->toDateTimeString(),
                    'customer_type' => 'external',
                    'registration_ip' => $request->ip(),
                ],
            ]);

            /*
             * STEP 3: Store KRA PIN certificate.
             *
             * Store privately, not on public disk.
             */
            $kraFile = $request->file(
                'kra_pin_certificate'
            );

            $kraPath = $kraFile->store(
                "customer-tax-documents/{$customer->id}",
                'local'
            );

            if (!$kraPath) {
                throw new \RuntimeException(
                    'Unable to store KRA PIN certificate.'
                );
            }

            $uploadedPaths[] = $kraPath;

            CustomerTaxDocument::create([
                'customer_id' => $customer->id,
                'document_type' => 'kra_pin_certificate',
                'file_path' => $kraPath,
                'original_filename' =>
                    $kraFile->getClientOriginalName(),
                'mime_type' => $kraFile->getMimeType(),
                'file_size' => $kraFile->getSize(),
                'verification_status' => 'pending',
                'uploaded_by' => $user->id,
                'verified_by' => null,
                'verified_at' => null,
                'verification_notes' => null,
            ]);

            /*
             * STEP 4: Store VAT exemption certificate
             * only if exemption was requested.
             */
            if ($vatRequested) {
                $vatFile = $request->file(
                    'vat_exemption_certificate'
                );

                $vatPath = $vatFile->store(
                    "customer-tax-documents/{$customer->id}",
                    'local'
                );

                if (!$vatPath) {
                    throw new \RuntimeException(
                        'Unable to store VAT exemption certificate.'
                    );
                }

                $uploadedPaths[] = $vatPath;

                CustomerTaxDocument::create([
                    'customer_id' => $customer->id,
                    'document_type' =>
                        'vat_exemption_certificate',
                    'file_path' => $vatPath,
                    'original_filename' =>
                        $vatFile->getClientOriginalName(),
                    'mime_type' => $vatFile->getMimeType(),
                    'file_size' => $vatFile->getSize(),
                    'verification_status' => 'pending',
                    'uploaded_by' => $user->id,
                    'verified_by' => null,
                    'verified_at' => null,
                    'verification_notes' => null,
                ]);
            }

            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();

            /*
             * File storage is outside DB transactions.
             * Clean up files if registration fails.
             */
            foreach ($uploadedPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            Log::error(
                'Customer registration failed',
                [
                    'email' => $request->input('email'),
                    'error' => $e->getMessage(),
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
                    'Registration failed. Please try again.'
                );
        }

      /*
|--------------------------------------------------------------------------
| STEP 5: Fire registration event
|--------------------------------------------------------------------------
*/
event(new Registered($user));

/*
|--------------------------------------------------------------------------
| STEP 6: Determine registration flow
|--------------------------------------------------------------------------
|
| Staff registration:
|   - Do not log in as the newly created customer.
|   - Keep the current staff session.
|   - Redirect to the customer management page.
|
| Customer self-registration:
|   - Log in as the newly created customer.
|   - Redirect to the customer dashboard.
|
*/

/*
 * CASE 1: Registration performed by KPLC staff.
 */
if ($isStaffRegistration) {

    return redirect()
        ->route('admin.customers.index')
        ->with(
            'success',
            $vatRequested
                ? 'Customer registered successfully. '
                    . 'VAT exemption documents are pending '
                    . 'Lab Manager verification.'
                : 'Customer registered successfully '
                    . 'and is awaiting account approval.'
        );
}

/*
 * CASE 2: Customer self-registration.
 */
Auth::login($user);

$request->session()->regenerate();

return redirect()
    ->route('dashboard')
    ->with(
        'success',
        $vatRequested
            ? 'Your account has been created successfully. '
                . 'Your VAT exemption documents are '
                . 'awaiting Lab Manager verification.'
            : 'Your account has been created successfully '
                . 'and is awaiting approval.'
    );
}

    /**
     * Authentication guard.
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
