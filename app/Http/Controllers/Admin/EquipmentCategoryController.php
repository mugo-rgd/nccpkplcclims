<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;
use App\Services\ConfigurationAuditService;

class EquipmentCategoryController extends Controller
{
    public function __construct(
    private readonly ConfigurationAuditService $auditService
) {
}
    /**
     * Display equipment categories.
     */
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $status = (string) $request->input('status', '');

        $categories = EquipmentCategory::query()
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
                                    'description',
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
            'total' => EquipmentCategory::query()->count(),

            'active' => EquipmentCategory::query()
                ->where('is_active', true)
                ->count(),

            'inactive' => EquipmentCategory::query()
                ->where('is_active', false)
                ->count(),

            'used' => EquipmentCategory::query()
                ->whereHas('equipment')
                ->count(),
        ];

        return view(
            'admin.equipment-categories.index',
            compact(
                'categories',
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
            'admin.equipment-categories.create'
        );
    }

    /**
     * Store a new category.
     */
    public function store(Request $request): RedirectResponse
{
    $validated = $request->validate(
        [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:equipment_categories,name',
            ],

            'code' => [
                'required',
                'string',
                'max:255',
                'unique:equipment_categories,code',
                'regex:/^[A-Za-z0-9_-]+$/',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ],
        [
            'name.required' =>
                'The category name is required.',

            'name.unique' =>
                'This equipment category already exists.',

            'code.required' =>
                'The category code is required.',

            'code.unique' =>
                'This category code is already in use.',

            'code.regex' =>
                'The category code may contain only letters, numbers, hyphens and underscores.',
        ]
    );

    try {
        $equipmentCategory = DB::transaction(
            function () use ($validated) {
                return EquipmentCategory::query()->create([
                    'name' => trim($validated['name']),

                    'code' => strtoupper(
                        trim($validated['code'])
                    ),

                    'description' => filled(
                        $validated['description'] ?? null
                    )
                        ? trim($validated['description'])
                        : null,

                    'is_active' =>
                        (bool) $validated['is_active'],
                ]);
            }
        );

        $this->auditService->log(
            action: 'equipment_category_created',
            subject: $equipmentCategory,
            description:
                "Created equipment category {$equipmentCategory->code}.",
            newValues: $equipmentCategory->only([
                'name',
                'code',
                'description',
                'is_active',
            ])
        );

        return redirect()
            ->route('equipment-categories.index')
            ->with(
                'success',
                'Equipment category created successfully.'
            );
    } catch (Throwable $exception) {
        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'The equipment category could not be created.'
            );
    }
}

    /**
     * Display one category.
     */
    public function show(
        EquipmentCategory $equipmentCategory
    ): View {
        $equipmentCategory->loadCount('equipment');

        $recentEquipment = $equipmentCategory
            ->equipment()
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view(
            'admin.equipment-categories.show',
            compact(
                'equipmentCategory',
                'recentEquipment'
            )
        );
    }

    /**
     * Display the edit form.
     */
    public function edit(
        EquipmentCategory $equipmentCategory
    ): View {
        return view(
            'admin.equipment-categories.edit',
            compact('equipmentCategory')
        );
    }

    /**
     * Update a category.
     */
    public function update(
    Request $request,
    EquipmentCategory $equipmentCategory
): RedirectResponse {
    $validated = $request->validate(
        [
            'name' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'equipment_categories',
                    'name'
                )->ignore($equipmentCategory->id),
            ],

            'code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/',

                Rule::unique(
                    'equipment_categories',
                    'code'
                )->ignore($equipmentCategory->id),
            ],

            'description' => [
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
        $oldValues = $equipmentCategory->only([
            'name',
            'code',
            'description',
            'is_active',
        ]);

        DB::transaction(
            function () use (
                $validated,
                $equipmentCategory
            ) {
                $equipmentCategory->update([
                    'name' => trim(
                        $validated['name']
                    ),

                    'code' => strtoupper(
                        trim($validated['code'])
                    ),

                    'description' => filled(
                        $validated['description'] ?? null
                    )
                        ? trim($validated['description'])
                        : null,

                    'is_active' =>
                        (bool) $validated['is_active'],
                ]);
            }
        );

        $equipmentCategory->refresh();

        $this->auditService->log(
            action: 'equipment_category_updated',
            subject: $equipmentCategory,
            description:
                "Updated equipment category {$equipmentCategory->code}.",
            oldValues: $oldValues,
            newValues: $equipmentCategory->only([
                'name',
                'code',
                'description',
                'is_active',
            ])
        );

        return redirect()
            ->route('equipment-categories.index')
            ->with(
                'success',
                'Equipment category updated successfully.'
            );
    } catch (Throwable $exception) {
        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'The equipment category could not be updated.'
            );
    }
}

    /**
     * Activate or deactivate a category.
     */
    public function toggleStatus(
    EquipmentCategory $equipmentCategory
): RedirectResponse {
    try {
        $oldStatus = (bool) $equipmentCategory->is_active;

        $equipmentCategory->update([
            'is_active' => !$oldStatus,
        ]);

        $equipmentCategory->refresh();

        $this->auditService->log(
            action: $equipmentCategory->is_active
                ? 'equipment_category_activated'
                : 'equipment_category_deactivated',

            subject: $equipmentCategory,

            description: $equipmentCategory->is_active
                ? "Activated equipment category {$equipmentCategory->code}."
                : "Deactivated equipment category {$equipmentCategory->code}.",

            oldValues: [
                'is_active' => $oldStatus,
            ],

            newValues: [
                'is_active' =>
                    (bool) $equipmentCategory->is_active,
            ]
        );

        return back()->with(
            'success',
            $equipmentCategory->is_active
                ? 'Equipment category activated successfully.'
                : 'Equipment category deactivated successfully.'
        );
    } catch (Throwable $exception) {
        report($exception);

        return back()->with(
            'error',
            'The category status could not be changed.'
        );
    }
}

    /**
     * Delete an unused category.
     */
    public function destroy(
    EquipmentCategory $equipmentCategory
): RedirectResponse {
    if ($equipmentCategory->equipment()->exists()) {
        return back()->with(
            'error',
            'This category cannot be deleted because equipment records are assigned to it. Deactivate it instead.'
        );
    }

    try {
        $oldValues = $equipmentCategory->only([
            'name',
            'code',
            'description',
            'is_active',
        ]);

        $categoryCode = $equipmentCategory->code;

        DB::transaction(
            function () use (
                $equipmentCategory,
                $oldValues,
                $categoryCode
            ) {
                /*
                 * Log before deletion while the subject still exists.
                 */
                $this->auditService->log(
                    action: 'equipment_category_deleted',
                    subject: $equipmentCategory,
                    description:
                        "Deleted equipment category {$categoryCode}.",
                    oldValues: $oldValues
                );

                $equipmentCategory->delete();
            }
        );

        return redirect()
            ->route('equipment-categories.index')
            ->with(
                'success',
                'Equipment category deleted successfully.'
            );
    } catch (Throwable $exception) {
        report($exception);

        return back()->with(
            'error',
            'The equipment category could not be deleted.'
        );
    }
}
}
