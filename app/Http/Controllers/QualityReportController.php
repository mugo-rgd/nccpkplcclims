<?php

namespace App\Http\Controllers;

use App\Models\QualityObjective;
use App\Models\QualityFyrPeriod;
use App\Services\QualityPeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class QualityReportController extends Controller
{
    public function __construct()
    {
        // FIX: Use correct permission names from your database
        $this->middleware('permission:view_quality_reports|view_reports')->only(['index']);
        $this->middleware('permission:generate_quality_reports|generate_reports|export_quality_reports')->only(['generate', 'export', 'quickPDF']);
    }

    public function index()
    {
        $fyrPeriods = QualityFyrPeriod::orderBy('start_date', 'desc')->get();
        $currentFyr = QualityPeriodService::getCurrentFYR();
        $sections = ['All', 'MCL', 'Instruments', 'Large Power'];
        $statuses = ['All', 'On Track', 'At Risk', 'Not Met', 'Pending'];

        return view('quality.reports.index', compact(
            'fyrPeriods',
            'currentFyr',
            'sections',
            'statuses'
        ));
    }

    public function generate(Request $request)
    {
        $fyr = $request->input('fyr', QualityPeriodService::getCurrentFYR()->fyr);
        $section = $request->input('section', 'All');
        $status = $request->input('status', 'All');
        $format = $request->input('format', 'html');

        $query = QualityObjective::with(['owner', 'fyrPeriod']);

        if ($section != 'All') {
            $query->where('section', $section);
        }

        if ($fyr) {
            $query->where('target_fyr', $fyr);
        }

        $objectives = $query->get();

        // Filter by status if needed
        if ($status != 'All') {
            $objectives = $objectives->filter(function($obj) use ($status) {
                return $obj->status == $status;
            });
        }

        $reportData = [
            'fyr' => $fyr,
            'section' => $section,
            'status' => $status,
            'generated_at' => now(),
            'objectives' => $objectives,
            'summary' => $this->getReportSummary($objectives),
            'section_analysis' => $this->getSectionAnalysis($objectives),
            'generated_by' => auth()->user()->name ?? 'System'
        ];

        if ($format == 'pdf' || $request->has('export')) {
            return $this->exportPDF($reportData);
        }

        return view('quality.reports.show', $reportData);
    }

    /**
     * Quick PDF report - GET method for quick links
     */
    public function quickPDF(Request $request)
    {
        $fyr = QualityPeriodService::getCurrentFYR()->fyr ?? null;
        $section = 'All';
        $status = 'All';

        $query = QualityObjective::with(['owner', 'fyrPeriod']);

        if ($section != 'All') {
            $query->where('section', $section);
        }

        if ($fyr) {
            $query->where('target_fyr', $fyr);
        }

        $objectives = $query->get();

        if ($status != 'All') {
            $objectives = $objectives->filter(function($obj) use ($status) {
                return $obj->status == $status;
            });
        }

        $reportData = [
            'fyr' => $fyr,
            'section' => $section,
            'status' => $status,
            'generated_at' => now(),
            'objectives' => $objectives,
            'summary' => $this->getReportSummary($objectives),
            'section_analysis' => $this->getSectionAnalysis($objectives),
            'generated_by' => auth()->user()->name ?? 'System'
        ];

        return $this->exportPDF($reportData);
    }

    public function export(Request $request)
    {
        return $this->generate($request);
    }

    private function getReportSummary($objectives)
    {
        $total = $objectives->count();
        $tracked = $objectives->filter(function($obj) {
            return $obj->achievements->isNotEmpty();
        })->count();

        $onTrack = $objectives->filter(function($obj) {
            return $obj->achievements->isNotEmpty() && $obj->status == 'On Track';
        })->count();

        $atRisk = $objectives->filter(function($obj) {
            return $obj->achievements->isNotEmpty() && $obj->status == 'At Risk';
        })->count();

        $notMet = $objectives->filter(function($obj) {
            return $obj->achievements->isNotEmpty() && $obj->status == 'Not Met';
        })->count();

        $pending = $total - $tracked;

        $avgAchievement = $objectives->filter(function($obj) {
            return $obj->achievements->isNotEmpty();
        })->avg(function($obj) {
            return $obj->achievements->first()->achievement_percentage ?? 0;
        });

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

    private function getSectionAnalysis($objectives)
    {
        $sections = ['MCL', 'Instruments', 'Large Power'];
        $analysis = [];

        foreach ($sections as $section) {
            $sectionObjectives = $objectives->filter(function($obj) use ($section) {
                return $obj->section == $section;
            });

            $tracked = $sectionObjectives->filter(function($obj) {
                return $obj->achievements->isNotEmpty();
            });

            $analysis[$section] = [
                'total' => $sectionObjectives->count(),
                'tracked' => $tracked->count(),
                'avg_achievement' => round($tracked->avg(function($obj) {
                    return $obj->achievements->first()->achievement_percentage ?? 0;
                }), 2),
                'on_track' => $tracked->filter(function($obj) {
                    return $obj->status == 'On Track';
                })->count(),
                'at_risk' => $tracked->filter(function($obj) {
                    return $obj->status == 'At Risk';
                })->count(),
                'not_met' => $tracked->filter(function($obj) {
                    return $obj->status == 'Not Met';
                })->count()
            ];
        }

        return $analysis;
    }

    private function exportPDF($reportData)
    {
        $pdf = Pdf::loadView('quality.reports.pdf', $reportData);
        return $pdf->download('Quality_Report_' . $reportData['fyr'] . '.pdf');
    }
}
