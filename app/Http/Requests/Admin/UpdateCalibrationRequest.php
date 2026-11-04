<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCalibrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('customer') === false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:calibration_items,id'],
            'items.*.equipment_name' => ['required', 'string', 'max:255'],
            'items.*.serial_number' => ['required', 'string', 'max:255'],
            'items.*.manufacturer' => ['nullable', 'string', 'max:255'],
            'items.*.model' => ['nullable', 'string', 'max:255'],
            'items.*.accuracy_class' => ['nullable', 'string', 'max:50'],
            'items.*.ratings' => ['nullable', 'string', 'max:255'],
            'items.*.equipment_type_id' => ['nullable', 'integer', 'exists:kplc_equipment_types,id'],
            'items.*.equipment_condition' => ['required', Rule::in(['Excellent', 'Good', 'Fair', 'Poor', 'Damaged'])],
            'items.*.last_calibration_date' => ['nullable', 'date'],
            'items.*.service_types' => ['nullable', 'array'],
            'items.*.service_types.*' => ['integer', 'exists:kplc_service_types,id'],

            // Per-item verification (must match blade)
            'items.*.verification_of_item' => ['nullable', 'string', 'max:2000'],
            'items.*.review_of_service' => ['nullable', 'string', 'max:3000'],
            'items.*.validity_period_required' => ['nullable', Rule::in(['yes', 'no'])],
            'items.*.validity_period' => ['nullable', 'string', 'max:100'],
            'items.*.validity_period_date' => ['nullable', 'date'],

            // Per-item lab equipment
            'items.*.laboratory_equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],

            // Calibration level
            'equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],
            'scheduled_date' => ['nullable', 'date'],
            'performed_by' => ['nullable', 'integer', 'exists:users,id'],
            'calibration_location' => ['required', Rule::in(['laboratory', 'on_site'])],
            'priority' => ['nullable', Rule::in(['normal', 'high', 'urgent'])],
            'test_parameters' => ['nullable', 'array'],
            'status' => ['required', Rule::in(['pending', 'quoted', 'pending_payment', 'paid', 'assigned', 'in_progress', 'testing', 'completed', 'certified', 'rejected'])],
            'reassignment_reason' => ['nullable', 'string', 'min:10', 'max:500'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
