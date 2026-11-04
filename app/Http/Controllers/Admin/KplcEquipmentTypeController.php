<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KplcEquipmentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;
use App\Services\ConfigurationAuditService;

class KplcEquipmentTypeController extends Controller
{
    public function __construct(
    private readonly ConfigurationAuditService $auditService
) {
}
    /**
     * Display equipment types and published pricing.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $equipmentTypes = KplcEquipmentType::query()
            ->withCount('calibrations')
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'rate',
                                'like',
                                '%' . $search . '%'
                            );
                    });
                }
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $statistics = [
            'total' => KplcEquipmentType::query()->count(),

            'average_rate' => (float) (
                KplcEquipmentType::query()->avg('rate') ?? 0
            ),

            'minimum_rate' => (float) (
                KplcEquipmentType::query()->min('rate') ?? 0
            ),

            'maximum_rate' => (float) (
                KplcEquipmentType::query()->max('rate') ?? 0
            ),
        ];

        return view(
            'admin.equipment-types.index',
            compact(
                'equipmentTypes',
                'statistics',
                'search'
            )
        );
    }

    /**
     * Display the create form.
     */

    public function create(): View
{
    return view('admin.equipment-types.create');
}

    /**
     * Store a new equipment type and rate.
     */
    public function store(Request $request): RedirectResponse
{
    $validated = $request->validate(
        [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:kplc_equipment_types,name',
            ],

            'rate' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],
        ],
        [
            'name.required' =>
                'The equipment type name is required.',

            'name.unique' =>
                'This equipment type already exists.',

            'rate.required' =>
                'The laboratory rate is required.',

            'rate.numeric' =>
                'The laboratory rate must be numeric.',

            'rate.min' =>
                'The laboratory rate cannot be negative.',
        ]
    );

    try {
        $equipmentType = DB::transaction(
            function () use ($validated) {
                return KplcEquipmentType::query()->create([
                    'name' => trim($validated['name']),

                    'rate' => round(
                        (float) $validated['rate'],
                        2
                    ),
                ]);
            }
        );

        $this->auditService->log(
            action: 'equipment_pricing_created',
            subject: $equipmentType,
            description:
                "Created equipment pricing for {$equipmentType->name}.",
            newValues: $equipmentType->only([
                'name',
                'rate',
            ])
        );

        return redirect()
            ->route('equipment-types.index')
            ->with(
                'success',
                'Equipment type and pricing created successfully.'
            );
    } catch (\Throwable $exception) {
        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'The equipment type could not be created. Please try again.'
            );
    }
}

    /**
     * Display one equipment type.
     */
    public function show(
        KplcEquipmentType $equipmentType
    ): View {
        $equipmentType->loadCount('calibrations');

        $recentCalibrations = $equipmentType
            ->calibrations()
            ->with('customer')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view(
            'admin.equipment-types.show',
            compact(
                'equipmentType',
                'recentCalibrations'
            )
        );
    }

    /**
     * Display the edit form.
     */
    public function edit(
        KplcEquipmentType $equipmentType
    ): View {
        return view(
            'admin.equipment-types.edit',
            compact('equipmentType')
        );
    }

    /**
     * Update an equipment type and rate.
     */
    public function update(
        Request $request,
        KplcEquipmentType $equipmentType
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:kplc_equipment_types,name,' .
                        $equipmentType->id,
                ],

                'rate' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999999.99',
                ],
            ],
            [
                'name.required' =>
                    'The equipment type name is required.',

                'name.unique' =>
                    'Another equipment type already uses this name.',

                'rate.required' =>
                    'The laboratory rate is required.',

                'rate.numeric' =>
                    'The laboratory rate must be numeric.',

                'rate.min' =>
                    'The laboratory rate cannot be negative.',
            ]
        );

        try {

            $oldValues = $equipmentType->only([
    'name',
    'rate',
]);
            DB::transaction(
                function () use (
                    $equipmentType,
                    $validated
                ) {
                    $equipmentType->update([
                        'name' => trim($validated['name']),
                        'rate' => round(
                            (float) $validated['rate'],
                            2
                        ),
                    ]);
                }
            );

            $equipmentType->refresh();

$this->auditService->log(
    action: 'equipment_pricing_updated',
    subject: $equipmentType,
    description:
        "Updated equipment pricing for {$equipmentType->name}.",
    oldValues: $oldValues,
    newValues: $equipmentType->only([
        'name',
        'rate',
    ])
);

            return redirect()
                ->route('equipment-types.index')
                ->with(
                    'success',
                    'Equipment type and pricing updated successfully.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'The equipment type could not be updated. Please try again.'
                );
        }
    }

    /**
     * Delete an equipment type.
     *
     * Records already used by calibrations cannot be deleted.
     */
    public function destroy(
        KplcEquipmentType $equipmentType
    ): RedirectResponse {
        if ($equipmentType->calibrations()->exists()) {
            return back()->with(
                'error',
                'This equipment type cannot be deleted because it is already used by one or more calibrations.'
            );
        }

        try {
            DB::transaction(function () use ($equipmentType) {
                $this->auditService->log(
    action: 'equipment_pricing_deleted',
    subject: $equipmentType,
    description:
        "Deleted equipment pricing for {$equipmentType->name}.",
    oldValues: $equipmentType->only([
        'name',
        'rate',
    ])
);

$equipmentType->delete();
            });

            return redirect()
                ->route('equipment-types.index')
                ->with(
                    'success',
                    'Equipment type deleted successfully.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'The equipment type could not be deleted.'
            );
        }
    }
}
