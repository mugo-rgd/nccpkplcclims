<?php

namespace App\Http\Controllers;

use App\Models\CorrectiveAction;
use App\Models\QualityObjective;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CorrectiveActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_corrective_actions')->only(['index', 'show']);
        $this->middleware('permission:create_corrective_actions')->only(['create', 'store']);
        $this->middleware('permission:edit_corrective_actions')->only(['edit', 'update']);
        $this->middleware('permission:delete_corrective_actions')->only(['destroy']);
        $this->middleware('permission:approve_corrective_actions')->only(['approve', 'reject']);
    }

    public function index(Request $request)
    {
        $query = CorrectiveAction::with(['objective', 'actionOwner']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('objective_id') && $request->objective_id) {
            $query->where('objective_id', $request->objective_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('car_id', 'like', '%' . $request->search . '%')
                  ->orWhere('issue_description', 'like', '%' . $request->search . '%');
            });
        }

        $correctiveActions = $query->orderBy('created_at', 'desc')->paginate(15);
        $objectives = QualityObjective::pluck('description', 'id');
        $statuses = ['Open', 'In Progress', 'Closed'];

        return view('quality.corrective-actions.index', compact(
            'correctiveActions',
            'objectives',
            'statuses'
        ));
    }

    public function create(Request $request)
    {
        $objective = null;
        if ($request->has('objective_id')) {
            $objective = QualityObjective::findOrFail($request->objective_id);
        }

        $objectives = QualityObjective::where('status', 'active')->pluck('description', 'id');
        $users = User::where('is_active', 1)->pluck('name', 'id');

        // Generate CAR ID
        $lastCar = CorrectiveAction::orderBy('id', 'desc')->first();
        $nextId = $lastCar ? intval(substr($lastCar->car_id, 4)) + 1 : 1;
        $carId = 'CAR-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('quality.corrective-actions.create', compact(
            'objective',
            'objectives',
            'users',
            'carId'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|unique:corrective_actions',
            'objective_id' => 'required|exists:quality_objectives,id',
            'issue_description' => 'required|string',
            'root_cause' => 'nullable|string',
            'action_plan' => 'required|string',
            'action_owner_id' => 'required|exists:users,id',
            'deadline' => 'required|date|after:today',
            'opened_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $correctiveAction = CorrectiveAction::create([
                'car_id' => $request->car_id,
                'objective_id' => $request->objective_id,
                'issue_description' => $request->issue_description,
                'root_cause' => $request->root_cause,
                'action_plan' => $request->action_plan,
                'action_owner_id' => $request->action_owner_id,
                'deadline' => $request->deadline,
                'opened_date' => $request->opened_date,
                'status' => 'Open'
            ]);

            return redirect()->route('quality.corrective-actions.index')
                ->with('success', 'Corrective Action created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create corrective action: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $correctiveAction = CorrectiveAction::with(['objective', 'actionOwner'])->findOrFail($id);
        return view('quality.corrective-actions.show', compact('correctiveAction'));
    }

    public function edit($id)
    {
        $correctiveAction = CorrectiveAction::findOrFail($id);
        $objectives = QualityObjective::where('status', 'active')->pluck('description', 'id');
        $users = User::where('is_active', 1)->pluck('name', 'id');
        $statuses = ['Open', 'In Progress', 'Closed'];

        return view('quality.corrective-actions.edit', compact(
            'correctiveAction',
            'objectives',
            'users',
            'statuses'
        ));
    }

    public function update(Request $request, $id)
    {
        $correctiveAction = CorrectiveAction::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'objective_id' => 'required|exists:quality_objectives,id',
            'issue_description' => 'required|string',
            'root_cause' => 'nullable|string',
            'action_plan' => 'required|string',
            'action_owner_id' => 'required|exists:users,id',
            'deadline' => 'required|date',
            'status' => 'required|in:Open,In Progress,Closed'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->all();

            if ($request->status == 'Closed' && $correctiveAction->status != 'Closed') {
                $data['closed_date'] = now();
            }

            $correctiveAction->update($data);

            return redirect()->route('quality.corrective-actions.index')
                ->with('success', 'Corrective Action updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update corrective action: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $correctiveAction = CorrectiveAction::findOrFail($id);
            $correctiveAction->delete();

            return redirect()->route('quality.corrective-actions.index')
                ->with('success', 'Corrective Action deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete corrective action: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $correctiveAction = CorrectiveAction::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Open,In Progress,Closed'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $data = ['status' => $request->status];

        if ($request->status == 'Closed') {
            $data['closed_date'] = now();
        }

        $correctiveAction->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'status' => $request->status
        ]);
    }

    public function approve($id)
    {
        try {
            $correctiveAction = CorrectiveAction::findOrFail($id);
            $correctiveAction->update([
                'status' => 'Closed',
                'closed_date' => now(),
                'verification_notes' => 'Approved by ' . Auth::user()->name
            ]);

            return redirect()->back()
                ->with('success', 'Corrective Action approved and closed.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to approve corrective action: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $correctiveAction = CorrectiveAction::findOrFail($id);
            $correctiveAction->update([
                'status' => 'Open',
                'verification_notes' => 'Rejected: ' . $request->rejection_reason
            ]);

            return redirect()->back()
                ->with('warning', 'Corrective Action rejected and reopened.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reject corrective action: ' . $e->getMessage());
        }
    }
}
