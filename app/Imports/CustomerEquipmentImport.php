<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomerEquipmentImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $customerId;
    protected $errors = [];

    public function __construct($customerId)
    {
        $this->customerId = $customerId;
    }

    public function collection(Collection $rows)
    {
        $importedData = [];

        foreach ($rows as $index => $row) {
            // Skip if required fields are empty
            if (empty($row['equipment_name']) && empty($row['serial_number'])) {
                continue;
            }

            $importedData[] = [
                'customer_id' => $this->customerId,
                'equipment_name' => $row['equipment_name'] ?? '',
                'serial_number' => $row['serial_number'] ?? '',
                'manufacturer' => $row['manufacturer'] ?? '',
                'model' => $row['model'] ?? '',
                'accuracy_class' => $row['accuracy_class'] ?? '',
                'ratings' => $row['ratings'] ?? '',
                'equipment_type' => $row['equipment_type'] ?? '',
                'equipment_condition' => $row['equipment_condition'] ?? 'Good',
                'last_calibration_date' => !empty($row['last_calibration_date']) ? date('Y-m-d', strtotime($row['last_calibration_date'])) : null,
                'notes' => $row['notes'] ?? '',
            ];
        }

        return $importedData;
    }

    public function rules(): array
    {
        return [
            'equipment_name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:191',
            'manufacturer' => 'nullable|string|max:191',
            'model' => 'nullable|string|max:191',
            'accuracy_class' => 'nullable|string|max:50',
            'ratings' => 'nullable|string|max:191',
            'equipment_type' => 'nullable|string|max:191',
            'equipment_condition' => 'nullable|in:Excellent,Good,Fair,Poor,Damaged',
            'last_calibration_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
