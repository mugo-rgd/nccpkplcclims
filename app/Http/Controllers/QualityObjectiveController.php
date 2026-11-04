<?php

namespace App\Http\Controllers;

use App\Models\QualityObjective;
use App\Models\User;
use App\Models\QualityFyrPeriod;
use App\Services\QualityPeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class QualityObjectiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_quality_objectives')->only(['index', 'show']);
        $this->middleware('permission:create_quality_objectives')->only(['create', 'store']);
        $this->middleware('permission:edit_quality_objectives')->only(['edit', 'update']);
        $this->middleware('permission:delete_quality_objectives')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = QualityObjective::with(['owner', 'fyrPeriod']);

        // Filter by section
        if ($request->has('section') && $request->section) {
            $query->where('section', $request->section);
        }

        // Filter by FYR
        if ($request->has('fyr') && $request->fyr) {
            $query->where('target_fyr', $request->fyr);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('objective_id', 'like', '%' . $request->search . '%');
            });
        }

        $objectives = $query->orderBy('created_at', 'desc')->paginate(15);

        $currentFyr = QualityPeriodService::getCurrentFYR();
        $sections = ['MCL', 'Instruments', 'Large Power'];
        $fyrPeriods = QualityFyrPeriod::orderBy('start_date', 'desc')->get();
        $statuses = ['active', 'inactive'];

        return view('quality.objectives.index', compact(
            'objectives',
            'currentFyr',
            'sections',
            'statuses',
            'fyrPeriods'
        ));
    }

    public function create()
    {
        $sections = [
            'MCL' => 'MCL Quality Objectives',
            'Instruments' => 'Instruments and Standards',
            'Large Power' => 'Large Power and Meter Testing'
        ];

        $units = ['Days', 'Hours', 'NO.', '%', 'Number'];
        $owners = User::where('is_active', 1)->pluck('name', 'id');
        $fyrPeriods = QualityFyrPeriod::orderBy('start_date', 'desc')->get();

        $isoClauses = [
            '7.7' => 'Ensuring validity of results',
            '8.2' => 'Management system documentation',
            '8.3' => 'Control of management system documents',
            '5.2' => 'Personnel competence',
            '6.5' => 'Metrological traceability',
            '6.4' => 'Equipment management',
            '7.10' => 'Nonconforming work'
        ];

        return view('quality.objectives.create', compact(
            'sections',
            'units',
            'owners',
            'fyrPeriods',
            'isoClauses'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'objective_id' => 'required|unique:quality_objectives|max:20',
            'section' => 'required|in:MCL,Instruments,Large Power',
            'description' => 'required|string|max:500',
            'unit_of_measure' => 'required|in:Days,Hours,NO.,%,Number',
            'baseline_value' => 'nullable|numeric',
            'baseline_fyr' => 'nullable|exists:quality_fyr_periods,fyr',
            'target_value' => 'required|numeric|min:0',
            'target_fyr' => 'required|exists:quality_fyr_periods,fyr',
            'owner_id' => 'required|exists:users,id',
            'iso17025_clause' => 'nullable|string',
            'iso17025_requirement' => 'nullable|string',
            'remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $objective = QualityObjective::create([
                'objective_id' => $request->objective_id,
                'section' => $request->section,
                'description' => $request->description,
                'unit_of_measure' => $request->unit_of_measure,
                'baseline_value' => $request->baseline_value,
                'baseline_fyr' => $request->baseline_fyr,
                'target_value' => $request->target_value,
                'target_fyr' => $request->target_fyr,
                'historical_targets' => [$request->target_fyr => $request->target_value],
                'owner_id' => $request->owner_id,
                'iso17025_clause' => $request->iso17025_clause,
                'iso17025_requirement' => $request->iso17025_requirement,
                'remarks' => $request->remarks,
                'status' => 'active'
            ]);

            return redirect()->route('quality.objectives.index')
                ->with('success', 'Quality Objective created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create objective: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $objective = QualityObjective::with([
            'owner',
            'fyrPeriod',
            'achievements' => function($q) {
                $q->orderBy('created_at', 'desc');
            },
            'correctiveActions' => function($q) {
                $q->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        $currentFyr = QualityPeriodService::getCurrentFYR();
        $fyrPeriods = QualityFyrPeriod::orderBy('start_date', 'desc')->get();

        return view('quality.objectives.show', compact(
            'objective',
            'currentFyr',
            'fyrPeriods'
        ));
    }

    public function edit($id)
    {
        $objective = QualityObjective::findOrFail($id);

        $sections = [
            'MCL' => 'MCL Quality Objectives',
            'Instruments' => 'Instruments and Standards',
            'Large Power' => 'Large Power and Meter Testing'
        ];

        $units = ['Days', 'Hours', 'NO.', '%', 'Number'];
        $owners = User::where('is_active', 1)->pluck('name', 'id');
        $fyrPeriods = QualityFyrPeriod::orderBy('start_date', 'desc')->get();

        return view('quality.objectives.edit', compact(
            'objective',
            'sections',
            'units',
            'owners',
            'fyrPeriods'
        ));
    }

    public function update(Request $request, $id)
    {
        $objective = QualityObjective::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:500',
            'unit_of_measure' => 'required|in:Days,Hours,NO.,%,Number',
            'baseline_value' => 'nullable|numeric',
            'baseline_fyr' => 'nullable|exists:quality_fyr_periods,fyr',
            'target_value' => 'required|numeric|min:0',
            'target_fyr' => 'required|exists:quality_fyr_periods,fyr',
            'owner_id' => 'required|exists:users,id',
            'remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $objective->update([
                'description' => $request->description,
                'unit_of_measure' => $request->unit_of_measure,
                'baseline_value' => $request->baseline_value,
                'baseline_fyr' => $request->baseline_fyr,
                'target_value' => $request->target_value,
                'target_fyr' => $request->target_fyr,
                'owner_id' => $request->owner_id,
                'remarks' => $request->remarks
            ]);

            return redirect()->route('quality.objectives.index')
                ->with('success', 'Quality Objective updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update objective: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $objective = QualityObjective::findOrFail($id);
            $objective->delete();

            return redirect()->route('quality.objectives.index')
                ->with('success', 'Quality Objective deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete objective: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $objective = QualityObjective::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $objective->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }
}
