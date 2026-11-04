<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentManufacturer;
use App\Models\EquipmentType; // Add if you have this model
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\KplcEquipmentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class EquipmentController extends Controller
{
 public function index(Request $request): View
{
    $search = trim(
        (string) $request->input('search', '')
    );

    $status = trim(
        (string) $request->input('status', '')
    );

    $configurationStatus = trim(
        (string) $request->input(
            'configuration_status',
            ''
        )
    );

    $categoryId = $request->input('category_id');
    $manufacturerId = $request->input('manufacturer_id');
    $typeId = $request->input('type_id');

    $equipment = Equipment::query()
        ->with([
            'categoryRecord',
            'manufacturerRecord',
            'pricingType',
        ])
        ->when(
            $search !== '',
            function ($query) use ($search) {
                $query->where(
                    function ($subQuery) use ($search) {
                        $subQuery
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'equipment_number',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'serial_number',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'model',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'categoryRecord',
                                function ($categoryQuery) use ($search) {
                                    $categoryQuery
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'code',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            )
                            ->orWhereHas(
                                'manufacturerRecord',
                                function ($manufacturerQuery) use ($search) {
                                    $manufacturerQuery
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'code',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            )
                            ->orWhereHas(
                                'pricingType',
                                function ($typeQuery) use ($search) {
                                    $typeQuery->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    );
                                }
                            );
                    }
                );
            }
        )
        ->when(
            $status !== '',
            fn ($query) => $query->where(
                'status',
                $status
            )
        )
        ->when(
            $configurationStatus === 'complete',
            fn ($query) =>
                $query->configurationComplete()
        )
        ->when(
            $configurationStatus === 'incomplete',
            fn ($query) =>
                $query->configurationIncomplete()
        )
        ->when(
            !empty($categoryId),
            fn ($query) => $query->where(
                'category_id',
                $categoryId
            )
        )
        ->when(
            !empty($manufacturerId),
            fn ($query) => $query->where(
                'manufacturer_id',
                $manufacturerId
            )
        )
        ->when(
            !empty($typeId),
            fn ($query) => $query->where(
                'type_id',
                $typeId
            )
        )
        ->latest('created_at')
        ->paginate(20)
        ->withQueryString();

    $categories = EquipmentCategory::query()
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'code',
            'is_active',
        ]);

    $manufacturers = EquipmentManufacturer::query()
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'code',
            'is_active',
        ]);

    $equipmentTypes = KplcEquipmentType::query()
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'rate',
        ]);

    $configurationStatistics = [
        'complete' => Equipment::query()
            ->configurationComplete()
            ->count(),

        'incomplete' => Equipment::query()
            ->configurationIncomplete()
            ->count(),

        'missing_category' => Equipment::query()
            ->whereNull('category_id')
            ->count(),

        'missing_manufacturer' => Equipment::query()
            ->whereNull('manufacturer_id')
            ->count(),

        'missing_type' => Equipment::query()
            ->whereNull('type_id')
            ->count(),
    ];

    return view(
        'admin.equipment.index',
        compact(
            'equipment',
            'categories',
            'manufacturers',
            'equipmentTypes',
            'configurationStatistics',
            'search',
            'status',
            'configurationStatus',
            'categoryId',
            'manufacturerId',
            'typeId'
        )
    );
}


public function create(): View
{
    $categories = EquipmentCategory::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'code',
            'is_active',
        ]);

    $manufacturers = EquipmentManufacturer::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'code',
            'is_active',
        ]);

    $equipmentTypes = KplcEquipmentType::query()
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'rate',
        ]);

    return view(
        'admin.equipment.create',
        compact(
            'categories',
            'manufacturers',
            'equipmentTypes'
        )
    );
}

    public function store(
    Request $request
): RedirectResponse {
    $validated = $request->validate(
        [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'serial_number' => [
                'required',
                'string',
                'max:255',
                'unique:equipment,serial_number',
            ],

            'model' => [
                'nullable',
                'string',
                'max:255',
            ],

            'category_id' => [
                'required',
                'integer',

                Rule::exists(
                    'equipment_categories',
                    'id'
                )->where(
                    fn ($query) =>
                        $query->where('is_active', true)
                ),
            ],

            'manufacturer_id' => [
                'required',
                'integer',

                Rule::exists(
                    'equipment_manufacturers',
                    'id'
                )->where(
                    fn ($query) =>
                        $query->where('is_active', true)
                ),
            ],

            'type_id' => [
                'required',
                'integer',
                'exists:kplc_equipment_types,id',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'in_use',
                    'under_repair',
                    'calibration_due',
                    'retired',
                ]),
            ],

            'condition' => [
                'required',
                Rule::in([
                    'excellent',
                    'good',
                    'fair',
                    'poor',
                    'needs_repair',
                ]),
            ],
        ],
        [
            'category_id.required' =>
                'Select an equipment category.',

            'manufacturer_id.required' =>
                'Select an equipment manufacturer.',

            'type_id.required' =>
                'Select an equipment type and pricing category.',
        ]
    );

    try {
        $equipment = DB::transaction(
            function () use ($validated) {
                return Equipment::query()->create([
                    'name' => trim($validated['name']),

                    'serial_number' =>
                        trim($validated['serial_number']),

                    'model' =>
                        !empty($validated['model'])
                            ? trim($validated['model'])
                            : null,

                    'category_id' =>
                        $validated['category_id'],

                    'manufacturer_id' =>
                        $validated['manufacturer_id'],

                    'type_id' =>
                        $validated['type_id'],

                    'status' =>
                        $validated['status'],

                    'condition' =>
                        $validated['condition'],
                ]);
            }
        );

        return redirect()
            ->route('equipment.show', $equipment)
            ->with(
                'success',
                'Equipment created successfully.'
            );
    } catch (Throwable $exception) {
        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'The equipment could not be created.'
            );
    }
}

    // public function show(Equipment $equipment)
    // {
    //     $equipment->load(['category', 'manufacturer', 'type', 'createdBy', 'updatedBy']);
    //     return view('admin.equipment.show', compact('equipment'));
    // }

   public function show(
    Equipment $equipment
): View {
    $equipment->loadMissing([
        'categoryRecord',
        'manufacturerRecord',
        'pricingType',
    ]);

    return view(
        'admin.equipment.show',
        compact('equipment')
    );
}

    public function edit(
    Equipment $equipment
): View {
    $equipment->loadMissing([
        'categoryRecord',
        'manufacturerRecord',
        'pricingType',
    ]);

    $categories = EquipmentCategory::query()
        ->where(function ($query) use ($equipment) {
            $query
                ->where('is_active', true)
                ->orWhere(
                    'id',
                    $equipment->category_id
                );
        })
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'code',
            'is_active',
        ]);

    $manufacturers = EquipmentManufacturer::query()
        ->where(function ($query) use ($equipment) {
            $query
                ->where('is_active', true)
                ->orWhere(
                    'id',
                    $equipment->manufacturer_id
                );
        })
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'code',
            'is_active',
        ]);

    $equipmentTypes = KplcEquipmentType::query()
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'rate',
        ]);

    return view(
        'admin.equipment.edit',
        compact(
            'equipment',
            'categories',
            'manufacturers',
            'equipmentTypes'
        )
    );
}

    public function update(
    Request $request,
    Equipment $equipment
): RedirectResponse {
    $validated = $request->validate(
        [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'equipment_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'serial_number' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'equipment',
                    'serial_number'
                )->ignore($equipment->id),
            ],

            'model' => [
                'nullable',
                'string',
                'max:255',
            ],

            'part_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'category_id' => [
                'required',
                'integer',

                Rule::exists(
                    'equipment_categories',
                    'id'
                )->where(
                    function ($query) use ($equipment) {
                        $query->where(
                            function ($innerQuery) use ($equipment) {
                                $innerQuery
                                    ->where('is_active', true)
                                    ->orWhere(
                                        'id',
                                        $equipment->category_id
                                    );
                            }
                        );
                    }
                ),
            ],

            'manufacturer_id' => [
                'required',
                'integer',

                Rule::exists(
                    'equipment_manufacturers',
                    'id'
                )->where(
                    function ($query) use ($equipment) {
                        $query->where(
                            function ($innerQuery) use ($equipment) {
                                $innerQuery
                                    ->where('is_active', true)
                                    ->orWhere(
                                        'id',
                                        $equipment->manufacturer_id
                                    );
                            }
                        );
                    }
                ),
            ],

            'type_id' => [
                'required',
                'integer',
                'exists:kplc_equipment_types,id',
            ],

            'accuracy_class' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'in_use',
                    'under_repair',
                    'calibration_due',
                    'retired',
                ]),
            ],

            'condition' => [
                'required',
                Rule::in([
                    'excellent',
                    'good',
                    'fair',
                    'poor',
                    'needs_repair',
                ]),
            ],

            'last_calibration_date' => [
                'nullable',
                'date',
            ],

            'next_calibration_date' => [
                'nullable',
                'date',
                'after_or_equal:last_calibration_date',
            ],

            'calibration_interval_days' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'calibration_laboratory' => [
                'nullable',
                'string',
                'max:255',
            ],

            'reference_standard_cert_no' => [
                'nullable',
                'string',
                'max:255',
            ],

            'reference_standard_cal_date' => [
                'nullable',
                'date',
            ],

            'reference_standard_due_date' => [
                'nullable',
                'date',
                'after_or_equal:reference_standard_cal_date',
            ],

            'reference_standard_accuracy' => [
                'nullable',
                'string',
                'max:255',
            ],

            'traceability_certificate_no' => [
                'nullable',
                'string',
                'max:255',
            ],

            'purchase_date' => [
                'nullable',
                'date',
            ],

            'purchase_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'supplier' => [
                'nullable',
                'string',
                'max:255',
            ],

            'warranty_until' => [
                'nullable',
                'date',
                'after_or_equal:purchase_date',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'assigned_to' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'training_required' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]
    );

    try {
        DB::transaction(
            function () use (
                $equipment,
                $validated
            ) {
                $equipment->update($validated);
            }
        );

        return redirect()
            ->route('equipment.show', $equipment)
            ->with(
                'success',
                'Equipment updated successfully.'
            );
    } catch (Throwable $exception) {
        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'The equipment could not be updated.'
            );
    }
}

    public function destroy(Equipment $equipment)
    {
        // Check if equipment can be deleted (e.g., no active calibrations, no pending tasks)
        // Add your business logic here

        $equipment->delete();

        return redirect()->route('equipment.index')
            ->with('success', 'Equipment deleted successfully!');
    }

    /**
     * Generate a unique equipment number
     */
    private function generateEquipmentNumber(): string
    {
        $prefix = 'EQ-';
        $year = date('Y');
        $lastEquipment = Equipment::where('equipment_number', 'LIKE', $prefix . $year . '%')
            ->orderBy('equipment_number', 'desc')
            ->first();

        if ($lastEquipment) {
            $lastNumber = (int) substr($lastEquipment->equipment_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $year . '-' . $newNumber;
    }

    /**
     * Bulk status update
     */
    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:equipment,id',
            'status' => ['required', Rule::in(['active', 'in_use', 'under_repair', 'calibration_due', 'retired'])],
        ]);

        Equipment::whereIn('id', $request->ids)->update(['status' => $request->status]);

        return response()->json(['message' => 'Status updated successfully']);
    }

    /**
     * Export equipment list
     */
    public function export(Equipment $equipment)
{
    // For PDF export using DomPDF or similar
    $pdf = Pdf::loadView('admin.equipment.export', compact('equipment'));
    return $pdf->download('equipment-' . $equipment->id . '.pdf');
}

    /**
     * Import equipment list
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        // Implement import logic
    }


}
