<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Calibration;
use App\Models\KplcServiceType;
use App\Models\KplcEquipmentType;
use PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\CalibrationItem;
use App\Services\CalibrationCertificateEligibility;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CertificateService
{
    /**
     * Generate certificate from calibration
     */
    public function generateFromCalibration(Calibration $calibration): Certificate
{
    if ($calibration->items()->exists()) {
        throw ValidationException::withMessages([
            'certificate' => 'Generate certificates separately for each equipment item.',
        ]);
    }

    // Get service types
    $serviceTypes = collect();
    if ($calibration->service_types) {
        $serviceTypeIds = is_array($calibration->service_types)
            ? $calibration->service_types
            : (is_string($calibration->service_types) ? json_decode($calibration->service_types, true) : []);
        if (!empty($serviceTypeIds)) {
            $serviceTypes = KplcServiceType::whereIn('id', $serviceTypeIds)->get();
        }
    }

    // Get equipment type
    $equipmentType = null;
    if ($calibration->equipment_type_id) {
        $equipmentType = KplcEquipmentType::find($calibration->equipment_type_id);
    }

    // Get all service type names
    $serviceTypeNames = $serviceTypes->pluck('serv_name')->implode(', ');
    $serviceTypeMethods = $serviceTypes->pluck('serv_method')->implode(', ');
    $serviceTypeNotes = $serviceTypes->pluck('notes')->implode('; ');

    // Calculate test result from actual test results
    $testResult = 'Pass';
    if ($calibration->test_results && is_array($calibration->test_results)) {
        $allPassed = true;
        foreach ($calibration->test_results as $result) {
            if (isset($result['is_within_tolerance']) && !$result['is_within_tolerance']) {
                $allPassed = false;
                break;
            }
        }
        $testResult = $allPassed ? 'Pass' : 'Fail';
    }

    // Check if certificate already exists
    $certificate = Certificate::where('calibration_id', $calibration->id)->first();

    if (!$certificate) {
        $certificate = Certificate::create([
            'calibration_id' => $calibration->id,
            'customer_id' => $calibration->customer_id,
            'certificate_number' => Certificate::generateCertificateNumber(),
            'reference_number' => 'REF-' . strtoupper(uniqid()),

            // Equipment details
            'equipment_name' => $calibration->equipment_name,
            'serial_number' => $calibration->serial_number,
            'manufacturer' => $calibration->manufacturer,
            'model' => $calibration->model,
            'equipment_type' => $equipmentType ? $equipmentType->name : $calibration->equipment_type,
            'accuracy_class' => $calibration->accuracy_class,
            'ratings' => $calibration->ratings,

            // Service details - combine all service types
            'service_name' => $serviceTypeNames ?: 'Calibration Service',
            'service_method' => $serviceTypeMethods ?: 'N/A',
            'service_notes' => $serviceTypeNotes ?: null,

            // Legacy test conditions (from calibration or defaults): use item-specific generation for multi-equipment records
            'voltage' => $calibration->test_conditions['voltage'] ?? 240,
            'current' => $calibration->test_conditions['current'] ?? 24.00,
            'temperature' => $calibration->test_conditions['temperature'] ?? 25.00,
            'humidity' => $calibration->test_conditions['humidity'] ?? 45.00,
            'equipment_constant' => $calibration->test_conditions['constant'] ?? 1000.00,
            'equipment_class' => $calibration->accuracy_class ?? '1',
            'wiring' => $calibration->test_conditions['wiring'] ?? '11',

            // Test results
            'test_results' => $calibration->test_results ?? [],
            'test_result' => $testResult,

            // Statements must be reviewed and confirmed by the supervisor.
            'traceability_information' => null,
            'statement_conformity' => null,
            'comments' => $calibration->notes ?? null,

            // Status
            'status' => 'draft',
        ]);
    }

    return $certificate;
}

    /**
     * Generate PDF for certificate
     */
   public function generatePDF(Certificate $certificate): string
{
    try {
        // Load relationships
        $certificate->load(['calibration', 'customer', 'generatedBy', 'checkedBy', 'approvedBy']);

        // Get service types for the calibration
        $serviceTypes = collect();
        if ($certificate->calibration && ($certificate->calibration_item_id ? CalibrationItem::find($certificate->calibration_item_id)?->service_types : $certificate->calibration->service_types)) {
            $rawTypes = $certificate->calibration_item_id
                ? CalibrationItem::find($certificate->calibration_item_id)?->service_types
                : $certificate->calibration->service_types;
            $serviceTypeIds = is_array($rawTypes) ? $rawTypes
                : (is_string($rawTypes) ? (json_decode($rawTypes, true) ?: []) : []);
            if (!empty($serviceTypeIds)) {
                $serviceTypes = KplcServiceType::whereIn('id', $serviceTypeIds)->get();
            }
        }

        $data = [
            'certificate' => $certificate,
            'service' => $certificate->calibration,
            'batch' => $certificate->customer,
            'stypes' => $serviceTypes->first(),
            'referenceInstruments' => $this->certificateReferenceInstruments($certificate),
        ];

        // Generate PDF
        $pdf = PDF::loadView('admin.certificates.certificate-pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        // Ensure directory exists
        $storagePath = storage_path('app/public/certificates');
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        // Sanitize filename - remove slashes and special characters
        $cleanCertNumber = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $certificate->certificate_number);
        $filename = 'certificate-' . $cleanCertNumber . '.pdf';
        $fullPath = $storagePath . '/' . $filename;

        // Save the PDF file
        file_put_contents($fullPath, $pdf->output());

        // Update certificate with PDF path (relative to public storage)
        $certificate->update(['pdf_path' => 'certificates/' . $filename]);

        return $fullPath;

    } catch (\Exception $e) {
        \Log::error('Failed to generate PDF: ' . $e->getMessage());
        throw $e;
    }
}

    /**
     * Check certificate (Supervisor)
     */
    public function checkCertificate(Certificate $certificate, int $userId): Certificate
    {
        $certificate->update([
            'status' => 'checked',
            'checked_by' => $userId,
            'checked_at' => now(),
        ]);

        // Regenerate PDF with signatures
        $this->generatePDF($certificate);

        return $certificate;
    }

    /**
     * Approve certificate (Lab Manager)
     */
    public function approveCertificate(Certificate $certificate, int $userId): Certificate
    {
        $certificate->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        // Regenerate PDF with signatures
        $this->generatePDF($certificate);

        return $certificate;
    }

    /**
     * Send certificate to customer
     */
    public function sendCertificate(Certificate $certificate, int $userId): Certificate
    {
        $certificate->update([
            'status' => 'sent',
            'sent_by' => $userId,
            'sent_at' => now(),
        ]);

        // Send email with PDF
        $this->sendEmail($certificate);

        return $certificate;
    }

    /**
     * Send certificate email
     */
    private function sendEmail(Certificate $certificate): void
    {
        try {
            $customer = $certificate->customer;
            $email = $customer->email;

            if (!$email) {
                Log::warning('No email found for customer: ' . $customer->id);
                return;
            }

            $pdfPath = storage_path('app/public/' . $certificate->pdf_path);

            if (!file_exists($pdfPath)) {
                Log::warning('PDF file not found: ' . $pdfPath);
                return;
            }

            $pdfName = 'Certificate_' . $certificate->certificate_number . '.pdf';

            Mail::send('emails.certificate', ['certificate' => $certificate], function ($message) use ($email, $pdfPath, $pdfName, $certificate) {
                $message->to($email)
                        ->subject('Calibration Certificate - ' . $certificate->certificate_number)
                        ->attach($pdfPath, [
                            'as' => $pdfName,
                            'mime' => 'application/pdf',
                        ]);
            });

            Log::info('Certificate sent to: ' . $email);

        } catch (\Exception $e) {
            Log::error('Failed to send certificate email: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get certificate preview data
     */
    public function getPreviewData(Certificate $certificate): array
    {
        $certificate->load(['calibration', 'customer', 'generatedBy', 'checkedBy', 'approvedBy']);

        $serviceTypes = collect();
        if ($certificate->calibration && ($certificate->calibration_item_id ? CalibrationItem::find($certificate->calibration_item_id)?->service_types : $certificate->calibration->service_types)) {
            $rawTypes = $certificate->calibration_item_id
                ? CalibrationItem::find($certificate->calibration_item_id)?->service_types
                : $certificate->calibration->service_types;
            $serviceTypeIds = is_array($rawTypes) ? $rawTypes
                : (is_string($rawTypes) ? (json_decode($rawTypes, true) ?: []) : []);
            if (!empty($serviceTypeIds)) {
                $serviceTypes = KplcServiceType::whereIn('id', $serviceTypeIds)->get();
            }
        }

        return [
            'certificate' => $certificate,
            'service' => $certificate->calibration,
            'batch' => $certificate->customer,
            'stypes' => $serviceTypes->first(),
            'referenceInstruments' => $this->certificateReferenceInstruments($certificate),
        ];
    }

    /**
 * Sanitize filename
 */
private function sanitizeFilename($filename): string
{
    return str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $filename);
}
/////////////////
/**
 * Attach test report PDF to certificate
 */
// public function attachTestReport(Certificate $certificate, $file, array $machineData = []): Certificate
// {
//     try {
//         // Validate file
//         if (!$file || !$file->isValid()) {
//             throw new \Exception('Invalid file uploaded.');
//         }

//         // Validate file type
//         if ($file->getClientMimeType() !== 'application/pdf') {
//             throw new \Exception('File must be a PDF.');
//         }

//         // Validate file size (max 10MB)
//         if ($file->getSize() > 10 * 1024 * 1024) {
//             throw new \Exception('File size exceeds 10MB limit.');
//         }

//         // Sanitize filename
//         $cleanCertNumber = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $certificate->certificate_number);
//         $filename = 'test-report-' . $cleanCertNumber . '.pdf';
//         $originalName = $file->getClientOriginalName();

//         // Store file
//         $path = $file->storeAs('test-reports', $filename, 'public');

//         // Update certificate
//         $certificate->update([
//             'test_report_attachment_path' => $path,
//             'test_report_attachment_filename' => $filename,
//             'test_report_original_name' => $originalName,
//             'machine_used' => $machineData['machine_used'] ?? 'N/A',
//             'machine_serial' => $machineData['machine_serial'] ?? 'N/A',
//             'test_engineer' => $machineData['test_engineer'] ?? auth()->user()->name ?? 'N/A',
//             'test_date' => $machineData['test_date'] ?? now(),
//         ]);

//         return $certificate;

//     } catch (\Exception $e) {
//         \Log::error('Failed to attach test report: ' . $e->getMessage());
//         throw $e;
//     }
// }

/**
 * Get test report attachment path
 */
public function getTestReportPath(Certificate $certificate): ?string
{
    $path = $certificate->test_report_attachment_path;
    if (!$path && $certificate->calibration_item_id) {
        $item = CalibrationItem::find($certificate->calibration_item_id);
        $path = $item?->test_report_path;
    }
    if (!$path) {
        return null;
    }

    foreach (['local', 'public'] as $disk) {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->path($path);
        }
    }
    return null;
}

/**
 * Generate a certificate for one approved calibration item.
 *
 * This creates the certificate record. PDF generation is handled
 * separately by the controller after the transaction succeeds.
 */
public function generateFromCalibrationItem(
    CalibrationItem $item,
    ?int $generatedBy = null
): Certificate {
    app(CalibrationCertificateEligibility::class)
        ->validateItem($item);

    return DB::transaction(function () use ($item, $generatedBy) {

        $item = CalibrationItem::query()
            ->whereKey($item->id)
            ->lockForUpdate()
            ->firstOrFail();

        // Revalidate after acquiring the lock.
        app(CalibrationCertificateEligibility::class)
            ->validateItem($item);

        $existing = Certificate::query()
            ->where('calibration_item_id', $item->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $calibration = $item->calibration;

        $serviceTypeIds = collect(is_string($item->service_types)
            ? (json_decode($item->service_types, true) ?: [])
            : ($item->service_types ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        $serviceTypes = KplcServiceType::query()
            ->whereIn('id', $serviceTypeIds)
            ->get();

        $testResults = $item->testResults()->get();

        if ($testResults->isEmpty()) {
            throw ValidationException::withMessages([
                'certificate' => 'No test results found for this equipment.',
            ]);
        }

        /*
         * Do not infer Pass/Fail from record existence.
         * Result evaluation must use the applicable test method.
         */
        $certificate = Certificate::create([
            'calibration_id' => $calibration->id,
            'calibration_item_id' => $item->id,
            'customer_id' => $calibration->customer_id,

            'certificate_number' => $this->nextItemCertificateNumber(),
            'reference_number' => 'REF-' . strtoupper(
                \Illuminate\Support\Str::random(12)
            ),

            'equipment_name' => $item->equipment_name,
            'serial_number' => $item->serial_number,
            'manufacturer' => $item->manufacturer,
            'model' => $item->model,
            'accuracy_class' => $item->accuracy_class,
            'ratings' => $item->ratings,

            'service_name' => $serviceTypes
                ->pluck('serv_name')
                ->filter()
                ->implode(', ') ?: 'Calibration Service',

            'service_method' => $serviceTypes
                ->pluck('serv_method')
                ->filter()
                ->implode(', ') ?: null,

            'service_notes' => $serviceTypes
                ->pluck('notes')
                ->filter()
                ->implode('; ') ?: null,

            'voltage' => $item->supply_voltage,

            'temperature' => (
                (float) $item->temperature_start +
                (float) $item->temperature_end
            ) / 2,

            'humidity' => (
                (float) $item->humidity_start +
                (float) $item->humidity_end
            ) / 2,

            'test_results' => $testResults
                ->map(fn ($result) => $result->toArray())
                ->all(),

            'test_result' => 'Pending evaluation',
            'statement_conformity' => null,
            'traceability_information' => null,

            'comments' => $item->notes,

            'status' => 'generated',
            'generated_by' => $generatedBy,
            'generated_at' => now(),

            'test_report_attachment_path' => $item->test_report_path ?: null,
            'test_report_original_name' => $item->test_report_path ? $item->test_report_original_name : null,
        ]);

        return $certificate;
    });
}

/**
 * Allocate a certificate number while the calling transaction holds
 * an exclusive lock on the calibration item.
 *
 * Uses a random unique suffix to avoid count()+1 collisions.
 */
private function nextItemCertificateNumber(): string
{
    do {
        $number = 'KP1/13B.1/5/3/'
            . now()->format('Y')
            . '/'
            . strtoupper(\Illuminate\Support\Str::random(10));

    } while (
        Certificate::withTrashed()
            ->where('certificate_number', $number)
            ->exists()
    );

    return $number;
}

/**
 * Generate certificate with test report combined
 */
    /** Reference instruments used for this certificate's specific item. */
    private function certificateReferenceInstruments(Certificate $certificate): \Illuminate\Support\Collection
    {
        if ($certificate->calibration_item_id) {
            $item = CalibrationItem::with(['referenceInstruments', 'laboratoryEquipment'])
                ->find($certificate->calibration_item_id);
            if (!$item) {
                return collect();
            }
            $instruments = $item->referenceInstruments;
            // Backwards compatibility with the existing single instrument field.
            if ($item->laboratoryEquipment && !$instruments->contains('id', $item->laboratoryEquipment->id)) {
                $instruments->push($item->laboratoryEquipment);
            }
            return $instruments;
        }
        $equipment = $certificate->calibration?->equipment;
        return $equipment ? collect([$equipment]) : collect();
    }

public function generateCertificateWithReport(Certificate $certificate): string
{
    $certificatePdf = $this->generatePDF($certificate);
    $report = $this->getTestReportPath($certificate);

    if ($report === null) {
        return $certificatePdf;
    }

    if (!class_exists(\setasign\Fpdi\Fpdi::class)) {
        throw ValidationException::withMessages([
            'test_report' => 'The report is attached, but PDF merging is not installed. Install setasign/fpdi to download the certificate and report together.',
        ]);
    }

    $merger = new \setasign\Fpdi\Fpdi();
    foreach ([$certificatePdf, $report] as $source) {
        $pageCount = $merger->setSourceFile($source);
        for ($page = 1; $page <= $pageCount; $page++) {
            $template = $merger->importPage($page);
            $size = $merger->getTemplateSize($template);
            $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $merger->useTemplate($template);
        }
    }

    $directory = storage_path('app/public/certificates-combined');
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new \RuntimeException('Unable to create combined certificate directory.');
    }
    $name = 'certificate-with-report-' . $this->sanitizeFilename($certificate->certificate_number) . '.pdf';
    $output = $directory . DIRECTORY_SEPARATOR . $name;
    $merger->Output('F', $output);
    return $output;
}
}
