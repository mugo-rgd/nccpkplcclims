<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use App\Models\TestResult;
use App\Models\UncertaintyCalculation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    /**
     * Show the test entry form for a calibration
     */
public function index(Calibration $calibration)
{
    $calibration->load([
        'customer',
        'performedBy',
    ]);

    $equipmentItems = $calibration->items()
        ->with([
            'testResults' => function ($query) {
                $query->orderBy('test_order');
            },
        ])
        ->orderBy('position')
        ->orderBy('id')
        ->get();

    return view(
        'admin.calibrations.tests.index',
        compact('calibration', 'equipmentItems')
    );
}

    /**
     * Show form to add a new test
     */
 public function create(Request $request, Calibration $calibration)
{
    $testTypes = [
        'accuracy' => 'Accuracy Test',
        'register' => 'Register Test',
        'starting' => 'Starting Current Test',
        'creep' => 'Creep Test',
    ];

    $loadTypes = [
        'low_load' => '0.05 Ib (Low Load)',
        'base_load' => 'Ib (Base Load)',
        'inductive_load' => 'Ib (Inductive Load)',
        'maximum_load' => 'IMAX (Maximum Load)',
    ];

    $powerFactors = ['1.0PF', '0.5LAG'];

    $equipmentItems = $calibration->items()
        ->with('equipmentType')
        ->get();

    // Accept either parameter name for compatibility.
    $selectedEquipmentId = $request->query(
        'calibration_item_id',
        $request->query('equipment_id')
    );

    $selectedEquipment = $equipmentItems->firstWhere(
        'id',
        $selectedEquipmentId
    );

    if (!$selectedEquipment) {
        return redirect()
            ->route('calibrations.tests.index', $calibration)
            ->with(
                'error',
                'Please select the equipment to be tested.'
            );
    }

    if (!$selectedEquipment->hasEnvironmentalConditions()) {
    return redirect()
        ->route('calibrations.items.environment.edit', [
            'calibration' => $calibration->id,
            'item' => $selectedEquipment->id,
        ])
        ->with(
            'error',
            'Record environmental conditions for this equipment before entering test results.'
        );
}

    return view('admin.calibrations.tests.create', compact(
        'calibration',
        'testTypes',
        'loadTypes',
        'powerFactors',
        'equipmentItems',
        'selectedEquipmentId',
        'selectedEquipment'
    ));
}

    /**
     * Store a new test result
     */
       /**
     * Store a new test result
     */
    public function store(Request $request, Calibration $calibration)
    {
        /*
        |--------------------------------------------------------------------------
        | Authenticated Technician
        |--------------------------------------------------------------------------
        */
        $user = $request->user();

        if (!$user) {
            abort(403, 'Authentication is required.');
        }

        /*
        |--------------------------------------------------------------------------
        | Permission Check
        |--------------------------------------------------------------------------
        */
        if (!$user->can('perform_calibrations')) {
            return back()->with(
                'error',
                'You do not have permission to record calibration test results.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Technician Role Check
        |--------------------------------------------------------------------------
        */
        if (!$user->hasAnyRole(['lab_technician', 'technician'])) {
            return back()->with(
                'error',
                'Only laboratory technicians can record test results.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Assigned Technician Check
        |--------------------------------------------------------------------------
        */
        if (
            empty($calibration->performed_by) ||
            (int) $calibration->performed_by !== (int) $user->id
        ) {
            return back()->with(
                'error',
                'Only the technician assigned to this calibration can record test results.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Calibration Status (RELAXED TO ALLOW INITIAL TEST)
        |--------------------------------------------------------------------------
        */
        // Allow adding tests if it's assigned, in progress, or already testing
        if (!in_array($calibration->status, ['assigned', 'in_progress', 'testing'])) {
            return back()->with(
                'error',
                'Test results can only be recorded when the calibration is Assigned, In Progress, or Testing.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Environmental Conditions Required
        |--------------------------------------------------------------------------
        */
       $validatedItem = $request->validate([
    'calibration_item_id' => [
        'required',
        'integer',
        \Illuminate\Validation\Rule::exists(
            'calibration_items',
            'id'
        )->where('calibration_id', $calibration->id),
    ],
]);

$item = $calibration->items()
    ->findOrFail($validatedItem['calibration_item_id']);

if (!$item->hasEnvironmentalConditions()) {
    return redirect()
        ->route('calibrations.items.environment.edit', [
            'calibration' => $calibration->id,
            'item' => $item->id,
        ])
        ->with(
            'error',
            'Record environmental conditions for equipment '
            . $item->serial_number
            . ' before entering test results.'
        );
}

if (in_array($item->status, ['completed', 'certified'], true)) {
    return back()->with(
        'error',
        'This equipment has already completed testing.'
    );
}

        $request->validate([
            'calibration_item_id' => [
    'required',
    'integer',
    \Illuminate\Validation\Rule::exists(
        'calibration_items',
        'id'
    )->where(
        'calibration_id',
        $calibration->id
    ),
],
            'test_name' => 'required|string|max:255',
            'test_type' => 'required|string|max:50',
            'applied_value' => 'nullable|string',
            'power_factor' => 'nullable|string',
            'voltage' => 'nullable|string',
            'frequency' => 'nullable|string',
            'error_value' => 'nullable|numeric',
            'uncertainty' => 'nullable|numeric',
            'result' => 'required|in:PASS,FAIL',
            'tolerance' => 'nullable|numeric|min:0.1|max:1',
            'load_type' => 'nullable|string',
            'start_reading' => 'nullable|numeric',
            'stop_reading' => 'nullable|numeric',
            'energy_difference' => 'nullable|numeric',
            'dial_error' => 'nullable|numeric',
            'trial_data' => 'nullable|array',
            'trial_data.*' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Calculate average error from trial data if available
            $errorValue = $request->error_value;
            $trialData = $request->trial_data;

            if ($trialData && is_array($trialData) && count($trialData) > 0) {
                $validTrials = array_filter($trialData, function($val) {
                    return $val !== null && $val !== '' && is_numeric($val);
                });
                if (count($validTrials) > 0) {
                    $errorValue = array_sum($validTrials) / count($validTrials);
                }
            }

            // Create test result
            $testResult = new TestResult();
            $testResult->calibration_id = $calibration->id;
            $testResult->calibration_item_id = $item->id;
            $testResult->calibration_item_id = $request->calibration_item_id;
            $testResult->test_name = $request->test_name;
            $testResult->test_type = $request->test_type;
            $testResult->applied_value = $request->applied_value;
            $testResult->power_factor = $request->power_factor;
            $testResult->voltage = $request->voltage;
            $testResult->frequency = $request->frequency;
            $testResult->error_value = $errorValue;
            $testResult->uncertainty = $request->uncertainty;
            $testResult->result = $request->result;
            $testResult->tolerance = $request->tolerance ?? 0.5;
            $testResult->start_reading = $request->start_reading;
            $testResult->stop_reading = $request->stop_reading;
            $testResult->energy_difference = $request->energy_difference;
            $testResult->dial_error = $request->dial_error;
            $testResult->trial_data = $trialData;
            $testResult->notes = $request->notes;
            $testResult->test_order = (
    $calibration->testResults()
        ->where(
            'calibration_item_id',
            $request->calibration_item_id
        )
        ->max('test_order') ?? 0
) + 1;

            // Set default values for required fields
            $testResult->parameter = $request->test_name ?? 'Test';
            $testResult->nominal_value = $errorValue ?? 0;
            $testResult->measured_value = $errorValue ?? 0;
            $testResult->tolerance_min = -($request->tolerance ?? 0.5);
            $testResult->tolerance_max = $request->tolerance ?? 0.5;
            $testResult->unit = '%';
            $testResult->is_within_tolerance = $request->result === 'PASS';
            $testResult->error_percentage = $errorValue ?? 0;

            $testResult->save();

            // Save uncertainty calculation if uncertainty exists and trial data is available
            if ($trialData && count($trialData) >= 2) {
                $this->saveUncertaintyCalculation($testResult, $request->load_type ?? 'base_load', $trialData);
            }

            // ---------------------------------------------------------
            // UPDATE CALIBRATION STATUS (UNCOMMENTED AS REQUESTED)
            // ---------------------------------------------------------
            if (in_array($calibration->status, ['assigned', 'in_progress'])) {
                $calibration->status = 'testing';
                $calibration->save();
            }

            DB::commit();

            Log::info(
                'Calibration test result recorded',
                [
                    'calibration_id' => $calibration->id,
                    'test_result_id' => $testResult->id,
                    'performed_by' => $calibration->performed_by,
                    'recorded_by' => $user->id,
                    'status' => $calibration->status,
                ]
            );

            return redirect()->route('calibrations.tests.index', $calibration)
                ->with('success', 'Test result added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Test result error: ' . $e->getMessage());
            return back()->with('error', 'Failed to add test result: ' . $e->getMessage());
        }
    }

    /**
     * Save uncertainty calculation to the database
     */
    private function saveUncertaintyCalculation($testResult, $loadType, $trialData)
    {
        // Filter valid trial data
        $validTrials = array_filter($trialData, function($val) {
            return $val !== null && $val !== '' && is_numeric($val);
        });

        if (count($validTrials) < 2) {
            return;
        }

        $values = array_values($validTrials);
        $avg = array_sum($values) / count($values);

        // Calculate standard deviation
        $squaredDiffs = array_map(function($v) use ($avg) {
            return pow($v - $avg, 2);
        }, $values);
        $variance = array_sum($squaredDiffs) / (count($values) - 1);
        $stdDev = sqrt($variance);

        // u₁: Standard uncertainty due to repeatability
        $u1 = $stdDev / sqrt(count($values));

        // u₂: Standard uncertainty due to reference standard
        $refStdValues = [
            'low_load' => 0.0769,
            'base_load' => 0.1041,
            'inductive_load' => 0.0768,
            'maximum_load' => 0.1178
        ];
        $refStd = $refStdValues[$loadType] ?? 0.0769;
        $u2 = $refStd / 2;

        // u₃: Standard uncertainty due to UUT resolution
        $u3 = (0.1 / 2) / sqrt(3);

        // Combined uncertainty
        $combinedUncertainty = sqrt(pow($u1, 2) + pow($u2, 2) + pow($u3, 2));

        // Expanded uncertainty (k=2 for 95% confidence)
        $expandedUncertainty = $combinedUncertainty * 2;

        // Save to uncertainty_calculations table
        UncertaintyCalculation::updateOrCreate(
            ['test_result_id' => $testResult->id],
            [
                'load_type' => $loadType,
                'u1_repeatability' => $u1,
                'u2_reference_std' => $u2,
                'u3_resolution' => $u3,
                'combined_uncertainty' => $combinedUncertainty,
                'expanded_uncertainty' => $expandedUncertainty,
                'confidence_level' => 95.00,
            ]
        );

        // Update the test result with the uncertainty value
        $testResult->uncertainty = $expandedUncertainty;
        $testResult->save();
    }

    /**
     * Get reference standard uncertainty based on load type
     */
    private function getReferenceStandardUncertainty($loadType)
    {
        $values = [
            'low_load' => 0.0769,
            'base_load' => 0.1041,
            'inductive_load' => 0.0768,
            'maximum_load' => 0.1178
        ];

        return $values[$loadType] ?? 0.0769;
    }

    /**
     * Edit a test result
     */
    public function edit(Calibration $calibration, TestResult $testResult)
    {
        return view('admin.calibrations.tests.edit', compact('calibration', 'testResult'));
    }

    /**
     * Update a test result
     */
    public function update(Request $request, Calibration $calibration, TestResult $testResult)
    {
        $request->validate([
            'test_name' => 'nullable|string|max:255',
            'test_type' => 'nullable|string|max:50',
            'applied_value' => 'nullable|string',
            'power_factor' => 'nullable|string',
            'voltage' => 'nullable|string',
            'frequency' => 'nullable|string',
            'error_value' => 'nullable|numeric',
            'uncertainty' => 'nullable|numeric',
            'result' => 'nullable|in:PASS,FAIL',
            'tolerance' => 'nullable|numeric|min:0.1|max:1',
            'load_type' => 'nullable|string',
            'start_reading' => 'nullable|numeric',
            'stop_reading' => 'nullable|numeric',
            'energy_difference' => 'nullable|numeric',
            'dial_error' => 'nullable|numeric',
            'trial_data' => 'nullable|array',
            'trial_data.*' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Calculate average error from trial data if available
            $errorValue = $request->error_value;
            $trialData = $request->trial_data;

            if ($trialData && is_array($trialData) && count($trialData) > 0) {
                $validTrials = array_filter($trialData, function($val) {
                    return $val !== null && $val !== '' && is_numeric($val);
                });
                if (count($validTrials) > 0) {
                    $errorValue = array_sum($validTrials) / count($validTrials);
                }
            }

            // Update the test result
            $testResult->update([
                'test_name' => $request->test_name,
                'test_type' => $request->test_type,
                'applied_value' => $request->applied_value,
                'power_factor' => $request->power_factor,
                'voltage' => $request->voltage,
                'frequency' => $request->frequency,
                'error_value' => $errorValue,
                'uncertainty' => $request->uncertainty,
                'result' => $request->result,
                'tolerance' => $request->tolerance ?? 0.5,
                'start_reading' => $request->start_reading,
                'stop_reading' => $request->stop_reading,
                'energy_difference' => $request->energy_difference,
                'dial_error' => $request->dial_error,
                'trial_data' => $trialData,
                'notes' => $request->notes,
            ]);

            // Update uncertainty calculation if trial data is available
            if ($trialData && count($trialData) >= 2) {
                $this->saveUncertaintyCalculation($testResult, $request->load_type ?? 'base_load', $trialData);
            } else {
                // Delete uncertainty calculation if no trial data
                UncertaintyCalculation::where('test_result_id', $testResult->id)->delete();
            }

            DB::commit();

            return redirect()->route('calibrations.tests.index', $calibration)
                ->with('success', 'Test result updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update test result: ' . $e->getMessage());
        }
    }

    /**
     * Delete a test result
     */
    public function destroy(Calibration $calibration, TestResult $testResult)
    {
        DB::beginTransaction();

        try {
            // Delete uncertainty calculation if exists
            UncertaintyCalculation::where('test_result_id', $testResult->id)->delete();

            // Delete the test result
            $testResult->delete();

            DB::commit();

            return redirect()->route('calibrations.tests.index', $calibration)
                ->with('success', 'Test result deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete test result: ' . $e->getMessage());
        }
    }

    /**
     * Get test results for a calibration (AJAX)
     */
    public function getResults(Calibration $calibration)
    {
        $results = $calibration->testResults()
            ->with('uncertaintyCalculation')
            ->orderBy('test_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Generate certificate from test results
     */
    public function generateCertificate(Calibration $calibration)
    {
        // Check if all required tests are completed
        $requiredTests = ['accuracy', 'register'];
        $completedTests = $calibration->testResults()
            ->whereIn('test_type', $requiredTests)
            ->pluck('test_type')
            ->unique()
            ->toArray();

        $missingTests = array_diff($requiredTests, $completedTests);
        if (!empty($missingTests)) {
            return back()->with('error', 'Please complete all required tests: ' . implode(', ', $missingTests));
        }

        // Generate certificate
        return redirect()->route('certificates.generate', ['calibration' => $calibration->id]);
    }
}
