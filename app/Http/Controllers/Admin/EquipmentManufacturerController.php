<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentManufacturer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;
use App\Services\ConfigurationAuditService;

class EquipmentManufacturerController extends Controller
{
    public function __construct(
    private readonly ConfigurationAuditService $auditService
) {
}
    /**
     * Display equipment manufacturers.
     */
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $status = (string) $request->input('status', '');

        $manufacturers = EquipmentManufacturer::query()
            ->withCount('equipment')
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($subQuery) use ($search) {
                            $subQuery
                                ->where(
                                    'name',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'code',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'contact_person',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'contact_phone',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'contact_email',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'address',
                                    'like',
                                    '%' . $search . '%'
                                );
                        }
                    );
                }
            )
            ->when(
                in_array(
                    $status,
                    ['active', 'inactive'],
                    true
                ),
                function ($query) use ($status) {
                    $query->where(
                        'is_active',
                        $status === 'active'
                    );
                }
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $statistics = [
            'total' => EquipmentManufacturer::query()
                ->count(),

            'active' => EquipmentManufacturer::query()
                ->where('is_active', true)
                ->count(),

            'inactive' => EquipmentManufacturer::query()
                ->where('is_active', false)
                ->count(),

            'used' => EquipmentManufacturer::query()
                ->whereHas('equipment')
                ->count(),
        ];

        return view(
            'admin.equipment-manufacturers.index',
            compact(
                'manufacturers',
                'statistics',
                'search',
                'status'
            )
        );
    }

    /**
     * Display the create form.
     */
    public function create(): View
    {
        return view(
            'admin.equipment-manufacturers.create'
        );
    }

    /**
     * Store a new equipment manufacturer.
     */
    public function store(Request $request): RedirectResponse
{
    $validated = $request->validate(
        [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:equipment_manufacturers,name',
            ],

            'code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/',
                'unique:equipment_manufacturers,code',
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contact_phone' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contact_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]
    );

    try {
        $equipmentManufacturer = DB::transaction(
            function () use ($validated) {
                return EquipmentManufacturer::query()->create([
                    'name' => trim($validated['name']),

                    'code' => strtoupper(
                        trim($validated['code'])
                    ),

                    'website' => filled(
                        $validated['website'] ?? null
                    )
                        ? trim($validated['website'])
                        : null,

                    'contact_person' => filled(
                        $validated['contact_person'] ?? null
                    )
                        ? trim($validated['contact_person'])
                        : null,

                    'contact_phone' => filled(
                        $validated['contact_phone'] ?? null
                    )
                        ? trim($validated['contact_phone'])
                        : null,

                    'contact_email' => filled(
                        $validated['contact_email'] ?? null
                    )
                        ? strtolower(
                            trim($validated['contact_email'])
                        )
                        : null,

                    'address' => filled(
                        $validated['address'] ?? null
                    )
                        ? trim($validated['address'])
                        : null,

                    'is_active' =>
                        (bool) $validated['is_active'],
                ]);
            }
        );

        $this->auditService->log(
            action: 'equipment_manufacturer_created',
            subject: $equipmentManufacturer,
            description:
                "Created equipment manufacturer {$equipmentManufacturer->code}.",
            newValues: $equipmentManufacturer->only([
                'name',
                'code',
                'website',
                'contact_person',
                'contact_phone',
                'contact_email',
                'address',
                'is_active',
            ])
        );

        return redirect()
            ->route('equipment-manufacturers.index')
            ->with(
                'success',
                'Equipment manufacturer created successfully.'
            );
    } catch (Throwable $exception) {
        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'The equipment manufacturer could not be created.'
            );
    }
}

    /**
     * Display one equipment manufacturer.
     */
    public function show(
        EquipmentManufacturer $equipmentManufacturer
    ): View {
        $equipmentManufacturer->loadCount('equipment');

        $recentEquipment = $equipmentManufacturer
            ->equipment()
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view(
            'admin.equipment-manufacturers.show',
            compact(
                'equipmentManufacturer',
                'recentEquipment'
            )
        );
    }

    /**
     * Display the edit form.
     */
    public function edit(
        EquipmentManufacturer $equipmentManufacturer
    ): View {
        return view(
            'admin.equipment-manufacturers.edit',
            compact('equipmentManufacturer')
        );
    }

    /**
     * Update an equipment manufacturer.
     */
    public function update(
    Request $request,
    EquipmentManufacturer $equipmentManufacturer
): RedirectResponse {
    $validated = $request->validate(
        [
            'name' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'equipment_manufacturers',
                    'name'
                )->ignore($equipmentManufacturer->id),
            ],

            'code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/',

                Rule::unique(
                    'equipment_manufacturers',
                    'code'
                )->ignore($equipmentManufacturer->id),
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contact_phone' => [
                'nullable',
                'string',
                'max:255',
            ],

            'contact_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]
    );

    try {
        $oldValues = $equipmentManufacturer->only([
            'name',
            'code',
            'website',
            'contact_person',
            'contact_phone',
            'contact_email',
            'address',
            'is_active',
        ]);

        DB::transaction(
            function () use (
                $validated,
                $equipmentManufacturer
            ) {
                $equipmentManufacturer->update([
                    'name' => trim($validated['name']),

                    'code' => strtoupper(
                        trim($validated['code'])
                    ),

                    'website' => filled(
                        $validated['website'] ?? null
                    )
                        ? trim($validated['website'])
                        : null,

                    'contact_person' => filled(
                        $validated['contact_person'] ?? null
                    )
                        ? trim($validated['contact_person'])
                        : null,

                    'contact_phone' => filled(
                        $validated['contact_phone'] ?? null
                    )
                        ? trim($validated['contact_phone'])
                        : null,

                    'contact_email' => filled(
                        $validated['contact_email'] ?? null
                    )
                        ? strtolower(
                            trim($validated['contact_email'])
                        )
                        : null,

                    'address' => filled(
                        $validated['address'] ?? null
                    )
                        ? trim($validated['address'])
                        : null,

                    'is_active' =>
                        (bool) $validated['is_active'],
                ]);
            }
        );

        $equipmentManufacturer->refresh();

        $this->auditService->log(
            action: 'equipment_manufacturer_updated',
            subject: $equipmentManufacturer,
            description:
                "Updated equipment manufacturer {$equipmentManufacturer->code}.",
            oldValues: $oldValues,
            newValues: $equipmentManufacturer->only([
                'name',
                'code',
                'website',
                'contact_person',
                'contact_phone',
                'contact_email',
                'address',
                'is_active',
            ])
        );

        return redirect()
            ->route('equipment-manufacturers.index')
            ->with(
                'success',
                'Equipment manufacturer updated successfully.'
            );
    } catch (Throwable $exception) {
        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'The equipment manufacturer could not be updated.'
            );
    }
}
    /**
     * Activate or deactivate a manufacturer.
     */
    public function toggleStatus(
    EquipmentManufacturer $equipmentManufacturer
): RedirectResponse {
    try {
        $oldStatus = (bool) $equipmentManufacturer->is_active;

        $equipmentManufacturer->update([
            'is_active' => !$oldStatus,
        ]);

        $equipmentManufacturer->refresh();

        $this->auditService->log(
            action: $equipmentManufacturer->is_active
                ? 'equipment_manufacturer_activated'
                : 'equipment_manufacturer_deactivated',

            subject: $equipmentManufacturer,

            description: $equipmentManufacturer->is_active
                ? "Activated equipment manufacturer {$equipmentManufacturer->code}."
                : "Deactivated equipment manufacturer {$equipmentManufacturer->code}.",

            oldValues: [
                'is_active' => $oldStatus,
            ],

            newValues: [
                'is_active' =>
                    (bool) $equipmentManufacturer->is_active,
            ]
        );

        return back()->with(
            'success',
            $equipmentManufacturer->is_active
                ? 'Equipment manufacturer activated successfully.'
                : 'Equipment manufacturer deactivated successfully.'
        );
    } catch (Throwable $exception) {
        report($exception);

        return back()->with(
            'error',
            'The manufacturer status could not be changed.'
        );
    }
}

    /**
     * Delete an unused equipment manufacturer.
     */
    public function destroy(
    EquipmentManufacturer $equipmentManufacturer
): RedirectResponse {
    if ($equipmentManufacturer->equipment()->exists()) {
        return back()->with(
            'error',
            'This manufacturer cannot be deleted because equipment records are assigned to it. Deactivate it instead.'
        );
    }

    try {
        $oldValues = $equipmentManufacturer->only([
            'name',
            'code',
            'website',
            'contact_person',
            'contact_phone',
            'contact_email',
            'address',
            'is_active',
        ]);

        $manufacturerCode = $equipmentManufacturer->code;

        DB::transaction(
            function () use (
                $equipmentManufacturer,
                $oldValues,
                $manufacturerCode
            ) {
                $this->auditService->log(
                    action: 'equipment_manufacturer_deleted',
                    subject: $equipmentManufacturer,
                    description:
                        "Deleted equipment manufacturer {$manufacturerCode}.",
                    oldValues: $oldValues
                );

                $equipmentManufacturer->delete();
            }
        );

        return redirect()
            ->route('equipment-manufacturers.index')
            ->with(
                'success',
                'Equipment manufacturer deleted successfully.'
            );
    } catch (Throwable $exception) {
        report($exception);

        return back()->with(
            'error',
            'The equipment manufacturer could not be deleted.'
        );
    }
}
}
