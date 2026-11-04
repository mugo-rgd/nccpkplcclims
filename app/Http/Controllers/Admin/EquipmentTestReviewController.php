<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use App\Models\CalibrationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class EquipmentTestReviewController extends Controller
{
    private function checkOwnership(
        Calibration $calibration,
        CalibrationItem $item
    ): void {
        abort_unless(
            (int) $item->calibration_id === (int) $calibration->id,
            404
        );
    }

    private function transition(
        Calibration $calibration,
        CalibrationItem $item,
        string $expectedStatus,
        callable $callback
    ): void {
        DB::transaction(function () use (
            $calibration,
            $item,
            $expectedStatus,
            $callback
        ) {
            $locked = CalibrationItem::query()
                ->where('calibration_id', $calibration->id)
                ->lockForUpdate()
                ->findOrFail($item->id);

            if ($locked->review_status !== $expectedStatus) {
                throw ValidationException::withMessages([
                    'review' => 'This equipment has already changed '
                        . 'workflow status. Refresh the page.',
                ]);
            }

            $callback($locked);
            $locked->save();
        });
    }

    public function submit(
        Request $request,
        Calibration $calibration,
        CalibrationItem $item
    ) {
        $this->checkOwnership($calibration, $item);

        abort_unless(
            $request->user()->can('perform_calibrations')
            && (int) $calibration->performed_by
                === (int) $request->user()->id,
            403
        );

        abort_unless(
            $calibration->status === Calibration::STATUS_TESTING,
            403
        );

        $this->transition(
            $calibration,
            $item,
            'testing',
            function ($locked) use ($request) {
                if (!$locked->hasEnvironmentalConditions()) {
                    throw ValidationException::withMessages([
                        'review' => 'Record all environmental conditions '
                            . 'before submitting equipment for review.',
                    ]);
                }

                if (!$locked->testResults()->exists()) {
                    throw ValidationException::withMessages([
                        'review' => 'No test results have been recorded '
                            . 'for this equipment.',
                    ]);
                }

                if (
    !$locked->test_report_path
    || !Storage::disk('local')->exists($locked->test_report_path)
) {
    throw ValidationException::withMessages([
        'review' => 'Upload the Test Machine Report '
            . 'before submitting equipment for review.',
    ]);
}

                // A separate required-test checklist must be validated
                // here before production deployment.

                $locked->review_status = 'pending_review';
                $locked->testing_completed_by = $request->user()->id;
                $locked->testing_completed_at = now();
                $locked->reviewed_by = null;
                $locked->reviewed_at = null;
                $locked->approved_by = null;
                $locked->approved_at = null;
            }
        );

        return back()->with(
            'success',
            'Equipment testing submitted for supervisor review.'
        );
    }

    public function review(
        Request $request,
        Calibration $calibration,
        CalibrationItem $item
    ) {
        $this->checkOwnership($calibration, $item);

        abort_unless(
            $request->user()->hasAnyRole(['supervisor', 'checker']),
            403
        );

        $data = $request->validate([
            'comments' => 'nullable|string|max:5000',
        ]);

        $this->transition(
            $calibration,
            $item,
            'pending_review',
            function ($locked) use ($request, $data) {
                if (
                    (int) $locked->testing_completed_by
                    === (int) $request->user()->id
                ) {
                    throw ValidationException::withMessages([
                        'review' => 'You cannot review testing '
                            . 'that you submitted.',
                    ]);
                }

                $locked->review_status = 'reviewed';
                $locked->reviewed_by = $request->user()->id;
                $locked->reviewed_at = now();
                $locked->review_comments = $data['comments'] ?? null;
            }
        );

        return back()->with(
            'success',
            'Equipment test results reviewed successfully.'
        );
    }

    public function returnForCorrection(
        Request $request,
        Calibration $calibration,
        CalibrationItem $item
    ) {
        $this->checkOwnership($calibration, $item);

        abort_unless(
            $request->user()->hasAnyRole([
                'supervisor',
                'checker',
                'lab_manager',
            ]),
            403
        );

        $data = $request->validate([
            'comments' => 'required|string|max:5000',
        ]);

        DB::transaction(function () use (
            $calibration,
            $item,
            $request,
            $data
        ) {
            $locked = CalibrationItem::query()
                ->where('calibration_id', $calibration->id)
                ->lockForUpdate()
                ->findOrFail($item->id);

            if (!in_array(
                $locked->review_status,
                ['pending_review', 'reviewed'],
                true
            )) {
                throw ValidationException::withMessages([
                    'review' => 'Equipment cannot be returned '
                        . 'from its current status.',
                ]);
            }

            if (
                (int) $locked->testing_completed_by
                === (int) $request->user()->id
            ) {
                throw ValidationException::withMessages([
                    'review' => 'You cannot review your own testing.',
                ]);
            }

            $locked->review_status = 'returned';
            $locked->review_comments = $data['comments'];
            $locked->reviewed_by = $request->user()->id;
            $locked->reviewed_at = now();
            $locked->approved_by = null;
            $locked->approved_at = null;
            $locked->save();
        });

        return back()->with(
            'success',
            'Equipment returned for correction.'
        );
    }

    public function reopen(
        Request $request,
        Calibration $calibration,
        CalibrationItem $item
    ) {
        $this->checkOwnership($calibration, $item);

        abort_unless(
            $request->user()->can('perform_calibrations')
            && (int) $calibration->performed_by
                === (int) $request->user()->id,
            403
        );

        abort_unless(
            $calibration->status === Calibration::STATUS_TESTING,
            403
        );

        $this->transition(
            $calibration,
            $item,
            'returned',
            function ($locked) {
                $locked->review_status = 'testing';
                $locked->testing_completed_at = null;
                $locked->testing_completed_by = null;
                $locked->reviewed_at = null;
                $locked->reviewed_by = null;
            }
        );

        return back()->with(
            'success',
            'Equipment reopened for correction.'
        );
    }

    public function approve(
        Request $request,
        Calibration $calibration,
        CalibrationItem $item
    ) {
        $this->checkOwnership($calibration, $item);

        abort_unless(
            $request->user()->hasRole('lab_manager'),
            403
        );

        $data = $request->validate([
            'comments' => 'nullable|string|max:5000',
        ]);

        $this->transition(
            $calibration,
            $item,
            'reviewed',
            function ($locked) use ($request, $data) {
                if (
                    (int) $locked->testing_completed_by
                        === (int) $request->user()->id
                    || (int) $locked->reviewed_by
                        === (int) $request->user()->id
                ) {
                    throw ValidationException::withMessages([
                        'review' => 'You cannot approve equipment '
                            . 'testing that you performed or reviewed.',
                    ]);
                }

                $locked->review_status = 'approved';
                $locked->approved_by = $request->user()->id;
                $locked->approved_at = now();
                $locked->approval_comments = $data['comments'] ?? null;
            }
        );

        return back()->with(
            'success',
            'Equipment test results approved.'
        );
    }
}
