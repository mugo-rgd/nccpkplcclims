<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCalibrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        return $user->hasRole('customer')
            || $user->can('create_calibrations');
    }

    public function rules(): array
    {
        $isCustomer = $this->user()?->hasRole('customer') ?? false;

        $rules = [
            'customer_id' => [
                Rule::requiredIf(!$isCustomer),
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            'items' => ['required', 'array', 'min:1', 'max:50'],

            'items.*.equipment_name' => ['required', 'string', 'max:255'],
            'items.*.serial_number' => ['required', 'string', 'max:255'],
            'items.*.manufacturer' => ['nullable', 'string', 'max:255'],
            'items.*.model' => ['nullable', 'string', 'max:255'],
            'items.*.accuracy_class' => ['nullable', 'string', 'max:50'],
            'items.*.ratings' => ['nullable', 'string', 'max:255'],
            'items.*.equipment_type_id' => ['nullable', 'integer', 'exists:kplc_equipment_types,id'],
            'items.*.equipment_condition' => ['required', Rule::in(['Excellent', 'Good', 'Fair', 'Poor', 'Damaged'])],
            'items.*.last_calibration_date' => ['nullable', 'date', 'before_or_equal:today'],
            'items.*.service_types' => ['required', 'array', 'min:1'],
            'items.*.service_types.*' => ['required', 'integer', 'distinct', 'exists:kplc_service_types,id'],

            // Per-item verification
            'items.*.verification_of_item' => ['nullable', 'string', 'max:2000'],
            'items.*.review_of_service' => ['nullable', 'string', 'max:3000'],
            'items.*.validity_period_required' => ['nullable', Rule::in(['yes', 'no'])],
            'items.*.validity_period' => ['nullable', 'string', 'max:100'],
            'items.*.validity_period_date' => ['nullable', 'date', 'after:today'],

            // Per-item lab equipment
            'items.*.laboratory_equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],

            'notes' => ['nullable', 'string', 'max:3000'],
        ];

        if (!$isCustomer) {
            $rules = array_merge($rules, [
                'equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],
                'scheduled_date' => ['nullable', 'date', 'after:now'],
                'performed_by' => ['nullable', 'integer', 'exists:users,id'],
                'calibration_location' => ['required', Rule::in(['laboratory', 'on_site'])],
                'priority' => ['required', Rule::in(['normal', 'high', 'urgent'])],
                'number_of_staff' => ['nullable', 'required_if:calibration_location,on_site', 'integer', 'min:1', 'max:50'],
                'travel_days' => ['nullable', 'required_if:calibration_location,on_site', 'integer', 'min:1', 'max:365'],
                'site_distance_km' => ['nullable', 'required_if:calibration_location,on_site', 'numeric', 'min:0'],
                'per_diem_rate' => ['nullable', 'required_if:calibration_location,on_site', 'numeric', 'min:0'],
                'transport_rate' => ['nullable', 'required_if:calibration_location,on_site', 'numeric', 'min:0'],
                'other_costs' => ['nullable', 'numeric', 'min:0'],
                'other_costs_description' => ['nullable', 'string', 'max:500'],
                'test_parameters' => ['nullable', 'array'],
                'test_parameters.*.name' => ['nullable', 'string', 'max:255'],
                'test_parameters.*.nominal_value' => ['nullable', 'numeric'],
                'test_parameters.*.unit' => ['nullable', 'string', 'max:50'],
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Select the customer who owns the equipment.',
            'items.required' => 'Add at least one equipment item.',
            'items.*.equipment_name.required' => 'Enter the equipment name for each item.',
            'items.*.serial_number.required' => 'Enter the serial number for each item.',
            'items.*.equipment_condition.required' => 'Select the equipment condition for each item.',
            'items.*.service_types.required' => 'Select at least one service type for each item.',
            'items.*.service_types.min' => 'Select at least one service type for each item.',
            'equipment_id.required' => 'Select the MCL laboratory equipment.',
            'calibration_location.required' => 'Select the calibration location.',
            'scheduled_date.after' => 'The scheduled date must be in the future.',
        ];
    }
}
