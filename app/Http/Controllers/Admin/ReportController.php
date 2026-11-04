<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use App\Models\Customer;
use App\Models\Certificate;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use App\Models\EquipmentManufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // Get statistics for reports
        $stats = [
            'total_calibrations' => Calibration::count(),
            'pending_calibrations' => Calibration::where('status', 'pending')->count(),
            'completed_calibrations' => Calibration::where('status', 'completed')->count(),
            'total_customers' => Customer::count(),
            'total_certificates' => Certificate::count(),
            'total_equipment' => Equipment::count(),
            'calibrations_by_status' => Calibration::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'calibrations_by_month' => Calibration::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get(),
        ];

        return view('admin.reports.index', compact('stats'));
    }

    public function calibrations(Request $request)
    {
        // Use the correct relationship name - 'performedBy' instead of 'technician'
        $query = Calibration::with(['customer', 'performedBy']);

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $calibrations = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reports.calibrations', compact('calibrations'));
    }

    public function customers()
    {
        $customers = Customer::withCount('calibrations')
            ->orderBy('calibrations_count', 'desc')
            ->paginate(20);

        return view('admin.reports.customers', compact('customers'));
    }

    public function certificates()
    {
        $certificates = Certificate::with(['calibration', 'calibration.customer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.reports.certificates', compact('certificates'));
    }

    public function equipment(Request $request)
    {
        $query = Equipment::with(['category', 'type', 'manufacturer']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%")
                  ->orWhere('equipment_number', 'LIKE', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type_id', $request->type);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Condition filter
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // Get equipment with pagination
        $equipment = $query->orderBy('name', 'asc')->paginate(20);

        // Get statistics
        $stats = [
            'active_count' => Equipment::where('status', 'active')->count(),
            'in_use_count' => Equipment::where('status', 'in_use')->count(),
            'under_repair_count' => Equipment::where('status', 'under_repair')->count(),
            'retired_count' => Equipment::where('status', 'retired')->count(),
            'calibration_due_count' => Equipment::calibrationDue()->count(),
            'overdue_count' => Equipment::where('next_calibration_date', '<', now())->count(),
        ];

        // Get categories for filter dropdown - Use full namespace
        $categories = EquipmentCategory::active()->orderBy('name')->get();

        // Get types for filter dropdown
        $types = EquipmentType::active()->orderBy('name')->get();

        return view('admin.reports.equipment', compact('equipment', 'stats', 'categories', 'types'));
    }

    public function export($type)
    {
        // This would export data to CSV/Excel
        // For now, just return a message
        return redirect()->back()->with('info', 'Export functionality coming soon!');
    }

    /**
     * Export equipment report to CSV.
     */
    public function exportEquipment(Request $request)
    {
        $query = Equipment::with(['category', 'type', 'manufacturer']);

        // Apply same filters as the view
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        $equipment = $query->orderBy('name', 'asc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="equipment_report_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($equipment) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers
            fputcsv($handle, [
                'ID',
                'Equipment Number',
                'Equipment Name',
                'Serial Number',
                'Model',
                'Manufacturer',
                'Category',
                'Type',
                'Status',
                'Condition',
                'Location',
                'Last Calibration Date',
                'Next Calibration Date',
                'Calibration Interval (Days)',
                'Created At',
                'Updated At'
            ]);

            foreach ($equipment as $item) {
                fputcsv($handle, [
                    $item->id,
                    $item->equipment_number ?? 'N/A',
                    $item->name ?? 'N/A',
                    $item->serial_number ?? 'N/A',
                    $item->model ?? 'N/A',
                    $item->manufacturer->name ?? $item->manufacturer ?? 'N/A',
                    $item->category->name ?? 'N/A',
                    $item->type->name ?? 'N/A',
                    $item->status ?? 'N/A',
                    $item->condition ?? 'N/A',
                    $item->location ?? 'N/A',
                    $item->last_calibration_date ?? 'Never',
                    $item->next_calibration_date ?? 'N/A',
                    $item->calibration_interval_days ?? 'N/A',
                    $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : 'N/A',
                    $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }


    /**
     * Display the comprehensive finance report.
     */
    public function finance(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        /*
         * Default to the current financial year:
         * 1 July to 30 June.
         */
        $today = now();

        if ($today->month >= 7) {
            $defaultStart = Carbon::create($today->year, 7, 1)->startOfDay();
            $defaultEnd = Carbon::create($today->year + 1, 6, 30)->endOfDay();
        } else {
            $defaultStart = Carbon::create($today->year - 1, 7, 1)->startOfDay();
            $defaultEnd = Carbon::create($today->year, 6, 30)->endOfDay();
        }

        $startDate = Carbon::parse(
            $validated['start_date'] ?? $defaultStart->toDateString()
        )->startOfDay();

        $endDate = Carbon::parse(
            $validated['end_date'] ?? $defaultEnd->toDateString()
        )->endOfDay();

        $financeStats = $this->getFinanceStats($startDate, $endDate);
        $revenueByStatus = $this->getRevenueByStatus($startDate, $endDate);
        $monthlyRevenue = $this->getMonthlyRevenue($startDate, $endDate);
        $revenueByEquipment = $this->getRevenueByEquipment($startDate, $endDate);
        $paymentMethods = $this->getPaymentMethods($startDate, $endDate);
        $agingAnalysis = $this->getAgingAnalysis($startDate, $endDate);
        $revenueByCustomer = $this->getRevenueByCustomer($startDate, $endDate);
        $revenueByTechnician = $this->getRevenueByTechnician($startDate, $endDate);
        $costBreakdown = $this->getCostBreakdown($startDate, $endDate);

        $recentCalibrations = Calibration::query()
            ->with([
                'customer',
                'performedBy',
                'equipmentType',
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where(function ($query) {
                $query
                    ->where('amount_due', '>', 0)
                    ->orWhere('amount_paid', '>', 0)
                    ->orWhereNotNull('payment_status');
            })
            ->latest('created_at')
            ->limit(50)
            ->get();

        return view('admin.reports.finance', [
            'financeStats' => $financeStats,
            'revenueByStatus' => $revenueByStatus,
            'monthlyRevenue' => $monthlyRevenue,
            'revenueByEquipment' => $revenueByEquipment,
            'paymentMethods' => $paymentMethods,
            'agingAnalysis' => $agingAnalysis,
            'revenueByCustomer' => $revenueByCustomer,
            'revenueByTechnician' => $revenueByTechnician,
            'costBreakdown' => $costBreakdown,
            'recentCalibrations' => $recentCalibrations,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
        ]);
    }

    /**
     * Get headline financial statistics.
     */
    private function getFinanceStats(Carbon $startDate, Carbon $endDate): array
    {
        $baseQuery = Calibration::query()
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalRevenue = (float) (clone $baseQuery)->sum('amount_due');
        $collected = (float) (clone $baseQuery)->sum('amount_paid');

        $perDiemCost = (float) (clone $baseQuery)->sum('per_diem_cost');
        $transportCost = (float) (clone $baseQuery)->sum('transport_cost');
        $otherCosts = (float) (clone $baseQuery)->sum('other_costs');
        $totalCosts = $perDiemCost + $transportCost + $otherCosts;

        $outstanding = max($totalRevenue - $collected, 0);
        $netProfit = $collected - $totalCosts;

        $totalCalibrations = (clone $baseQuery)->count();

        $paidCalibrations = (clone $baseQuery)
            ->where('payment_status', 'paid')
            ->count();

        $partiallyPaidCalibrations = (clone $baseQuery)
            ->where('amount_paid', '>', 0)
            ->whereColumn('amount_paid', '<', 'amount_due')
            ->count();

        $unpaidCalibrations = (clone $baseQuery)
            ->where(function ($query) {
                $query
                    ->whereNull('amount_paid')
                    ->orWhere('amount_paid', '<=', 0);
            })
            ->where('amount_due', '>', 0)
            ->count();

        $overdueQuery = (clone $baseQuery)
            ->where('created_at', '<', now()->subDays(30))
            ->where('amount_due', '>', 0)
            ->whereRaw('COALESCE(amount_paid, 0) < COALESCE(amount_due, 0)');

        $overdueAmount = (float) (clone $overdueQuery)
            ->selectRaw(
                'COALESCE(SUM(COALESCE(amount_due, 0) - COALESCE(amount_paid, 0)), 0) as outstanding'
            )
            ->value('outstanding');

        $overdueCount = (clone $overdueQuery)->count();

        return [
            'total_revenue_generated' => $totalRevenue,
            'revenue_collected' => $collected,
            'outstanding' => $outstanding,
            'collection_rate' => $totalRevenue > 0
                ? ($collected / $totalRevenue) * 100
                : 0,
            'net_profit' => $netProfit,
            'profit_margin' => $collected > 0
                ? ($netProfit / $collected) * 100
                : 0,
            'avg_revenue_per_calibration' => $totalCalibrations > 0
                ? $totalRevenue / $totalCalibrations
                : 0,
            'avg_collected_per_calibration' => $totalCalibrations > 0
                ? $collected / $totalCalibrations
                : 0,
            'overdue_amount' => $overdueAmount,
            'overdue_count' => $overdueCount,
            'total_calibrations' => $totalCalibrations,
            'paid_calibrations' => $paidCalibrations,
            'partially_paid_calibrations' => $partiallyPaidCalibrations,
            'unpaid_calibrations' => $unpaidCalibrations,
            'total_costs' => $totalCosts,
            'per_diem_cost' => $perDiemCost,
            'transport_cost' => $transportCost,
            'other_costs' => $otherCosts,
        ];
    }

    /**
     * Get revenue breakdown by payment status.
     */
    private function getRevenueByStatus(
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $rows = Calibration::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw(
                'COALESCE(NULLIF(payment_status, ""), "unclassified") as payment_status'
            )
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('COALESCE(SUM(amount_due), 0) as total_due')
            ->selectRaw('COALESCE(SUM(amount_paid), 0) as total_paid')
            ->groupBy('payment_status')
            ->orderByDesc('total_due')
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $key = $row->payment_status ?: 'unclassified';

            $result[$key] = [
                'amount' => (float) $row->total_due,
                'collected' => (float) $row->total_paid,
                'outstanding' => max(
                    (float) $row->total_due - (float) $row->total_paid,
                    0
                ),
                'count' => (int) $row->total_count,
            ];
        }

        return $result;
    }

    /**
     * Get monthly revenue, collections, costs and profit.
     */
    private function getMonthlyRevenue(
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $databaseRows = Calibration::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month_key')
            ->selectRaw('COALESCE(SUM(amount_due), 0) as total_generated')
            ->selectRaw('COALESCE(SUM(amount_paid), 0) as total_collected')
            ->selectRaw(
                'COALESCE(SUM(
                    COALESCE(per_diem_cost, 0)
                    + COALESCE(transport_cost, 0)
                    + COALESCE(other_costs, 0)
                ), 0) as total_costs'
            )
            ->selectRaw('COUNT(*) as total_count')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()
            ->keyBy('month_key');

        $result = [];
        $cursor = $startDate->copy()->startOfMonth();
        $lastMonth = $endDate->copy()->startOfMonth();

        while ($cursor->lte($lastMonth)) {
            $monthKey = $cursor->format('Y-m');
            $row = $databaseRows->get($monthKey);

            $generated = (float) ($row->total_generated ?? 0);
            $collected = (float) ($row->total_collected ?? 0);
            $costs = (float) ($row->total_costs ?? 0);

            $result[] = [
                'month' => $cursor->format('M Y'),
                'generated' => $generated,
                'collected' => $collected,
                'costs' => $costs,
                'profit' => $collected - $costs,
                'count' => (int) ($row->total_count ?? 0),
            ];

            $cursor->addMonth();
        }

        return $result;
    }

    /**
     * Get revenue and profitability by equipment type.
     */
    private function getRevenueByEquipment(
        Carbon $startDate,
        Carbon $endDate
    ) {
        return Calibration::query()
            ->leftJoin(
                'kplc_equipment_types',
                'calibrations.equipment_type_id',
                '=',
                'kplc_equipment_types.id'
            )
            ->whereBetween(
                'calibrations.created_at',
                [$startDate, $endDate]
            )
            ->selectRaw(
                'COALESCE(
                    kplc_equipment_types.name,
                    NULLIF(calibrations.equipment_type, ""),
                    "Unknown"
                ) as equipment_type'
            )
            ->selectRaw('COUNT(calibrations.id) as total_count')
            ->selectRaw(
                'COALESCE(SUM(calibrations.amount_due), 0) as total_revenue'
            )
            ->selectRaw(
                'COALESCE(SUM(calibrations.amount_paid), 0) as total_collected'
            )
            ->selectRaw(
                'COALESCE(SUM(
                    COALESCE(calibrations.per_diem_cost, 0)
                    + COALESCE(calibrations.transport_cost, 0)
                    + COALESCE(calibrations.other_costs, 0)
                ), 0) as total_costs'
            )
            ->groupBy(
                'calibrations.equipment_type_id',
                'kplc_equipment_types.name',
                'calibrations.equipment_type'
            )
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($item) {
                $revenue = (float) $item->total_revenue;
                $collected = (float) $item->total_collected;
                $costs = (float) $item->total_costs;

                return [
                    'equipment_type' => $item->equipment_type ?: 'Unknown',
                    'count' => (int) $item->total_count,
                    'revenue' => $revenue,
                    'collected' => $collected,
                    'outstanding' => max($revenue - $collected, 0),
                    'costs' => $costs,
                    'profit' => $collected - $costs,
                ];
            });
    }

    /**
     * Get payment method breakdown.
     */
    private function getPaymentMethods(
        Carbon $startDate,
        Carbon $endDate
    ) {
        $rows = Calibration::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('payment_method')
            ->where('payment_method', '!=', '')
            ->where('amount_paid', '>', 0)
            ->selectRaw('payment_method')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('COALESCE(SUM(amount_paid), 0) as total_amount')
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();

        $totalCollected = (float) $rows->sum('total_amount');

        return $rows->map(function ($item) use ($totalCollected) {
            $amount = (float) $item->total_amount;

            return [
                'method' => $item->payment_method ?: 'unknown',
                'transactions' => (int) $item->transaction_count,
                'amount' => $amount,
                'percentage' => $totalCollected > 0
                    ? ($amount / $totalCollected) * 100
                    : 0,
            ];
        });
    }

    /**
     * Get receivables aging for the selected period.
     */
    private function getAgingAnalysis(
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $categories = [
            ['label' => '0 - 30 Days', 'minimum' => 0, 'maximum' => 30],
            ['label' => '31 - 60 Days', 'minimum' => 31, 'maximum' => 60],
            ['label' => '61 - 90 Days', 'minimum' => 61, 'maximum' => 90],
            ['label' => 'Over 90 Days', 'minimum' => 91, 'maximum' => null],
        ];

        $baseQuery = Calibration::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('amount_due', '>', 0)
            ->whereRaw('COALESCE(amount_paid, 0) < COALESCE(amount_due, 0)');

        $totalOutstanding = (float) (clone $baseQuery)
            ->selectRaw(
                'COALESCE(SUM(COALESCE(amount_due, 0) - COALESCE(amount_paid, 0)), 0) as outstanding'
            )
            ->value('outstanding');

        $result = [];

        foreach ($categories as $category) {
            $query = clone $baseQuery;

            $latestDate = now()->subDays($category['minimum']);

            if ($category['maximum'] === null) {
                $query->where('created_at', '<', $latestDate);
            } else {
                $oldestDate = now()->subDays($category['maximum']);

                $query->whereBetween('created_at', [
                    $oldestDate,
                    $latestDate,
                ]);
            }

            $amount = (float) (clone $query)
                ->selectRaw(
                    'COALESCE(SUM(COALESCE(amount_due, 0) - COALESCE(amount_paid, 0)), 0) as outstanding'
                )
                ->value('outstanding');

            $result[] = [
                'category' => $category['label'],
                'amount' => $amount,
                'count' => $query->count(),
                'days' => $category['minimum'],
                'percentage' => $totalOutstanding > 0
                    ? ($amount / $totalOutstanding) * 100
                    : 0,
            ];
        }

        return $result;
    }

    /**
     * Get revenue ranking by customer.
     */
  private function getRevenueByCustomer(
    Carbon $startDate,
    Carbon $endDate
) {
    /*
     * Use the Calibration customer relationship so Laravel uses
     * the correct foreign key and owner key defined in the model.
     */
    $calibrations = Calibration::query()
        ->with('customer')
        ->whereBetween(
            'created_at',
            [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ]
        )
        ->whereNotNull('customer_id')
        ->get([
            'id',
            'customer_id',
            'amount_due',
            'amount_paid',
        ]);

    return $calibrations
        ->groupBy('customer_id')
        ->map(function ($customerCalibrations, $customerId) {
            /*
             * Take the customer relationship from the first calibration
             * in this customer group.
             */
            $firstCalibration = $customerCalibrations->first();

            $customer = $firstCalibration?->customer;

            $customerName = trim(
                (string) (
                    $customer?->company_name
                    ?? $customer?->name
                    ?? ''
                )
            );

            if ($customerName === '') {
                $customerName = 'Customer #' . $customerId;
            }

            $revenue = (float) $customerCalibrations->sum(
                fn ($calibration) =>
                    (float) ($calibration->amount_due ?? 0)
            );

            $collected = (float) $customerCalibrations->sum(
                fn ($calibration) =>
                    (float) ($calibration->amount_paid ?? 0)
            );

            return [
                'customer_id' => (int) $customerId,

                'customer_name' => $customerName,

                'count' => $customerCalibrations->count(),

                'revenue' => $revenue,

                'collected' => $collected,

                'outstanding' => max(
                    $revenue - $collected,
                    0
                ),
            ];
        })
        ->sortByDesc('revenue')
        ->take(15)
        ->values();
}

    /**
     * Get revenue and workload by technician.
     */
    private function getRevenueByTechnician(
        Carbon $startDate,
        Carbon $endDate
    ) {
        return Calibration::query()
            ->leftJoin(
                'users',
                'calibrations.performed_by',
                '=',
                'users.id'
            )
            ->whereBetween('calibrations.created_at', [$startDate, $endDate])
            ->selectRaw(
                'COALESCE(NULLIF(users.name, ""), "Unassigned") as technician_name'
            )
            ->selectRaw('COUNT(calibrations.id) as total_count')
            ->selectRaw(
                'SUM(CASE WHEN calibrations.status IN ("completed", "certified") THEN 1 ELSE 0 END) as completed_count'
            )
            ->selectRaw(
                'COALESCE(SUM(calibrations.amount_due), 0) as total_revenue'
            )
            ->selectRaw(
                'COALESCE(SUM(calibrations.amount_paid), 0) as total_collected'
            )
            ->groupBy(
                'calibrations.performed_by',
                'users.name'
            )
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($item) {
                return [
                    'technician_name' => $item->technician_name ?: 'Unassigned',
                    'count' => (int) $item->total_count,
                    'completed' => (int) $item->completed_count,
                    'revenue' => (float) $item->total_revenue,
                    'collected' => (float) $item->total_collected,
                ];
            });
    }

    /**
     * Get operating cost breakdown.
     */
    private function getCostBreakdown(
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $query = Calibration::query()
            ->whereBetween('created_at', [$startDate, $endDate]);

        $perDiem = (float) (clone $query)->sum('per_diem_cost');
        $transport = (float) (clone $query)->sum('transport_cost');
        $other = (float) (clone $query)->sum('other_costs');
        $total = $perDiem + $transport + $other;

        return [
            [
                'category' => 'Per Diem',
                'amount' => $perDiem,
                'percentage' => $total > 0 ? ($perDiem / $total) * 100 : 0,
            ],
            [
                'category' => 'Transport',
                'amount' => $transport,
                'percentage' => $total > 0 ? ($transport / $total) * 100 : 0,
            ],
            [
                'category' => 'Other Costs',
                'amount' => $other,
                'percentage' => $total > 0 ? ($other / $total) * 100 : 0,
            ],
        ];
    }

    /**
     * Export finance report to Excel/CSV.
     */
    public function exportFinance(Request $request)
    {
        $startDate = $request->input('start_date', '2025-07-01');
        $endDate = $request->input('end_date', '2026-06-30');

        $calibrations = Calibration::with(['customer', 'equipmentType'])
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where(function($q) {
                $q->where('amount_due', '>', 0)
                  ->orWhere('amount_paid', '>', 0)
                  ->orWhereNotNull('payment_status');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="finance_report_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($calibrations) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Add headers
            fputcsv($handle, [
                'ID',
                'Customer',
                'Equipment',
                'Equipment Type',
                'Status',
                'Payment Status',
                'Amount Due (KES)',
                'Amount Paid (KES)',
                'Outstanding (KES)',
                'Payment Method',
                'Payment Reference',
                'Per Diem Cost (KES)',
                'Transport Cost (KES)',
                'Other Costs (KES)',
                'Number of Staff',
                'Travel Days',
                'Created Date',
                'Completed Date'
            ]);

            // Add rows
            foreach ($calibrations as $cal) {
                $outstanding = ($cal->amount_due ?? 0) - ($cal->amount_paid ?? 0);
                fputcsv($handle, [
                    $cal->id,
                    $cal->customer?->company_name ?? $cal->customer?->name ?? 'N/A',
                    $cal->equipment_name ?? 'N/A',
                    $cal->equipmentType?->name ?? $cal->equipment_type ?? 'N/A',
                    $cal->status ?? 'N/A',
                    $cal->payment_status ?? 'pending',
                    number_format($cal->amount_due ?? 0, 2),
                    number_format($cal->amount_paid ?? 0, 2),
                    number_format($outstanding, 2),
                    $cal->payment_method ?? 'N/A',
                    $cal->payment_reference ?? 'N/A',
                    number_format($cal->per_diem_cost ?? 0, 2),
                    number_format($cal->transport_cost ?? 0, 2),
                    number_format($cal->other_costs ?? 0, 2),
                    $cal->number_of_staff ?? 1,
                    $cal->travel_days ?? 1,
                    $cal->created_at ? $cal->created_at->format('Y-m-d H:i:s') : 'N/A',
                    $cal->completed_at ? $cal->completed_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
