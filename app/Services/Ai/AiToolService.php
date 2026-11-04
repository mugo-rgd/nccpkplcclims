<?php

namespace App\Services\Ai;

use App\Models\Calibration;
use App\Models\Certificate;
use App\Models\Equipment;
use App\Models\KplcEquipmentType;
use App\Models\KplcServiceType;
use App\Models\UserGuide;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Services\Ai\AiDataRedactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AiToolService
{
    public function __construct(
    private readonly AiDataRedactionService $redactor
) {
}
    public function execute(
    string $toolName,
    array $arguments
): array {
    $result = match ($toolName) {
        'lookup_calibration' =>
            $this->lookupCalibration($arguments),

        'lookup_certificate' =>
            $this->lookupCertificate($arguments),

        'search_equipment' =>
            $this->searchEquipment($arguments),

        'search_user_guide' =>
            $this->searchUserGuide($arguments),

        'get_laboratory_services' =>
            $this->getLaboratoryServices(),

        'get_equipment_pricing' =>
            $this->getEquipmentPricing(),

        'get_finance_summary' =>
            $this->getFinanceSummary($arguments),

        'get_pending_work_summary' =>
            $this->getPendingWorkSummary(),

        'get_overdue_calibrations' =>
            $this->getOverdueCalibrations($arguments),

        default => throw ValidationException::withMessages([
            'tool' => 'Unsupported AI tool requested.',
        ]),
    };

    return $this->redactor->redact($result);
}

private function getOverdueCalibrations(
    array $arguments
): array {
    abort_unless(
        Auth::user()?->can('view_equipment'),
        403
    );

    $limit = min(
        max((int) ($arguments['limit'] ?? 10), 1),
        50
    );

    return Equipment::query()
        ->with([
            'categoryRecord',
            'manufacturerRecord',
            'pricingType',
        ])
        ->whereNotNull('next_calibration_date')
        ->whereDate(
            'next_calibration_date',
            '<',
            today()
        )
        ->whereNotIn('status', [
            'retired',
            'disposed',
        ])
        ->orderBy('next_calibration_date')
        ->limit($limit)
        ->get()
        ->map(fn (Equipment $equipment) => [
            'id' => $equipment->id,
            'name' => $equipment->name,
            'serial_number' =>
                $equipment->serial_number,

            'next_calibration_date' =>
                $equipment->next_calibration_date
                    ?->format('Y-m-d'),

            'days_overdue' =>
                $equipment->next_calibration_date
                    ?->diffInDays(today()),

            'category' =>
                $equipment->categoryRecord?->name,

            'manufacturer' =>
                $equipment->manufacturerRecord?->name,

            'status' => $equipment->status,
        ])
        ->values()
        ->all();
}

private function getFinanceSummary(array $arguments): array
{
    abort_unless(
        Auth::user()?->can('view_finance_reports')
        || Auth::user()?->can('view_finance')
        || Auth::user()?->hasAnyRole([
            'super_admin',
            'lab_manager',
            'finance',
        ]),
        403,
        'You are not authorized to view finance summaries.'
    );

    $startDate = !empty($arguments['start_date'])
        ? Carbon::parse($arguments['start_date'])->startOfDay()
        : now()->startOfMonth();

    $endDate = !empty($arguments['end_date'])
        ? Carbon::parse($arguments['end_date'])->endOfDay()
        : now()->endOfDay();

    if ($startDate->greaterThan($endDate)) {
        throw ValidationException::withMessages([
            'start_date' =>
                'The start date must be before the end date.',
        ]);
    }

    /*
     * Limit unrestricted requests to a maximum period of two years.
     */
    if ($startDate->diffInDays($endDate) > 731) {
        throw ValidationException::withMessages([
            'date_range' =>
                'Finance summaries are limited to two years.',
        ]);
    }

    $summary = Calibration::query()
        ->whereBetween(
            'created_at',
            [$startDate, $endDate]
        )
        ->selectRaw('COUNT(*) as total_jobs')
        ->selectRaw(
            'COALESCE(SUM(amount_due), 0) as total_revenue'
        )
        ->selectRaw(
            'COALESCE(SUM(amount_paid), 0) as total_collected'
        )
        ->first();

    $revenue = (float) ($summary?->total_revenue ?? 0);
    $collected = (float) ($summary?->total_collected ?? 0);

    $byPaymentStatus = Calibration::query()
        ->whereBetween(
            'created_at',
            [$startDate, $endDate]
        )
        ->selectRaw(
            'COALESCE(payment_status, "not_recorded") as payment_status'
        )
        ->selectRaw('COUNT(*) as jobs')
        ->selectRaw(
            'COALESCE(SUM(amount_due), 0) as amount_due'
        )
        ->selectRaw(
            'COALESCE(SUM(amount_paid), 0) as amount_paid'
        )
        ->groupBy('payment_status')
        ->orderByDesc('amount_due')
        ->get()
        ->map(fn ($item) => [
            'payment_status' => $item->payment_status,
            'jobs' => (int) $item->jobs,
            'amount_due' => (float) $item->amount_due,
            'amount_paid' => (float) $item->amount_paid,
        ])
        ->values()
        ->all();

    return [
        'period' => [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ],

        'total_jobs' => (int) ($summary?->total_jobs ?? 0),

        'total_revenue' => $revenue,

        'total_collected' => $collected,

        'total_outstanding' => max(
            $revenue - $collected,
            0
        ),

        'collection_rate_percent' => $revenue > 0
            ? round(($collected / $revenue) * 100, 2)
            : 0,

        'by_payment_status' => $byPaymentStatus,
    ];
}

private function getPendingWorkSummary(): array
{
    $user = Auth::user();

    abort_unless(
        $user?->can('view_calibrations'),
        403
    );

    $calibrationQuery = Calibration::query();

    /*
     * Technicians should only see work assigned to them.
     * Adjust performed_by/allocated_to to match your schema.
     */
    if (
        $user->hasRole('lab_technician')
        && !$user->hasAnyRole([
            'super_admin',
            'lab_manager',
            'supervisor',
        ])
    ) {
        $calibrationQuery->where(function ($query) use ($user) {
            $query
                ->where('performed_by', $user->id)
                ->orWhere('allocated_to', $user->id);
        });
    }

    $calibrationStatuses = $calibrationQuery
        ->whereNotIn('status', [
            'completed',
            'cancelled',
            'rejected',
        ])
        ->selectRaw(
            'COALESCE(status, "not_recorded") as status'
        )
        ->selectRaw('COUNT(*) as total')
        ->groupBy('status')
        ->orderByDesc('total')
        ->pluck('total', 'status')
        ->map(fn ($value) => (int) $value)
        ->all();

    $certificateStatuses = [];

    if ($user->can('view_certificates')) {
        $certificateStatuses = Certificate::query()
            ->whereNotIn('status', [
                'approved',
                'issued',
                'sent',
                'cancelled',
            ])
            ->selectRaw(
                'COALESCE(status, "not_recorded") as status'
            )
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->pluck('total', 'status')
            ->map(fn ($value) => (int) $value)
            ->all();
    }

    $equipmentDue = 0;

    if ($user->can('view_equipment')) {
        $equipmentDue = Equipment::query()
            ->whereNotNull('next_calibration_date')
            ->whereDate(
                'next_calibration_date',
                '<=',
                now()->addDays(30)->toDateString()
            )
            ->whereNotIn('status', [
                'retired',
                'disposed',
            ])
            ->count();
    }

    return [
        'scope' => $user->hasRole('lab_technician')
            ? 'assigned_to_current_user'
            : 'laboratory',

        'calibrations_by_status' =>
            $calibrationStatuses,

        'certificates_by_status' =>
            $certificateStatuses,

        'equipment_due_within_30_days' =>
            $equipmentDue,

        'generated_at' => now()->toIso8601String(),
    ];
}

    private function lookupCalibration(array $arguments): array
    {
        abort_unless(
            Auth::user()?->can('view_calibrations'),
            403
        );

        $calibrationId = (int) (
            $arguments['calibration_id'] ?? 0
        );

        $calibration = Calibration::query()
            ->with([
                'customer',
                'equipment',
                'performedBy',
            ])
            ->findOrFail($calibrationId);

        return [
            'id' => $calibration->id,
            'status' => $calibration->status,
            'customer' =>
                $calibration->customer?->company_name
                ?? $calibration->customer?->name
                ?? 'Unknown',

            'equipment' =>
                $calibration->equipment?->name
                ?? $calibration->equipment_name
                ?? 'Unknown',

            'serial_number' =>
                $calibration->serial_number
                ?? $calibration->equipment?->serial_number,

            'scheduled_date' =>
                $calibration->scheduled_date?->format('Y-m-d'),

            'technician' =>
                $calibration->performedBy?->name,

            'amount_due' =>
                (float) ($calibration->amount_due ?? 0),

            'amount_paid' =>
                (float) ($calibration->amount_paid ?? 0),
        ];
    }

    private function lookupCertificate(array $arguments): array
    {
        abort_unless(
            Auth::user()?->can('view_certificates'),
            403
        );

        $certificateId = (int) (
            $arguments['certificate_id'] ?? 0
        );

        $certificate = Certificate::query()
            ->with([
                'calibration.customer',
                'generatedBy',
                'checkedBy',
                'approvedBy',
            ])
            ->findOrFail($certificateId);

        return [
            'id' => $certificate->id,
            'certificate_number' =>
                $certificate->certificate_number,

            'status' => $certificate->status,

            'customer' =>
                $certificate->calibration?->customer?->company_name
                ?? 'Unknown',

            'generated_by' =>
                $certificate->generatedBy?->name,

            'checked_by' =>
                $certificate->checkedBy?->name,

            'approved_by' =>
                $certificate->approvedBy?->name,

            'checked_at' =>
                $certificate->checked_at?->format(
                    'Y-m-d H:i:s'
                ),

            'approved_at' =>
                $certificate->approved_at?->format(
                    'Y-m-d H:i:s'
                ),
        ];
    }

    private function searchEquipment(array $arguments): array
{
    abort_unless(
        auth()->user()?->can('view_equipment'),
        403
    );

    $search = trim(
        (string) ($arguments['search'] ?? '')
    );

    if ($search === '') {
        return [
            'found' => false,
            'message' =>
                'No equipment search term was provided.',
            'records' => [],
        ];
    }

    $equipment = Equipment::query()
        ->with([
            'categoryRecord',
            'manufacturerRecord',
            'pricingType',
        ])
        ->where(function ($query) use ($search) {
            $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere(
                    'serial_number',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'model',
                    'like',
                    "%{$search}%"
                );
        })
        ->limit(10)
        ->get()
        ->map(fn (Equipment $item) => [
            'id' => $item->id,
            'name' => $item->name,
            'serial_number' => $item->serial_number,
            'model' => $item->model,
            'status' => $item->status,
            'condition' => $item->condition,
            'category' =>
                $item->categoryRecord?->name,
            'manufacturer' =>
                $item->manufacturerRecord?->name,
            'equipment_type' =>
                $item->pricingType?->name,
        ])
        ->values()
        ->all();

    return [
        'found' => count($equipment) > 0,

        'search_term' => $search,

        'message' => count($equipment) > 0
            ? count($equipment)
                . ' equipment record(s) found.'
            : 'No equipment records matched the supplied name, model or serial number.',

        'records' => $equipment,
    ];
}

    private function searchUserGuide(array $arguments): array
{
    $search = trim(
        (string) ($arguments['search'] ?? '')
    );

    if ($search === '') {
        return [
            'found' => false,
            'message' =>
                'No user-guide search topic was provided.',
            'articles' => [],
        ];
    }

    $articles = UserGuide::query()
        ->where('is_published', true)
        ->where(function ($query) use ($search) {
            $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere(
                    'content',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'category',
                    'like',
                    "%{$search}%"
                );
        })
        ->orderBy('order')
        ->limit(8)
        ->get()
        ->map(fn ($guide) => [
            'title' => $guide->title,
            'slug' => $guide->slug,
            'category' => $guide->category,

            'excerpt' => str(
                strip_tags($guide->content)
            )
                ->squish()
                ->limit(1500)
                ->toString(),
        ])
        ->values()
        ->all();

    return [
        'found' => count($articles) > 0,

        'search_term' => $search,

        'message' => count($articles) > 0
            ? count($articles)
                . ' relevant user-guide article(s) found.'
            : 'No published user-guide article matched the requested topic.',

        'articles' => $articles,
    ];
}

    private function getLaboratoryServices(): array
    {
        abort_unless(
            Auth::user()?->can('view_service_types'),
            403
        );

        return KplcServiceType::query()
            ->active()
            ->orderBy('serv_name')
            ->get([
                'serv_code',
                'serv_name',
                'serv_method',
                'notes',
            ])
            ->toArray();
    }

    private function getEquipmentPricing(): array
    {
        abort_unless(
            Auth::user()?->can('view_equipment_types'),
            403
        );

        return KplcEquipmentType::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'rate',
            ])
            ->toArray();
    }
}
