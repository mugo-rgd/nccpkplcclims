<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        // Allow all authenticated users
        return auth()->check();
    }

    public function rules()
    {
        return [
            'company_name' => ['required', 'string', 'max:191'],
            'contact_person' => ['required', 'string', 'max:191'],
            'phone' => ['required', 'string', 'max:191', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:191', 'unique:customers,email', 'unique:users,email'],
            'address' => ['required', 'string'],
            'tax_id' => ['nullable', 'string', 'max:191'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'string', Rule::in(['30_days', '60_days', '90_days'])],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'company_name.required' => 'Company name is required.',
            'contact_person.required' => 'Contact person name is required.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email is already registered.',
        ];
    }
}
