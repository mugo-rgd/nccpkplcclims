<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestResultRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'parameter' => ['required', 'string', 'max:100'],
            'nominal_value' => ['required', 'numeric'],
            'measured_value' => ['required', 'numeric'],
            'tolerance_min' => ['required', 'numeric'],
            'tolerance_max' => ['required', 'numeric', 'gt:tolerance_min'],
            'unit' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages()
    {
        return [
            'parameter.required' => 'Parameter name is required.',
            'nominal_value.required' => 'Nominal value is required.',
            'measured_value.required' => 'Measured value is required.',
            'tolerance_min.required' => 'Tolerance minimum is required.',
            'tolerance_max.required' => 'Tolerance maximum is required.',
            'tolerance_max.gt' => 'Tolerance maximum must be greater than tolerance minimum.',
            'unit.required' => 'Unit is required.',
        ];
    }
}
