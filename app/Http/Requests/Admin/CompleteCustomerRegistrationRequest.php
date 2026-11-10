<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteCustomerRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole([
            'super_admin',
            'lab_manager',
            'supervisor',
        ]) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim(
                (string) $this->input('email', '')
            )),
            'tax_id' => strtoupper(preg_replace(
                '/\s+/',
                '',
                (string) $this->input('tax_id', '')
            )),
            'vat_exemption_requested' =>
                $this->boolean('vat_exemption_requested'),
        ]);
    }

    public function rules(): array
    {
        $vatRequested = $this->boolean(
            'vat_exemption_requested'
        );

        return [
            'name' => [
                'required', 'string', 'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'phone_number' => [
                'required', 'string', 'max:20',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'company_name' => [
                'required', 'string', 'max:255',
            ],

            'contact_person' => [
                'required', 'string', 'max:255',
            ],

            'company_phone' => [
                'required', 'string', 'max:20',
            ],

            'company_email' => [
                'nullable', 'email', 'max:255',
            ],

            'address' => [
                'required', 'string',
            ],

            'tax_id' => [
                'required',
                'string',
                'regex:/^[AP][0-9]{9}[A-Z]$/',
                'max:20',
            ],

            'vat_exemption_requested' => [
                'required', 'boolean',
            ],

            'vat_exemption_certificate_number' => [
                Rule::requiredIf($vatRequested),
                'nullable', 'string', 'max:255',
            ],

            'vat_exemption_expiry_date' => [
                Rule::requiredIf($vatRequested),
                'nullable',
                'date',
                'after_or_equal:today',
            ],

            'kra_pin_certificate' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'vat_exemption_certificate' => [
                Rule::requiredIf($vatRequested),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' =>
                'This email address is already registered.',

            'tax_id.regex' =>
                'Enter a valid KRA PIN, for example A123456789B.',

            'kra_pin_certificate.required' =>
                'The KRA PIN certificate is required.',

            'vat_exemption_certificate.required' =>
                'Upload the VAT exemption certificate.',

            'vat_exemption_certificate_number.required' =>
                'Enter the VAT exemption certificate number.',

            'vat_exemption_expiry_date.required' =>
                'Enter the VAT exemption expiry date.',
        ];
    }
}
