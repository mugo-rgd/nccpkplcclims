<?php

namespace App\Services;

use App\Models\Calibration;
use App\Models\CalibrationItem;
use Illuminate\Validation\ValidationException;

/**
 * Certificate readiness checks. Machine-generated PDF reports are OPTIONAL.
 * This class never checks test_report_path or requires an attachment.
 */
class CalibrationCertificateEligibility
{
    public function validate(Calibration $calibration): void
    {
        $this->assertCompleted($calibration);
        $items = $calibration->items()->get();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'certificate' => 'Cannot generate a certificate without equipment.',
            ]);
        }

        foreach ($items as $item) {
            $this->validateItem($item);
        }
    }

    public function validateItem(CalibrationItem $item): void
    {
        $calibration = $item->calibration;
        if (!$calibration) {
            throw ValidationException::withMessages([
                'certificate' => 'This equipment is not associated with a calibration.',
            ]);
        }

        $this->assertCompleted($calibration);
        $serial = trim((string) ($item->serial_number ?: $item->id));

        if ($item->review_status !== 'approved') {
            throw ValidationException::withMessages([
                'certificate' => "Equipment {$serial} has not been approved.",
            ]);
        }

        if (!$item->hasEnvironmentalConditions()) {
            throw ValidationException::withMessages([
                'certificate' => "Environmental conditions are missing for {$serial}.",
            ]);
        }

        if (!$item->testResults()->exists()) {
            throw ValidationException::withMessages([
                'certificate' => "Test results are missing for {$serial}.",
            ]);
        }

        // Intentionally no Test Machine Report requirement.
    }

    private function assertCompleted(Calibration $calibration): void
    {
        if (!in_array((string) $calibration->status, [
            Calibration::STATUS_COMPLETED,
            Calibration::STATUS_CERTIFIED,
        ], true)) {
            throw ValidationException::withMessages([
                'certificate' => 'Complete the calibration before generating a certificate.',
            ]);
        }
    }
}
