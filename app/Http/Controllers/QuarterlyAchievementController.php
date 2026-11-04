<?php

namespace App\Http\Controllers;

use App\Models\QualityObjective;
use App\Models\QuarterlyAchievement;
use App\Models\QualityFyrPeriod;
use App\Services\QualityPeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuarterlyAchievementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_achievements')->only(['index', 'show']);
        $this->middleware('permission:create_achievements')->only(['create', 'store']);
        $this->middleware('permission:edit_achievements')->only(['edit', 'update']);
        $this->middleware('permission:delete_achievements')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = QuarterlyAchievement::with(['objective', 'collectedBy', 'fyrPeriod']);

        // Filter by objective
        if ($request->has('objective_id') && $request->objective_id) {
            $query->where('objective_id', $request->objective_id);
        }

        // Filter by FYR
        if ($request->has('fyr_period_id') && $request->fyr_period_id) {
            $query->where('fyr_period_id', $request->fyr_period_id);
        }

        // Filter by quarter
        if ($request->has('quarter') && $request->quarter) {
            $query->where('quarter', $request->quarter);
        }

        $achievements = $query->orderBy('created_at', 'desc')->paginate(15);

        $objectives = QualityObjective::pluck('description', 'id');
        $fyrPeriods = QualityFyrPeriod::orderBy('start_date', 'desc')->get();
        $currentFyr = QualityPeriodService::getCurrentFYR();
        $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];

        return view('quality.achievements.index', compact(
            'achievements',
            'objectives',
            'fyrPeriods',
            'currentFyr',
            'quarters'
        ));
    }

    public function create(Request $request)
    {
        $objectiveId = $request->objective_id;
        $objective = $objectiveId ? QualityObjective::findOrFail($objectiveId) : null;

        $objectives = QualityObjective::where('status', 'active')->pluck('description', 'id');
        $fyrPeriods = QualityFyrPeriod::orderBy('start_date', 'desc')->get();
        $currentFyr = QualityPeriodService::getCurrentFYR();
        $quarters = ['Q1' => 'Q1 (Sept)', 'Q2' => 'Q2 (Dec)', 'Q3' => 'Q3 (Mar)', 'Q4' => 'Q4 (June)'];

        return view('quality.achievements.create', compact(
            'objective',
            'objectives',
            'fyrPeriods',
            'currentFyr',
            'quarters'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'objective_id' => 'required|exists:quality_objectives,id',
            'fyr_period_id' => 'required|exists:quality_fyr_periods,id',
            'quarter' => 'required|in:Q1,Q2,Q3,Q4',
            'quarter_label' => 'required|string',
            'actual_achievement' => 'required|numeric|min:0',
            'data_collection_date' => 'required|date',
            'remarks' => 'nullable|string',
            'evidence_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check for duplicate
        $exists = QuarterlyAchievement::where('objective_id', $request->objective_id)
            ->where('fyr_period_id', $request->fyr_period_id)
            ->where('quarter', $request->quarter)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Achievement for this quarter already exists.')
                ->withInput();
        }

        try {
            $achievement = QuarterlyAchievement::create([
                'objective_id' => $request->objective_id,
                'fyr_period_id' => $request->fyr_period_id,
                'quarter' => $request->quarter,
                'quarter_label' => $request->quarter_label,
                'actual_achievement' => $request->actual_achievement,
                'data_collection_date' => $request->data_collection_date,
                'collected_by_id' => Auth::id(),
                'remarks' => $request->remarks,
                'evidence_url' => $request->evidence_url
            ]);

            return redirect()->route('quality.achievements.index')
                ->with('success', 'Achievement recorded successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to save achievement: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $achievement = QuarterlyAchievement::with('objective')->findOrFail($id);
        $objectives = QualityObjective::where('status', 'active')->pluck('description', 'id');
        $fyrPeriods = QualityFyrPeriod::orderBy('start_date', 'desc')->get();
        $quarters = ['Q1' => 'Q1 (Sept)', 'Q2' => 'Q2 (Dec)', 'Q3' => 'Q3 (Mar)', 'Q4' => 'Q4 (June)'];

        return view('quality.achievements.edit', compact(
            'achievement',
            'objectives',
            'fyrPeriods',
            'quarters'
        ));
    }

    public function update(Request $request, $id)
    {
        $achievement = QuarterlyAchievement::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'objective_id' => 'required|exists:quality_objectives,id',
            'fyr_period_id' => 'required|exists:quality_fyr_periods,id',
            'quarter' => 'required|in:Q1,Q2,Q3,Q4',
            'quarter_label' => 'required|string',
            'actual_achievement' => 'required|numeric|min:0',
            'data_collection_date' => 'required|date',
            'remarks' => 'nullable|string',
            'evidence_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check for duplicate (excluding current)
        $exists = QuarterlyAchievement::where('objective_id', $request->objective_id)
            ->where('fyr_period_id', $request->fyr_period_id)
            ->where('quarter', $request->quarter)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Achievement for this quarter already exists.')
                ->withInput();
        }

        try {
            $achievement->update([
                'objective_id' => $request->objective_id,
                'fyr_period_id' => $request->fyr_period_id,
                'quarter' => $request->quarter,
                'quarter_label' => $request->quarter_label,
                'actual_achievement' => $request->actual_achievement,
                'data_collection_date' => $request->data_collection_date,
                'remarks' => $request->remarks,
                'evidence_url' => $request->evidence_url
            ]);

            return redirect()->route('quality.achievements.index')
                ->with('success', 'Achievement updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update achievement: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $achievement = QuarterlyAchievement::findOrFail($id);
            $achievement->delete();

            return redirect()->route('quality.achievements.index')
                ->with('success', 'Achievement deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete achievement: ' . $e->getMessage());
        }
    }

    public function bulkEntry(Request $request)
    {
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'fyr_period_id' => 'required|exists:quality_fyr_periods,id',
                'quarter' => 'required|in:Q1,Q2,Q3,Q4',
                'quarter_label' => 'required|string',
                'data' => 'required|array',
                'data.*.objective_id' => 'required|exists:quality_objectives,id',
                'data.*.actual_achievement' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $count = 0;
            $errors = [];

            foreach ($request->data as $item) {
                try {
                    $exists = QuarterlyAchievement::where('objective_id', $item['objective_id'])
                        ->where('fyr_period_id', $request->fyr_period_id)
                        ->where('quarter', $request->quarter)
                        ->exists();

                    if (!$exists) {
                        QuarterlyAchievement::create([
                            'objective_id' => $item['objective_id'],
                            'fyr_period_id' => $request->fyr_period_id,
                            'quarter' => $request->quarter,
                            'quarter_label' => $request->quarter_label,
                            'actual_achievement' => $item['actual_achievement'],
                            'data_collection_date' => now(),
                            'collected_by_id' => Auth::id(),
                            'remarks' => $item['remarks'] ?? null
                        ]);
                        $count++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to save objective ID: " . $item['objective_id'];
                }
            }

            $message = $count . ' achievements saved successfully.';
            if (!empty($errors)) {
                $message .= ' Errors: ' . implode(', ', $errors);
            }

            return redirect()->route('quality.achievements.index')
                ->with('success', $message);
        }

        // GET request - show bulk entry form
        $objectives = QualityObjective::where('status', 'active')
            ->get()
            ->groupBy('section');

        $fyrPeriods = QualityFyrPeriod::orderBy('start_date', 'desc')->get();
        $currentFyr = QualityPeriodService::getCurrentFYR();
        $quarters = ['Q1' => 'Q1 (Sept)', 'Q2' => 'Q2 (Dec)', 'Q3' => 'Q3 (Mar)', 'Q4' => 'Q4 (June)'];

        return view('quality.achievements.bulk', compact(
            'objectives',
            'fyrPeriods',
            'currentFyr',
            'quarters'
        ));
    }
}
