<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Calibration;
use App\Models\Certificate;
use App\Models\Customer;
use App\Models\Equipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Calibration statistics
        |--------------------------------------------------------------------------
        |
        | One aggregate query instead of executing one query for every status.
        |
        */

        $calibrationStats = Calibration::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'quoted' THEN 1 ELSE 0 END) as quoted")
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid")
            ->selectRaw("SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned")
            ->selectRaw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress")
            ->selectRaw("SUM(CASE WHEN status = 'testing' THEN 1 ELSE 0 END) as testing")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN status = 'certified' THEN 1 ELSE 0 END) as certified")
            ->first();

        $totalCalibrations = (int) ($calibrationStats->total ?? 0);
        $completedCalibrations = (int) ($calibrationStats->completed ?? 0);
        $pendingCalibrations = (int) ($calibrationStats->pending ?? 0);

        $statusCounts = [
            'pending' => $pendingCalibrations,
            'quoted' => (int) ($calibrationStats->quoted ?? 0),
            'paid' => (int) ($calibrationStats->paid ?? 0),
            'assigned' => (int) ($calibrationStats->assigned ?? 0),
            'in_progress' => (int) ($calibrationStats->in_progress ?? 0),
            'testing' => (int) ($calibrationStats->testing ?? 0),
            'completed' => $completedCalibrations,
            'certified' => (int) ($calibrationStats->certified ?? 0),
        ];

        $completedPercentage = $totalCalibrations > 0
            ? round(($completedCalibrations / $totalCalibrations) * 100)
            : 0;

        $pendingPercentage = $totalCalibrations > 0
            ? round(($pendingCalibrations / $totalCalibrations) * 100)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Equipment statistics
        |--------------------------------------------------------------------------
        |
        | Again, use one aggregate query rather than five individual queries.
        |
        */

        $equipmentStats = Equipment::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN status = 'in_use' THEN 1 ELSE 0 END) as in_use")
            ->selectRaw("SUM(CASE WHEN status = 'under_repair' THEN 1 ELSE 0 END) as under_repair")
            ->selectRaw("SUM(CASE WHEN status = 'calibration_due' THEN 1 ELSE 0 END) as calibration_due")
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Current month
        |--------------------------------------------------------------------------
        |
        | whereBetween is preferable to whereMonth because it also keeps the
        | year correct and is more index-friendly.
        |
        */

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $monthlyStats = [
            'customers' => Customer::query()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count(),

            'calibrations' => Calibration::query()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count(),

            'certificates' => Certificate::query()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Dashboard statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total_customers' => Customer::count(),
            'total_calibrations' => $totalCalibrations,
            'pending_calibrations' => $pendingCalibrations,
            'total_certificates' => Certificate::count(),

            'new_customers_this_month' => $monthlyStats['customers'],
            'new_calibrations_this_month' => $monthlyStats['calibrations'],
            'certificates_this_month' => $monthlyStats['certificates'],

            /*
             * Keep these values for now if your Blade expects them.
             * Eventually these should be calculated from actual historical data.
             */
            'customer_growth_percentage' => 75,
            'calibration_growth_percentage' => 60,
            'certificate_growth_percentage' => 80,

            'completed_percentage' => $completedPercentage,
            'pending_percentage' => $pendingPercentage,

            'status_counts' => $statusCounts,

            'total_equipment' => (int) ($equipmentStats->total ?? 0),
            'equipment_active' => (int) ($equipmentStats->active ?? 0),
            'equipment_in_use' => (int) ($equipmentStats->in_use ?? 0),
            'equipment_under_repair' => (int) ($equipmentStats->under_repair ?? 0),
            'equipment_calibration_due' => (int) ($equipmentStats->calibration_due ?? 0),
        ];

        /*
        |--------------------------------------------------------------------------
        | Recent activities
        |--------------------------------------------------------------------------
        */

        $recentActivities = ActivityLog::query()
            ->with([
                'user:id,name',
            ])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                $colors = [
                    'user' => 'primary',
                    'customer' => 'success',
                    'calibration' => 'warning',
                    'certificate' => 'info',
                    'equipment' => 'secondary',
                    'auth' => 'danger',
                ];

                $color = $colors[$log->module] ?? 'primary';

                return [
                    'user' => $log->user?->name ?? 'System',
                    'action' => ucfirst($log->action)
                        . ' - '
                        . ucfirst($log->module),
                    'time' => $log->created_at,
                    'icon' => $this->getActivityIcon($log->module),
                    'color' => $color,
                    'badge' => $log->module,
                    'badge_color' => $color,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Featured equipment
        |--------------------------------------------------------------------------
        */

        $featuredEquipment = Equipment::query()
            ->select([
                'id',
                'name',
                'serial_number',
                'model',
                'status',
                'updated_at',
            ])
            ->orderByRaw("
                CASE
                    WHEN status = 'active' THEN 1
                    WHEN status = 'in_use' THEN 2
                    ELSE 3
                END
            ")
            ->latest('updated_at')
            ->limit(4)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Activity fallback
        |--------------------------------------------------------------------------
        */

        if ($recentActivities->isEmpty()) {
            $recentActivities = collect([
                [
                    'user' => 'System',
                    'action' => 'System initialized successfully',
                    'time' => now(),
                    'icon' => 'check-circle',
                    'color' => 'success',
                    'badge' => 'System',
                    'badge_color' => 'success',
                ],
                [
                    'user' => $user->name,
                    'action' => 'Logged into the admin dashboard',
                    'time' => now(),
                    'icon' => 'sign-in-alt',
                    'color' => 'primary',
                    'badge' => 'Auth',
                    'badge_color' => 'primary',
                ],
            ]);
        }

        return view('dashboard.index', compact(
            'user',
            'stats',
            'recentActivities',
            'featuredEquipment'
        ));
    }

    private function getActivityIcon(string $module): string
    {
        $icons = [
            'user' => 'user',
            'customer' => 'building',
            'calibration' => 'microscope',
            'certificate' => 'file-pdf',
            'equipment' => 'tools',
            'auth' => 'sign-in-alt',
            'report' => 'chart-bar',
            'system' => 'cogs',
        ];

        return $icons[$module] ?? 'bell';
    }
}
