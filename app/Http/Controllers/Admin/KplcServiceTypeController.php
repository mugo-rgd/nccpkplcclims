<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KplcServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;
use App\Services\ConfigurationAuditService;

class KplcServiceTypeController extends Controller
{
    public function __construct(
    private readonly ConfigurationAuditService $auditService
) {
}
    /**
     * Display laboratory service types.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $status = $request->input('status');

        $serviceTypes = KplcServiceType::query()
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where(
                                'serv_code',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'serv_name',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'serv_method',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'notes',
                                'like',
                                '%' . $search . '%'
                            );
                    });
                }
            )
            ->when(
                in_array((string) $status, ['active', 'inactive'], true),
                function ($query) use ($status) {
                    $query->where(
                        'status',
                        $status === 'active'
                    );
                }
            )
            ->orderBy('serv_code')
            ->paginate(15)
            ->withQueryString();

        $statistics = [
            'total' => KplcServiceType::query()->count(),

            'active' => KplcServiceType::query()
                ->where('status', true)
                ->count(),

            'inactive' => KplcServiceType::query()
                ->where('status', false)
                ->count(),
        ];

        return view(
            'admin.service-types.index',
            compact(
                'serviceTypes',
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
        return view('admin.service-types.create');
    }

    /**
     * Store a new service type.
     */
    public function store(Request $request): RedirectResponse
{
    $validated = $request->validate(
        [
            'serv_code' => [
                'required',
                'string',
                'max:50',
                'unique:kplc_service_types,serv_code',
            ],

            'serv_name' => [
                'required',
                'string',
                'max:255',
                'unique:kplc_service_types,serv_name',
            ],

            'serv_method' => [
                'required',
                'string',
                'max:500',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'comments' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'status' => [
                'required',
                'boolean',
            ],
        ],
        [
            'serv_code.required' =>
                'The service code is required.',

            'serv_code.unique' =>
                'This service code already exists.',

            'serv_name.required' =>
                'The service name is required.',

            'serv_name.unique' =>
                'This service name already exists.',

            'serv_method.required' =>
                'The applicable method or standard is required.',
        ]
    );

    try {
        $serviceType = DB::transaction(
            function () use ($validated) {
                return KplcServiceType::query()->create([
                    'serv_code' => strtoupper(
                        trim($validated['serv_code'])
                    ),

                    'serv_name' => trim(
                        $validated['serv_name']
                    ),

                    'serv_method' => trim(
                        $validated['serv_method']
                    ),

                    'notes' => filled(
                        $validated['notes'] ?? null
                    )
                        ? trim($validated['notes'])
                        : null,

                    'comments' => filled(
                        $validated['comments'] ?? null
                    )
                        ? trim($validated['comments'])
                        : null,

                    'status' => (bool) $validated['status'],
                ]);
            }
        );

        $this->auditService->log(
            action: 'service_type_created',
            subject: $serviceType,
            description:
                "Created laboratory service {$serviceType->serv_code}.",
            newValues: $serviceType->only([
                'serv_code',
                'serv_name',
                'serv_method',
                'notes',
                'comments',
                'status',
            ])
        );

        return redirect()
            ->route('service-types.index')
            ->with(
                'success',
                'Laboratory service created successfully.'
            );
    } catch (Throwable $exception) {
        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'The laboratory service could not be created.'
            );
    }
}

    /**
     * Display a service type.
     */
    public function show(
        KplcServiceType $serviceType
    ): View {
        return view(
            'admin.service-types.show',
            compact('serviceType')
        );
    }

    /**
     * Display the edit form.
     */
    public function edit(
        KplcServiceType $serviceType
    ): View {
        return view(
            'admin.service-types.edit',
            compact('serviceType')
        );
    }

    /**
     * Update a service type.
     */
   public function update(
    Request $request,
    KplcServiceType $serviceType
): RedirectResponse {
    $validated = $request->validate(
        [
            'serv_code' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'kplc_service_types',
                    'serv_code'
                )->ignore($serviceType->id),
            ],

            'serv_name' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'kplc_service_types',
                    'serv_name'
                )->ignore($serviceType->id),
            ],

            'serv_method' => [
                'required',
                'string',
                'max:500',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'comments' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'status' => [
                'required',
                'boolean',
            ],
        ]
    );

    try {
        $oldValues = $serviceType->only([
            'serv_code',
            'serv_name',
            'serv_method',
            'notes',
            'comments',
            'status',
        ]);

        DB::transaction(
            function () use (
                $validated,
                $serviceType
            ) {
                $serviceType->update([
                    'serv_code' => strtoupper(
                        trim($validated['serv_code'])
                    ),

                    'serv_name' => trim(
                        $validated['serv_name']
                    ),

                    'serv_method' => trim(
                        $validated['serv_method']
                    ),

                    'notes' => filled(
                        $validated['notes'] ?? null
                    )
                        ? trim($validated['notes'])
                        : null,

                    'comments' => filled(
                        $validated['comments'] ?? null
                    )
                        ? trim($validated['comments'])
                        : null,

                    'status' => (bool) $validated['status'],
                ]);
            }
        );

        $serviceType->refresh();

        $this->auditService->log(
            action: 'service_type_updated',
            subject: $serviceType,
            description:
                "Updated laboratory service {$serviceType->serv_code}.",
            oldValues: $oldValues,
            newValues: $serviceType->only([
                'serv_code',
                'serv_name',
                'serv_method',
                'notes',
                'comments',
                'status',
            ])
        );

        return redirect()
            ->route('service-types.index')
            ->with(
                'success',
                'Laboratory service updated successfully.'
            );
    } catch (Throwable $exception) {
        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'The laboratory service could not be updated.'
            );
    }
}

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(
    KplcServiceType $serviceType
): RedirectResponse {
    try {
        $oldStatus = (bool) $serviceType->status;

        $serviceType->update([
            'status' => !$oldStatus,
        ]);

        $serviceType->refresh();

        $this->auditService->log(
            action: $serviceType->status
                ? 'service_type_activated'
                : 'service_type_deactivated',

            subject: $serviceType,

            description: $serviceType->status
                ? "Activated laboratory service {$serviceType->serv_code}."
                : "Deactivated laboratory service {$serviceType->serv_code}.",

            oldValues: [
                'status' => $oldStatus,
            ],

            newValues: [
                'status' => (bool) $serviceType->status,
            ]
        );

        return back()->with(
            'success',
            $serviceType->status
                ? 'Laboratory service activated successfully.'
                : 'Laboratory service deactivated successfully.'
        );
    } catch (Throwable $exception) {
        report($exception);

        return back()->with(
            'error',
            'The service status could not be changed.'
        );
    }
}

    /**
     * Delete a service type.
     */
    public function destroy(
    KplcServiceType $serviceType
): RedirectResponse {
    try {
        $oldValues = $serviceType->only([
            'serv_code',
            'serv_name',
            'serv_method',
            'notes',
            'comments',
            'status',
        ]);

        $serviceCode = $serviceType->serv_code;

        $this->auditService->log(
            action: 'service_type_deleted',
            subject: $serviceType,
            description:
                "Deleted laboratory service {$serviceCode}.",
            oldValues: $oldValues
        );

        DB::transaction(
            function () use ($serviceType) {
                $serviceType->delete();
            }
        );

        return redirect()
            ->route('service-types.index')
            ->with(
                'success',
                'Laboratory service deleted successfully.'
            );
    } catch (Throwable $exception) {
        report($exception);

        return back()->with(
            'error',
            'The laboratory service could not be deleted. It may already be referenced by another record.'
        );
    }
}
}
