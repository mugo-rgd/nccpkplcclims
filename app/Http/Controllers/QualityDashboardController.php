<?php

namespace App\Http\Controllers;

use App\Models\QualityObjective;
use App\Models\QuarterlyAchievement;
use App\Models\CorrectiveAction;
use App\Models\QualityFyrPeriod;
use App\Services\QualityPeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QualityDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_quality_dashboard');
    }

    public function index()
    {
        $currentFyr = QualityFyrPeriod::getCurrentFYR();
        $currentQuarter = QualityPeriodService::getCurrentQuarter();
        $fyrPeriods = QualityFyrPeriod::orderBy('start_date', 'desc')->get();

        // Get dashboard data
        $summary = $this->getDashboardSummary($currentFyr);
        $sectionPerformance = $this->getSectionPerformance($currentFyr);
        $trendData = $this->getTrendData();
        $atRiskObjectives = $this->getAtRiskObjectives($currentFyr);
        $carStatus = $this->getCARStatus();

        return view('quality.dashboard', compact(
            'summary',
            'sectionPerformance',
            'trendData',
            'atRiskObjectives',
            'carStatus',
            'currentFyr',
            'currentQuarter',
            'fyrPeriods'
        ));
    }

    private function getDashboardSummary($fyr)
    {
        if (!$fyr) {
            return [
                'total' => 0,
                'tracked' => 0,
                'on_track' => 0,
                'at_risk' => 0,
                'not_met' => 0,
                'pending' => 0,
                'avg_achievement' => 0,
                'completion_rate' => 0
            ];
        }

        $total = QualityObjective::where('target_fyr', $fyr->fyr)->count();
        $achievements = QuarterlyAchievement::where('fyr_period_id', $fyr->id)->get();
        $tracked = $achievements->count();

        $onTrack = $achievements->filter(function($a) {
            return $a->status == 'On Track';
        })->count();

        $atRisk = $achievements->filter(function($a) {
            return $a->status == 'At Risk';
        })->count();

        $notMet = $achievements->filter(function($a) {
            return $a->status == 'Not Met';
        })->count();

        $pending = $total - $tracked;
        $avgAchievement = $achievements->avg('achievement_percentage') ?? 0;

        return [
            'total' => $total,
            'tracked' => $tracked,
            'on_track' => $onTrack,
            'at_risk' => $atRisk,
            'not_met' => $notMet,
            'pending' => $pending,
            'avg_achievement' => round($avgAchievement, 2),
            'completion_rate' => $total > 0 ? round(($tracked / $total) * 100, 2) : 0
        ];
    }

    private function getSectionPerformance($fyr)
    {
        if (!$fyr) {
            return [];
        }

        $sections = ['MCL', 'Instruments', 'Large Power'];
        $performance = [];

        foreach ($sections as $section) {
            $objectives = QualityObjective::where('section', $section)
                ->where('target_fyr', $fyr->fyr)
                ->pluck('id');

            $achievements = QuarterlyAchievement::whereIn('objective_id', $objectives)
                ->where('fyr_period_id', $fyr->id)
                ->get();

            $performance[$section] = [
                'total' => $objectives->count(),
                'tracked' => $achievements->count(),
                'on_track' => $achievements->filter(fn($a) => $a->status == 'On Track')->count(),
                'at_risk' => $achievements->filter(fn($a) => $a->status == 'At Risk')->count(),
                'not_met' => $achievements->filter(fn($a) => $a->status == 'Not Met')->count(),
                'avg_achievement' => round($achievements->avg('achievement_percentage') ?? 0, 2)
            ];
        }

        return $performance;
    }

    private function getTrendData()
    {
        $periods = QualityFyrPeriod::orderBy('start_date')->get();
        $trend = [];

        foreach ($periods as $period) {
            $achievements = QuarterlyAchievement::where('fyr_period_id', $period->id)->get();

            $trend[$period->fyr] = [
                'avg_achievement' => round($achievements->avg('achievement_percentage') ?? 0, 2),
                'count' => $achievements->count(),
                'target' => 95
            ];
        }

        return $trend;
    }

    private function getAtRiskObjectives($fyr)
{
    if (!$fyr) {
        return collect();
    }

    // Use a different approach to avoid the relationship issue
    $objectives = QualityObjective::where('target_fyr', $fyr->fyr)
        ->with(['owner'])
        ->get();

    // Manually load achievements for each objective
    $objectives->each(function($objective) use ($fyr) {
        $objective->setRelation('achievements',
            QuarterlyAchievement::where('objective_id', $objective->id)
                ->where('fyr_period_id', $fyr->id)
                ->get()
        );
    });

    return $objectives->filter(function($objective) {
        return $objective->achievements->isNotEmpty() &&
               in_array($objective->status, ['At Risk', 'Not Met']);
    })->map(function($objective) {
        $achievement = $objective->achievements->first();
        return [
            'id' => $objective->id,
            'objective_id' => $objective->objective_id,
            'description' => $objective->description,
            'section' => $objective->section,
            'target' => $objective->target_value,
            'actual' => $achievement ? $achievement->actual_achievement : null,
            'percentage' => $achievement ? $achievement->achievement_percentage : null,
            'status' => $achievement ? $achievement->status : 'Pending',
            'owner' => $objective->owner->name ?? 'Unassigned'
        ];
    })->values();
}

    private function getCARStatus()
    {
        $total = CorrectiveAction::count();
        $open = CorrectiveAction::whereIn('status', ['Open', 'In Progress'])->count();
        $closed = CorrectiveAction::where('status', 'Closed')->count();
        $overdue = CorrectiveAction::overdue()->count();

        return [
            'total' => $total,
            'open' => $open,
            'closed' => $closed,
            'overdue' => $overdue,
            'closure_rate' => $total > 0 ? round(($closed / $total) * 100, 2) : 0
        ];
    }

    // API endpoints
    public function getQuarterData(Request $request)
    {
        $fyr = $request->fyr ? QualityFyrPeriod::where('fyr', $request->fyr)->first() : QualityFyrPeriod::getCurrentFYR();

        $data = [
            'summary' => $this->getDashboardSummary($fyr),
            'sections' => $this->getSectionPerformance($fyr),
            'at_risk' => $this->getAtRiskObjectives($fyr)
        ];

        return response()->json($data);
    }
}
