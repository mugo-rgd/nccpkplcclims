<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use App\Models\TestResult;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QualityController extends Controller
{
    /**
     * Display quality dashboard
     */
    public function dashboard()
    {
        $stats = [
            'pending_quality_checks' => Calibration::where('status', 'testing')
                ->where('quality_checked', false)
                ->count(),
            'pending_approvals' => Calibration::where('status', 'completed')
                ->where('quality_approved', false)
                ->count(),
            'total_quality_checks' => Calibration::where('quality_checked', true)->count(),
            'passed_quality' => Calibration::where('quality_approved', true)->count(),
            'failed_quality' => Calibration::where('quality_rejected', true)->count(),
        ];

        $pendingChecks = Calibration::where('status', 'testing')
            ->where('quality_checked', false)
            ->with(['customer', 'performedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.quality.dashboard', compact('stats', 'pendingChecks'));
    }

    /**
     * Display quality check form for a calibration
     */
    public function check(Calibration $calibration)
    {
        // Load relationships
        $calibration->load(['customer', 'performedBy', 'testResults', 'equipment']);

        // Get test results with uncertainty calculations
        $testResults = $calibration->testResults()
            ->with('uncertaintyCalculation')
            ->orderBy('test_order')
            ->get();

        return view('admin.quality.check', compact('calibration', 'testResults'));
    }

    /**
     * Process quality check
     */
    public function processCheck(Request $request, Calibration $calibration)
    {
        $request->validate([
            'quality_decision' => 'required|in:pass,fail,needs_review',
            'quality_notes' => 'nullable|string|max:1000',
            'checklist_items' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            // Update calibration with quality check results
            $calibration->quality_checked = true;
            $calibration->quality_checked_at = now();
            $calibration->quality_checked_by = auth()->id();
            $calibration->quality_notes = $request->quality_notes;
            $calibration->quality_checklist = $request->checklist_items;

            if ($request->quality_decision === 'pass') {
                $calibration->quality_approved = true;
                $calibration->quality_rejected = false;
                $calibration->status = 'completed';
                $message = 'Quality check passed. Calibration approved.';
            } elseif ($request->quality_decision === 'fail') {
                $calibration->quality_approved = false;
                $calibration->quality_rejected = true;
                $calibration->status = 'rejected';
                $message = 'Quality check failed. Calibration rejected.';
            } else { // needs_review
                $calibration->quality_approved = false;
                $calibration->quality_rejected = false;
                $calibration->status = 'testing';
                $message = 'Quality check needs review. Calibration returned for rework.';
            }

            $calibration->save();

            // Log quality action
            Log::info('Quality check performed', [
                'calibration_id' => $calibration->id,
                'decision' => $request->quality_decision,
                'checked_by' => auth()->id(),
                'notes' => $request->quality_notes,
            ]);

            DB::commit();

            return redirect()->route('admin.quality.dashboard')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quality check failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to process quality check: ' . $e->getMessage());
        }
    }

    /**
     * Display quality reports
     */
    public function reports(Request $request)
    {
        $query = Calibration::where('quality_checked', true);

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('quality_checked_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('quality_checked_at', '<=', $request->to_date);
        }

        // Filter by decision
        if ($request->filled('decision')) {
            if ($request->decision === 'passed') {
                $query->where('quality_approved', true);
            } elseif ($request->decision === 'failed') {
                $query->where('quality_rejected', true);
            }
        }

        $reports = $query->with(['customer', 'qualityCheckedBy'])
            ->orderBy('quality_checked_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_checked' => Calibration::where('quality_checked', true)->count(),
            'passed' => Calibration::where('quality_approved', true)->count(),
            'failed' => Calibration::where('quality_rejected', true)->count(),
            'pass_rate' => Calibration::where('quality_checked', true)->count() > 0
                ? round((Calibration::where('quality_approved', true)->count() /
                         Calibration::where('quality_checked', true)->count()) * 100, 2)
                : 0,
        ];

        return view('admin.quality.reports', compact('reports', 'stats'));
    }

    /**
     * Export quality report
     */
    public function exportReport(Request $request)
    {
        // Export logic for quality reports
        // This could generate CSV, Excel, or PDF
        return redirect()->back()->with('info', 'Export functionality coming soon!');
    }

    /**
     * Get quality statistics for a calibration
     */
    public function getStatistics(Calibration $calibration)
    {
        $testResults = $calibration->testResults()->get();

        $stats = [
            'total_tests' => $testResults->count(),
            'passed_tests' => $testResults->where('is_within_tolerance', true)->count(),
            'failed_tests' => $testResults->where('is_within_tolerance', false)->count(),
            'average_uncertainty' => $testResults->where('uncertainty', '!=', null)->avg('uncertainty'),
            'test_results' => $testResults,
        ];

        return response()->json($stats);
    }
}
