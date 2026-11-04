<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('edit_customers');
    }

    public function rules()
    {
        $customer = $this->route('customer');

        return [
            'company_name' => ['required', 'string', 'max:191'],
            'contact_person' => ['required', 'string', 'max:191'],
            'phone' => ['required', 'string', 'max:191', Rule::unique('customers', 'phone')->ignore($customer)],
            'email' => ['nullable', 'email', 'max:191', Rule::unique('customers', 'email')->ignore($customer)],
            'address' => ['required', 'string'],
            'tax_id' => ['nullable', 'string', 'max:191'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'string', Rule::in(['30_days', '60_days', '90_days'])],
            'status' => ['nullable', Rule::in(['active', 'pending', 'suspended'])],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
