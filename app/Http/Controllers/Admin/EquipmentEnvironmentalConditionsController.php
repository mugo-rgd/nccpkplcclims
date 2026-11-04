<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use App\Models\CalibrationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EquipmentEnvironmentalConditionsController extends Controller
{
    private function authorizeTechnician(
        Request $request,
        Calibration $calibration,
        CalibrationItem $item
    ): void {
        abort_unless(
            (int) $item->calibration_id === (int) $calibration->id,
            404
        );

        $user = $request->user();

        abort_unless(
            $user
            && $user->hasAnyRole(['technician', 'lab_technician'])
            && $user->can('perform_calibrations')
            && (int) $calibration->performed_by === (int) $user->id,
            403,
            'Only the assigned laboratory technician may record conditions.'
        );

        abort_unless(
            in_array(
                $calibration->status,
                ['assigned', 'in_progress', 'testing'],
                true
            ),
            403,
            'Calibration is not available for testing.'
        );

        abort_if(
            in_array($item->status, ['completed', 'certified'], true),
            403,
            'Completed equipment cannot be modified.'
        );
    }

    public function edit(
        Request $request,
        Calibration $calibration,
        CalibrationItem $item
    ) {
        $this->authorizeTechnician($request, $calibration, $item);

        return view(
            'admin.calibrations.tests.environmental-conditions',
            compact('calibration', 'item')
        );
    }

    public function update(
        Request $request,
        Calibration $calibration,
        CalibrationItem $item
    ) {
        $this->authorizeTechnician($request, $calibration, $item);

        $data = $request->validate([
            'temperature_start' => 'required|numeric|between:-99.99,99.99',
            'temperature_end' => 'required|numeric|between:-99.99,99.99',
            'humidity_start' => 'required|numeric|between:0,100',
            'humidity_end' => 'required|numeric|between:0,100',
            'supply_voltage' => 'required|numeric|between:0,999999.99',
            'supply_frequency' => 'required|numeric|between:0,9999.99',
        ]);

        DB::transaction(function () use ($item, $request, $data) {
            $lockedItem = CalibrationItem::query()
                ->lockForUpdate()
                ->findOrFail($item->id);

            // Do not silently overwrite conditions after testing begins.
            if ($lockedItem->testResults()->exists()) {
                throw ValidationException::withMessages([
                    'environment' =>
                        'Test results already exist for this equipment. '
                        . 'Use a controlled correction process to change conditions.',
                ]);
            }

            $lockedItem->fill($data);

            $lockedItem->test_parameters_recorded_by =
                $request->user()->id;

            $lockedItem->test_parameters_recorded_at = now();

            $lockedItem->save();
        });

        return redirect()
            ->route('calibrations.tests.create', [
                'calibration' => $calibration->id,
                'calibration_item_id' => $item->id,
            ])
            ->with(
                'success',
                'Environmental conditions recorded for equipment '
                . $item->serial_number
                . '. You may now enter test results.'
            );
    }
}
