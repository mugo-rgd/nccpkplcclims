<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerRegistrationRequest extends FormRequest
{
    public function authorize(): bool
{
    $user = $this->user();

    return $user === null
        || $user->hasAnyRole([
            'super_admin',
            'lab_manager',
            'supervisor',
        ]);
}

    protected function prepareForValidation(): void
{
    $this->merge([
        'tax_id' => strtoupper(
            preg_replace(
                '/\s+/',
                '',
                (string) $this->input('tax_id', '')
            )
        ),

        'vat_exemption_requested' => $this->boolean(
            'vat_exemption_requested'
        ),

        'vat_exemption_certificate_number' => trim(
            (string) $this->input(
                'vat_exemption_certificate_number',
                ''
            )
        ),
    ]);
}

    public function rules(): array
    {
        return [
            // Account information
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'phone_number' => [
                'required', 'string', 'max:20'
            ],
          'password' => [
    'required',
    'string',
    Password::min(8)
        ->letters()
        ->numbers(),
    'confirmed',
],

            // Company information
            'company_name' => [
                'required', 'string', 'max:255'
            ],
            'contact_person' => [
                'required', 'string', 'max:255'
            ],
            'company_phone' => [
                'required', 'string', 'max:20'
            ],
            'company_email' => [
                'nullable', 'email', 'max:255'
            ],
            'address' => [
                'required', 'string'
            ],

            // KRA PIN
            'tax_id' => [
                'required',
                'string',
                'regex:/^[AP][0-9]{9}[A-Z]$/',
                'max:20',
            ],

            // VAT exemption request
            'vat_exemption_requested' => [
                'required', 'boolean'
            ],

            'vat_exemption_certificate_number' => [
                Rule::requiredIf(
                    fn () => $this->boolean(
                        'vat_exemption_requested'
                    )
                ),
                'nullable',
                'string',
                'max:255',
            ],

            'vat_exemption_expiry_date' => [
                Rule::requiredIf(
                    fn () => $this->boolean(
                        'vat_exemption_requested'
                    )
                ),
                'nullable',
                'date',
                'after_or_equal:today',
            ],

            // Supporting documents
            'kra_pin_certificate' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'vat_exemption_certificate' => [
                Rule::requiredIf(
                    fn () => $this->boolean(
                        'vat_exemption_requested'
                    )
                ),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
            'terms' => [
              'accepted',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'Please enter your full name.',

            'email.required' =>
                'Please enter your email address.',

            'email.unique' =>
                'This email is already registered.',

            'password.required' =>
                'Please enter a password.',

            'password.min' =>
                'Password must contain at least 8 characters.',

            'password.confirmed' =>
                'Passwords do not match.',

            'company_name.required' =>
                'Please enter your company name.',

            'contact_person.required' =>
                'Please enter the contact person name.',

            'company_phone.required' =>
                'Please enter the company phone number.',

            'address.required' =>
                'Please enter your physical address.',

            'tax_id.required' =>
                'Please enter your KRA PIN.',

            'tax_id.regex' =>
                'Enter a valid KRA PIN, for example A123456789B.',

            'vat_exemption_requested.required' =>
                'Please indicate whether you are requesting VAT exemption.',

            'vat_exemption_certificate_number.required' =>
                'Enter your VAT exemption certificate number.',

            'vat_exemption_expiry_date.required' =>
                'Enter the VAT exemption certificate expiry date.',

            'vat_exemption_expiry_date.after_or_equal' =>
                'The VAT exemption certificate must not be expired.',

            'kra_pin_certificate.required' =>
                'Please upload your KRA PIN certificate.',

            'vat_exemption_certificate.required' =>
                'Please upload your VAT exemption certificate.',

            'kra_pin_certificate.max' =>
                'The KRA PIN document must not exceed 5 MB.',

            'vat_exemption_certificate.max' =>
                'The VAT exemption document must not exceed 5 MB.',
                'terms.accepted' =>
    'You must confirm that the information is accurate.',
        ];
    }
}
