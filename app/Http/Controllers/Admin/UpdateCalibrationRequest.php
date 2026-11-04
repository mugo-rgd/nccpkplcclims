<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalibrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Customer Information
            'customer_id' => 'nullable|exists:customers,id',

            // Equipment Details
            'equipment_name' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:150',
            'model' => 'nullable|string|max:100',
            'accuracy_class' => 'nullable|string|max:50',
            'ratings' => 'nullable|string|max:100',
            'equipment_type' => 'nullable|string|max:100',
            'equipment_type_id' => 'nullable|exists:kplc_equipment_types,id',
            'equipment_condition' => 'nullable|string|max:50',
            'last_calibration_date' => 'nullable|date',

            // MCL Equipment
            'equipment_id' => 'nullable|exists:equipment,id',

            // Calibration Details
            'scheduled_date' => 'nullable|date',
            'performed_by' => 'nullable|exists:users,id',
            'calibration_location' => 'nullable|in:laboratory,on_site',
            'priority' => 'nullable|in:normal,high,urgent',

            // Service Types
            'service_types' => 'nullable|array',
            'service_types.*' => 'exists:kplc_service_types,id',

            // Test Parameters
            'test_parameters' => 'nullable|array',
            'test_parameters.*.name' => 'nullable|string',
            'test_parameters.*.nominal_value' => 'nullable|numeric',
            'test_parameters.*.unit' => 'nullable|string',

            // Test Results
            'test_results' => 'nullable|array',
            'test_results.*.parameter' => 'nullable|string',
            'test_results.*.nominal_value' => 'nullable|numeric',
            'test_results.*.measured_value' => 'nullable|numeric',
            'test_results.*.tolerance_min' => 'nullable|numeric',
            'test_results.*.tolerance_max' => 'nullable|numeric',
            'test_results.*.unit' => 'nullable|string',
            'test_results.*.is_within_tolerance' => 'nullable|boolean',

            // Status and Notes
            'status' => 'nullable|in:pending,quoted,paid,assigned,in_progress,testing,completed,certified,rejected,dispatched',
            'notes' => 'nullable|string',
            'assignment_notes' => 'nullable|string',

            // On-Site Costs
            'per_diem_cost' => 'nullable|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'other_costs' => 'nullable|numeric|min:0',
            'other_costs_description' => 'nullable|string',
            'number_of_staff' => 'nullable|integer|min:1',
            'travel_days' => 'nullable|integer|min:1',
            'site_distance_km' => 'nullable|numeric|min:0',

            // Quote & Payment
            'quote_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:pending,partial,paid',
            'payment_reference' => 'nullable|string|max:50',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => 'The selected customer does not exist.',
            'equipment_id.exists' => 'The selected equipment does not exist.',
            'performed_by.exists' => 'The selected technician does not exist.',
            'equipment_type_id.exists' => 'The selected equipment type does not exist.',
            'service_types.*.exists' => 'One or more selected service types do not exist.',
            'status.in' => 'Invalid status value.',
            'calibration_location.in' => 'Invalid calibration location.',
            'priority.in' => 'Invalid priority value.',
            'payment_status.in' => 'Invalid payment status.',
        ];
    }
}
