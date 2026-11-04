<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use App\Models\CalibrationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EquipmentTestReportController extends Controller
{
    public function store(
        Request $request,
        Calibration $calibration,
        CalibrationItem $item
    ) {
        abort_unless(
            (int) $item->calibration_id === (int) $calibration->id,
            404
        );

        abort_unless(
            $request->user()->can('perform_calibrations')
            && (int) $calibration->performed_by
                === (int) $request->user()->id
            && $calibration->status === Calibration::STATUS_TESTING,
            403
        );

        $request->validate([
            'test_report' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ]);

        $file = $request->file('test_report');

        $path = $file->store(
            "calibrations/{$calibration->id}/items/{$item->id}/test-reports",
            'local'
        );

        try {
            DB::transaction(function () use (
                $item,
                $path,
                $file,
                $request,
                &$oldPath
            ) {
                $locked = CalibrationItem::query()
                    ->lockForUpdate()
                    ->findOrFail($item->id);

                if ($locked->review_status !== 'testing') {
                    throw ValidationException::withMessages([
                        'test_report' => 'Reopen equipment testing '
                            . 'before replacing the test report.',
                    ]);
                }

                $oldPath = $locked->test_report_path;

                $locked->update([
                    'test_report_path' => $path,
                    'test_report_original_name' =>
                        $file->getClientOriginalName(),
                    'test_report_uploaded_by' => $request->user()->id,
                    'test_report_uploaded_at' => now(),
                ]);
            });

            if (!empty($oldPath) && $oldPath !== $path) {
                Storage::disk('local')->delete($oldPath);
            }

        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return back()->with(
            'success',
            'Test Machine Report uploaded successfully.'
        );
    }

    public function download(
        Request $request,
        Calibration $calibration,
        CalibrationItem $item
    ) {
        abort_unless(
            (int) $item->calibration_id === (int) $calibration->id,
            404
        );

        abort_unless(
            $request->user()->can('perform_calibrations')
            || $request->user()->hasAnyRole([
                'supervisor',
                'checker',
                'lab_manager',
                'super_admin',
            ]),
            403
        );

        abort_unless(
            $item->test_report_path
            && Storage::disk('local')->exists(
                $item->test_report_path
            ),
            404
        );

        return Storage::disk('local')->download(
            $item->test_report_path,
            basename(
                $item->test_report_original_name
                    ?: 'test-machine-report.pdf'
            )
        );
    }
}
