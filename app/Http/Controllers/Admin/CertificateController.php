<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Calibration;
use App\Models\Customer;
use App\Models\CustomerRequest;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\JobCard;
use App\Models\KplcEquipmentType;
use App\Models\KplcServiceType;
use App\Models\TestResult;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\TrialDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PDF;
use setasign\Fpdi\Fpdi;
use App\Mail\CalibrationCertificateMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\CertificateRequiresApprovalMail;
use App\Mail\CertificateRequiresCheckingMail;
use App\Services\CalibrationCertificateEligibility;

class CertificateController extends Controller
{
    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_GENERATED = 'generated';
    const STATUS_CHECKED = 'checked';
    const STATUS_APPROVED = 'approved';
    const STATUS_SENT = 'sent';
    const STATUS_ISSUED = 'issued';

    /**
     * Allowed statuses for various operations
     */
    const ALLOWED_GENERATE_STATUSES = ['completed', 'certified'];
    const ALLOWED_DOWNLOAD_STATUSES = ['approved', 'sent', 'issued'];
    const ALLOWED_EDIT_STATUSES = ['draft', 'generated'];

    /**
     * @var CertificateService
     */
    protected $certificateService;

    /**
     * @var TrialDataService
     */
    protected $trialDataService;

    /**
     * CertificateController constructor.
     *
     * @param CertificateService $certificateService
     * @param TrialDataService $trialDataService
     */
    public function __construct(CertificateService $certificateService, TrialDataService $trialDataService)
    {
        $this->certificateService = $certificateService;
        $this->trialDataService = $trialDataService;
    }

    /**
     * Display certificates list
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Certificate::with(['calibration', 'calibrationItem', 'customer', 'generatedBy', 'checkedBy', 'approvedBy']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('certificate_number', 'LIKE', "%{$request->search}%")
                  ->orWhere('equipment_name', 'LIKE', "%{$request->search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$request->search}%")
                  ->orWhere('manufacturer', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $certificates = $query->orderBy('created_at', 'desc')->paginate(15);
        $statuses = [self::STATUS_DRAFT, self::STATUS_GENERATED, self::STATUS_CHECKED, self::STATUS_APPROVED, self::STATUS_SENT];

        return view('admin.certificates.index', compact('certificates', 'statuses'));
    }

    /**
     * Generate certificate from calibration
     *
     * @param Request $request
     * @param Calibration $calibration
     * @return \Illuminate\Http\RedirectResponse
     */

public function generate(Request $request, Calibration $calibration)
{
    // Legacy calibration-level POST: do not generate a combined certificate
    // or require a report for every item. Direct staff to per-item actions.
    return redirect()->route('calibrations.show', $calibration)
        ->with('error', 'Select an equipment item and use its Generate Certificate button. Each item has a separate certificate.');
}

    /**
     * Process test results with trial data
     *
     * @param int $calibrationId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function processTestResults($calibrationId, ?int $calibrationItemId = null)
    {
        $query = TestResult::query()->where('calibration_id', $calibrationId);
        if ($calibrationItemId !== null) {
            $query->where('calibration_item_id', $calibrationItemId);
        }

        $testResults = $query->orderBy('test_order')->orderBy('id')->get();

        foreach ($testResults as $result) {
            $trials = $result->trial_data;
            if (is_string($trials)) {
                $trials = json_decode($trials, true);
            }
            $trials = is_array($trials) ? $trials : [];
            $numeric = array_values(array_filter($trials, static fn ($v) => $v !== null && $v !== '' && is_numeric($v)));
            $count = count($numeric);
            $mean = $count ? array_sum($numeric) / $count : null;
            $stdDev = null;
            if ($count > 1) {
                $variance = array_sum(array_map(static fn ($v) => pow((float)$v - $mean, 2), $numeric)) / $count;
                $stdDev = sqrt($variance);
            }
            $result->setAttribute('trial_data_array', $trials);
            $result->setAttribute('trial_data_stats', [
                'mean' => $mean, 'min' => $count ? min($numeric) : null,
                'max' => $count ? max($numeric) : null, 'std_dev' => $stdDev, 'count' => $count,
            ]);

            // Never overwrite the measured error or stored technical assessment.
            $error = $result->getRawOriginal('error_percentage');
            $assessment = $result->getRawOriginal('is_within_tolerance');
            $assessment = $assessment === null ? null : filter_var($assessment, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $result->setAttribute('formatted_nominal', is_numeric($result->nominal_value) ? number_format((float)$result->nominal_value, 4).' '.($result->unit ?? '') : 'N/A');
            $result->setAttribute('formatted_measured', is_numeric($result->measured_value) ? number_format((float)$result->measured_value, 4).' '.($result->unit ?? '') : 'N/A');
            $result->setAttribute('formatted_error', is_numeric($error) ? number_format((float)$error, 4).'%' : 'N/A');
            $result->setAttribute('formatted_tolerance', $result->tolerance_min !== null && $result->tolerance_max !== null
                ? $result->tolerance_min.' to '.$result->tolerance_max.' '.($result->unit ?? '') : 'Not recorded');
            $result->setAttribute('status_badge', $assessment === true ? 'success' : ($assessment === false ? 'danger' : 'secondary'));
            $result->setAttribute('status_label', $assessment === true ? 'PASS' : ($assessment === false ? 'FAIL' : 'NOT EVALUATED'));
        }

        return $testResults;
    }


    public function generateForItem(
    Request $request,
    \App\Models\CalibrationItem $item
) {
    $user = Auth::user();

    if (!$user || !$user->can('perform_calibrations')) {
        abort(403, 'You cannot generate certificates.');
    }

    try {
        $certificate = $this->certificateService
            ->generateFromCalibrationItem($item, $user->id);

        return redirect()
            ->route('certificates.show', $certificate)
            ->with(
                'success',
                'Certificate record created for equipment '
                . $item->serial_number
                . '. PDF generation and result evaluation are pending.'
            );

    } catch (\Illuminate\Validation\ValidationException $e) {
        return back()->withErrors($e->errors());

    } catch (\Throwable $e) {
        Log::error('Item certificate generation failed', [
            'calibration_item_id' => $item->id,
            'error' => $e->getMessage(),
        ]);

        return back()->with(
            'error',
            'Certificate generation failed. Check the application log.'
        );
    }
}
    /**
     * Get service types for certificate
     *
     * @param Certificate $certificate
     * @return \Illuminate\Support\Collection
     */
    private function getServiceTypes(Certificate $certificate)
    {
        $certificate->loadMissing(['calibration', 'calibrationItem']);
        $source = $certificate->calibrationItem ?? $certificate->calibration;
        $ids = $this->parseServiceTypeIds($source?->service_types);
        return empty($ids) ? collect() : KplcServiceType::whereIn('id', $ids)->get();
    }


    /**
     * Parse service type IDs from various formats
     *
     * @param mixed $serviceTypes
     * @return array
     */
    private function parseServiceTypeIds($serviceTypes)
    {
        if (is_array($serviceTypes)) {
            return $serviceTypes;
        }

        if (is_string($serviceTypes)) {
            $decoded = json_decode($serviceTypes, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Show certificate details
     *
     * @param Certificate $certificate
     * @return \Illuminate\View\View
     */
    // public function show(Certificate $certificate)
    // {
    //     // Load relationships
    //     $certificate->load([
    //         'calibration',
    //         'customer',
    //         'generatedBy',
    //         'checkedBy',
    //         'approvedBy',
    //         'issuedBy',
    //         'sentBy'
    //     ]);

    //     // Process test results
    //     $testResults = $this->processTestResults($certificate->calibration_id);

    //     // Get service types
    //     $serviceTypes = $this->getServiceTypes($certificate);

    //     // Calculate certificate statistics
    //     $stats = $this->calculateCertificateStats($testResults);

    //     return view('admin.certificates.show', compact(
    //         'certificate',
    //         'serviceTypes',
    //         'testResults',
    //         'stats'
    //     ));
    // }

    public function show(Certificate $certificate)
    {
        $certificate->load([
            'calibration',
            'calibration.customer',
            'calibration.equipment',
            'calibration.equipmentType',
            'calibrationItem',
            'calibrationItem.testResults',
            'customer',
            'generatedBy',
            'checkedBy',
            'approvedBy',
            'issuedBy',
            'sentBy',
        ]);

        $testResults = $this->processTestResults(
            $certificate->calibration_id,
            $certificate->calibration_item_id
        );

        $serviceTypes = $this->getServiceTypes($certificate);

        $stats = $this->calculateCertificateStats($testResults);

        return view('admin.certificates.show', [
            'certificate' => $certificate,
            'calibration' => $certificate->calibration,
            'customer' => $certificate->customer
                ?? $certificate->calibration?->customer,
            'testResults' => $testResults,
            'serviceTypes' => $serviceTypes,
            'stats' => $stats,
        ]);
    }

    /**
     * Calculate certificate statistics
     *
     * @param \Illuminate\Database\Eloquent\Collection $testResults
     * @return array
     */
    private function calculateCertificateStats($testResults)
    {
        $total = $testResults->count();
        $passed = $testResults->filter(fn ($r) => $r->status_label === 'PASS')->count();
        $failed = $testResults->filter(fn ($r) => $r->status_label === 'FAIL')->count();
        $notEvaluated = $total - $passed - $failed;
        return [
            'total_tests' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'not_evaluated' => $notEvaluated,
            'pass_rate' => $total ? round($passed / $total * 100, 2) : 0,
            'overall_result' => $failed ? 'Fail' : ($total && !$notEvaluated ? 'Pass' : 'Not Evaluated'),
        ];
    }


    /**
     * Check certificate (Supervisor)
     *
     * @param Request $request
     * @param Certificate $certificate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function check(Request $request, Certificate $certificate)
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('supervisor')) {
            return back()->with(
                'error',
                'Only supervisors can check certificates.'
            );
        }

        if ($certificate->status !== self::STATUS_GENERATED) {
            return back()->with(
                'error',
                'Certificate must be in generated status before checking.'
            );
        }

        if (
            $certificate->generated_by
            && (int) $certificate->generated_by === (int) $user->id
        ) {
            return back()->with(
                'error',
                'You cannot check a certificate that you generated.'
            );
        }

        $validated = $request->validate([
            'check_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'statement_conformity' => ['required', 'string', 'max:5000'],
            'traceability_information' => ['required', 'string', 'max:5000'],
            'conformity_review_confirmed' => ['required', 'accepted'],
            'traceability_review_confirmed' => ['required', 'accepted'],
        ]);

        DB::beginTransaction();

        try {
            $checkNotes = trim(
                (string) ($validated['check_notes'] ?? '')
            );

            $comments = $certificate->comments;

            if ($checkNotes !== '') {
                $comments = $comments
                    ? $comments . "\n\nCheck Notes: " . $checkNotes
                    : 'Check Notes: ' . $checkNotes;
            }

            $certificate->update([
                'status' => self::STATUS_CHECKED,
                'checked_by' => $user->id,
                'checked_at' => now(),
                'statement_conformity' => trim($validated['statement_conformity']),
                'traceability_information' => trim($validated['traceability_information']),
                'comments' => $comments,
            ]);

            /*
             * Regenerate before commit. If PDF creation fails, the workflow
             * change is rolled back and the stored certificate cannot become
             * inconsistent with the database.
             */
            $this->regenerateCertificatePdf($certificate);

            DB::commit();

            $this->notifyLabManagers($certificate);

            Log::info('Certificate checked successfully', [
                'certificate_id' => $certificate->id,
                'certificate_number' => $certificate->certificate_number,
                'checked_by' => $user->id,
            ]);

            return redirect()
                ->route('certificates.show', $certificate)
                ->with(
                    'success',
                    'Certificate checked successfully. The PDF has been updated and the certificate forwarded for approval.'
                );
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to check certificate', [
                'certificate_id' => $certificate->id,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to check certificate: ' . $exception->getMessage()
                );
        }
    }

    /**
     * Return a checked certificate with missing statements for supervisor review.
     * This action does not approve the certificate or invent statement text.
     */
    public function returnForStatementReview(Request $request, Certificate $certificate)
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('lab_manager')) {
            return back()->with('error', 'Only laboratory managers may return a checked certificate for review.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        DB::transaction(function () use ($certificate, $user, $validated) {
            $locked = Certificate::query()->lockForUpdate()->findOrFail($certificate->id);
            if ($locked->status !== self::STATUS_CHECKED) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'reason' => 'Only checked certificates may be returned for statement review.',
                ]);
            }
            $audit = sprintf(
                '[%s] Returned for statement review by %s (user #%d): %s',
                now()->format('Y-m-d H:i:s'),
                $user->name,
                $user->id,
                trim($validated['reason'])
            );
            $locked->update([
                'status' => self::STATUS_GENERATED,
                'checked_by' => null,
                'checked_at' => null,
                'comments' => trim(($locked->comments ? $locked->comments . "\n\n" : '') . $audit),
            ]);
        });

        Log::warning('Certificate returned for statement review', [
            'certificate_id' => $certificate->id,
            'returned_by' => $user->id,
            'reason' => $validated['reason'],
        ]);

        return redirect()->route('certificates.show', $certificate)
            ->with('success', 'Certificate returned to generated status for supervisor statement review.');
    }

    /**
 * Approve certificate.
 *
 * Lab Manager:
 *
 * checked -> approved
 *
 * After successful certificate approval:
 *
 * calibration completed -> certified
 */
public function approve(
    Request $request,
    Certificate $certificate
 ) {
    $user = Auth::user();


    /*
    |--------------------------------------------------------------------------
    | Lab Manager Only
    |--------------------------------------------------------------------------
    */

    if (
        !$user
        || !$user->hasRole('lab_manager')
    ) {

        return back()->with(
            'error',
            'Only laboratory managers can approve certificates.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Certificate Must First Be Checked
    |--------------------------------------------------------------------------
    */

    if (
        $certificate->status
        !== self::STATUS_CHECKED
    ) {

        return back()->with(
            'error',
            'The certificate must be checked before it can be approved.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Segregation of Duties - Checker != Approver
    |--------------------------------------------------------------------------
    */

    if (
        $certificate->checked_by

        && (int) $certificate->checked_by
            === (int) $user->id
    ) {

        return back()->with(
            'error',
            'You cannot approve a certificate that you checked.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Segregation of Duties - Generator != Approver
    |--------------------------------------------------------------------------
    */

    if (
        $certificate->generated_by

        && (int) $certificate->generated_by
            === (int) $user->id
    ) {

        return back()->with(
            'error',
            'You cannot approve a certificate that you generated.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Load Calibration
    |--------------------------------------------------------------------------
    */

    $certificate->loadMissing(
        'calibration'
    );


    $calibration =
        $certificate->calibration;


    if (!$calibration) {

        return back()->with(
            'error',
            'The calibration associated with this certificate could not be found.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Calibration Must Already Be Technically Completed
    |--------------------------------------------------------------------------
    */

    if (
        !in_array(
            $calibration->status,
            [
                Calibration::STATUS_COMPLETED,
                Calibration::STATUS_CERTIFIED,
            ],
            true
        )
    ) {

        return back()->with(
            'error',
            'The calibration must be completed before its certificate can be approved.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Environmental Conditions Must Exist
    |--------------------------------------------------------------------------
    */

  /*
|--------------------------------------------------------------------------
| Validate all approved equipment and attached reports
|--------------------------------------------------------------------------
*/
// Approval requires the supervisor-reviewed statements for this certificate.
// Test Machine Reports remain optional; missing reports do not block approval.
if (!filled($certificate->statement_conformity) || !filled($certificate->traceability_information)) {
    return back()->with('error', 'The supervisor must review and save both certificate statements before approval.');
}

if ($certificate->calibration_item_id !== null) {
    $item = \App\Models\CalibrationItem::with('testResults')->find($certificate->calibration_item_id);
    if (!$item || (int) $item->calibration_id !== (int) $calibration->id || $item->testResults->isEmpty()) {
        return back()->with('error', 'The certificate equipment and its test results could not be verified.');
    }
    $unevaluated = $item->testResults->contains(function ($test) {
        if ($test->is_within_tolerance !== null) {
            return false;
        }
        return !in_array(strtoupper(trim((string) $test->result)), ['PASS', 'FAIL'], true);
    });
    if ($unevaluated) {
        return back()->with('error', 'All equipment test results must be evaluated before certificate approval.');
    }
    // Do not use calibration-wide report eligibility for item-specific approval.
} else {
    try {
        app(CalibrationCertificateEligibility::class)->validate($calibration);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return back()->withErrors($e->errors());
    }
}

    // Validate Approval Notes
    $validated =
        $request->validate([

            'comments' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'approval_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

        ]);


    DB::beginTransaction();


    try {

        /*
        |--------------------------------------------------------------------------
        | Approval Notes
        |--------------------------------------------------------------------------
        */

        $approvalNotes =
            trim(
                (string) (
                    $validated['comments']
                    ?? $validated['approval_notes']
                    ?? ''
                )
            );


        $comments =
            $certificate->comments;


        if ($approvalNotes !== '') {

            $comments =
                $comments

                    ? $comments
                        . "\n\nApproval Notes: "
                        . $approvalNotes

                    : 'Approval Notes: '
                        . $approvalNotes;
        }


        /*
        |--------------------------------------------------------------------------
        | Approve Certificate
        |--------------------------------------------------------------------------
        */

        $certificate->update([

            'status' =>
                self::STATUS_APPROVED,

            'approved_by' =>
                $user->id,

            'approved_at' =>
                now(),

            'issued_date' => $certificate->issued_date,

            'expiry_date' => $certificate->expiry_date,

            'is_signed' => $certificate->is_signed,

            /*
             * signed_by is VARCHAR.
             */
            'signed_by' => $certificate->signed_by,

            'signed_at' => $certificate->signed_at,

            'comments' =>
                $comments,

        ]);


        /*
        |--------------------------------------------------------------------------
        | Synchronize Calibration
        |--------------------------------------------------------------------------
        |
        | Technical work was completed by performed_by.
        |
        | Certificate approval now changes the calibration from:
        |
        | completed -> certified
        |
        */

        if (
            $calibration->status
            !== Calibration::STATUS_CERTIFIED
        ) {

            $calibration->update([

                'status' =>
                    Calibration::STATUS_CERTIFIED,

                /*
                 * Do not overwrite technician completion time.
                 */
                'completed_at' =>
                    $calibration->completed_at
                    ?? now(),

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Rebuild Approved PDF
        |--------------------------------------------------------------------------
        */

        $this->regenerateCertificatePdf(
            $certificate
        );


        DB::commit();


        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        Log::info(
            'Certificate approved successfully',
            [

                'certificate_id' =>
                    $certificate->id,

                'certificate_number' =>
                    $certificate
                        ->certificate_number,

                'calibration_id' =>
                    $calibration->id,

                'calibration_status' =>
                    $calibration->status,

                'approved_by' =>
                    $user->id,

                'checked_by' =>
                    $certificate
                        ->checked_by,

                'generated_by' =>
                    $certificate
                        ->generated_by,

            ]
        );


        return redirect()
            ->route(
                'certificates.show',
                $certificate
            )
            ->with(
                'success',
                'Certificate approved successfully. The calibration is now certified.'
            );


    } catch (\Throwable $exception) {

        DB::rollBack();


        Log::error(
            'Failed to approve certificate',
            [

                'certificate_id' =>
                    $certificate->id,

                'calibration_id' =>
                    $calibration->id,

                'user_id' =>
                    $user->id,

                'error' =>
                    $exception->getMessage(),

                'trace' =>
                    $exception
                        ->getTraceAsString(),

            ]
        );


        return back()
            ->withInput()
            ->with(
                'error',
                'Failed to approve certificate: '
                . $exception->getMessage()
            );
    }
}

/**
 * Email an approved certificate to the customer.
 */
    protected function sendApprovedCertificateToCustomer(
        Certificate $certificate
    ): void {
        $certificate->refresh();

        $certificate->load([
            'customer',
            'calibration.customer',
            'generatedBy',
            'checkedBy',
            'approvedBy',
            'issuedBy',
            'sentBy',
        ]);

        if ($certificate->status !== self::STATUS_APPROVED) {
            throw new \RuntimeException(
                'Only approved certificates can be sent to customers.'
            );
        }

        $customerEmail = trim(
            (string) (
                $certificate->customer?->email
                ?? $certificate->calibration?->customer?->email
                ?? ''
            )
        );

        if ($customerEmail === '') {
            throw new \RuntimeException(
                'The customer does not have an email address.'
            );
        }

        /*
         * Rebuild immediately before email so the attachment is guaranteed to
         * contain the latest checker and approver details.
         */
        $this->regenerateCertificatePdf($certificate);

        $certificate->refresh();

        if (!$certificate->pdf_path) {
            throw new \RuntimeException(
                'The certificate PDF path is missing.'
            );
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($certificate->pdf_path)) {
            throw new \RuntimeException(
                'The certificate PDF file could not be found.'
            );
        }

        $absolutePdfPath = $disk->path(
            $certificate->pdf_path
        );

        Mail::to($customerEmail)->send(
            new CalibrationCertificateMail(
                $certificate,
                $absolutePdfPath
            )
        );

        $certificate->update([
            'status' => self::STATUS_SENT,
            'sent_by' => Auth::id(),
            'sent_at' => now(),
        ]);
    }
    /**
     * Send certificate to customer
     *
     * @param Request $request
     * @param Certificate $certificate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request, Certificate $certificate)
    {
        $user = Auth::user();

        if (
            !$user
            || (
                !$user->hasRole('super_admin')
                && !$user->hasRole('lab_manager')
                && !$user->hasRole('supervisor')
            )
        ) {
            return back()->with(
                'error',
                'You are not authorized to send certificates.'
            );
        }

        if ($certificate->status !== self::STATUS_APPROVED) {
            return back()->with(
                'error',
                'Certificate must be approved before sending.'
            );
        }

        try {
            $this->sendApprovedCertificateToCustomer($certificate);

            Log::info('Certificate sent to customer successfully', [
                'certificate_id' => $certificate->id,
                'certificate_number' => $certificate->certificate_number,
                'sent_by' => $user->id,
            ]);

            return redirect()
                ->route('certificates.show', $certificate)
                ->with(
                    'success',
                    'Certificate sent to customer successfully.'
                );
        } catch (\Throwable $exception) {
            Log::error('Failed to send certificate', [
                'certificate_id' => $certificate->id,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()->with(
                'error',
                config('app.debug')
                    ? 'Failed to send certificate: '
                        . $exception->getMessage()
                    : 'The certificate could not be sent.'
            );
        }
    }

    /**
     * Preview certificate PDF
     *
     * @param Certificate $certificate
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function preview(Certificate $certificate)
    {
        try {
            $certificate->refresh();

            $certificate->load([
                'calibration',
                'calibration.customer',
                'calibration.equipment',
                'calibration.equipmentType',
                'customer',
                'generatedBy',
                'checkedBy',
                'approvedBy',
                'issuedBy',
                'sentBy',
            ]);

            $pdf = $this->generateCertificatePdf($certificate);

            $cleanCertNumber = $this->cleanCertificateNumber(
                $certificate->certificate_number
            );

            Log::info('Certificate preview generated', [
                'certificate_id' => $certificate->id,
                'certificate_number' => $certificate->certificate_number,
                'user_id' => Auth::id(),
            ]);

            return $pdf->stream(
                'certificate-' . $cleanCertNumber . '.pdf',
                [
                    'Attachment' => false,
                ]
            );
        } catch (\Throwable $exception) {
            Log::error('Failed to preview certificate', [
                'certificate_id' => $certificate->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()->with(
                'error',
                'Failed to preview certificate: '
                    . $exception->getMessage()
            );
        }
    }

   /**
 * Download certificate PDF
 *
 * @param Certificate $certificate
 * @param Request $request
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
 */
public function download(Request $request, Certificate $certificate)
{
    if (!auth()->user()->can('view_certificates')) {
        abort(403, 'Unauthorized to download this certificate.');
    }

    if (
        !in_array(
            $certificate->status,
            self::ALLOWED_DOWNLOAD_STATUSES,
            true
        )
        && !auth()->user()->hasRole('super_admin')
    ) {
        return back()->with(
            'error',
            'Certificate must be approved before download.'
        );
    }

    try {
        /*
         * Load all relationships before deciding whether the existing
         * PDF is still current.
         */
        $certificate->load([
            'calibration',
            'customer',
            'generatedBy',
            'checkedBy',
            'approvedBy',
            'issuedBy',
            'sentBy',
        ]);

        $forceRegenerate =
            $request->boolean('force');

        /*
         * Regenerate when any signatory has updated a signature after
         * the saved PDF was last generated.
         */
        $signatureUsers = collect([
            $certificate->generatedBy,
            $certificate->checkedBy,
            $certificate->approvedBy,
            $certificate->issuedBy,
        ])->filter();

        $latestSignatureUpdate = $signatureUsers
            ->pluck('signature_updated_at')
            ->filter()
            ->map(function ($date) {
                return \Carbon\Carbon::parse($date);
            })
            ->sortDesc()
            ->first();

        $existingPath = null;
        $existingPdfModifiedAt = null;

        if ($certificate->pdf_path) {
            $existingPath = storage_path(
                'app/public/' . ltrim(
                    $certificate->pdf_path,
                    '/'
                )
            );

            if (file_exists($existingPath)) {
                $existingPdfModifiedAt =
                    \Carbon\Carbon::createFromTimestamp(
                        filemtime($existingPath)
                    );
            }
        }

        $signatureChangedAfterPdf =
            $latestSignatureUpdate
            && (
                !$existingPdfModifiedAt
                || $latestSignatureUpdate->greaterThan(
                    $existingPdfModifiedAt
                )
            );

        /*
         * Use the existing PDF only when:
         * - regeneration was not explicitly requested;
         * - the file exists; and
         * - no signature has changed since it was generated.
         */
        if (
            !$forceRegenerate
            && !$signatureChangedAfterPdf
            && $existingPath
            && file_exists($existingPath)
        ) {
            $downloadFilename =
                $this->certificateDownloadFilename(
                    $certificate
                );

            Log::info(
                'Certificate downloaded using existing PDF',
                [
                    'certificate_id' => $certificate->id,
                    'user_id' => auth()->id(),
                ]
            );

            return response()->download(
                $existingPath,
                $downloadFilename
            );
        }

        $pdf = $this->generateCertificatePdf($certificate);
        $testResults = $this->processTestResults($certificate->calibration_id, $certificate->calibration_item_id);

        $cleanCertNumber =
            $this->cleanCertificateNumber(
                $certificate->certificate_number
            );

        $downloadFilename =
            'certificate-' . $cleanCertNumber . '.pdf';

        $pdfPath =
            'certificates/certificate-'
            . $cleanCertNumber
            . '.pdf';

        $fullPath = storage_path(
            'app/public/' . $pdfPath
        );

        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            if (
                !mkdir(
                    $directory,
                    0755,
                    true
                )
                && !is_dir($directory)
            ) {
                throw new \RuntimeException(
                    'Unable to create certificate directory.'
                );
            }
        }

        $pdfOutput = $pdf->output();

        if (
            file_put_contents(
                $fullPath,
                $pdfOutput
            ) === false
        ) {
            throw new \RuntimeException(
                'Unable to save the generated certificate PDF.'
            );
        }

        $certificate->update([
            'pdf_path' => $pdfPath,
        ]);

        Log::info(
            'Certificate PDF regenerated and downloaded',
            [
                'certificate_id' =>
                    $certificate->id,

                'certificate_number' =>
                    $certificate->certificate_number,

                'test_results_count' =>
                    $testResults->count(),

                'signature_changed_after_pdf' =>
                    (bool) $signatureChangedAfterPdf,

                'forced_regeneration' =>
                    $forceRegenerate,

                'user_id' =>
                    auth()->id(),
            ]
        );

        return response(
            $pdfOutput,
            200,
            [
                'Content-Type' =>
                    'application/pdf',

                'Content-Disposition' =>
                    'attachment; filename="'
                    . $downloadFilename
                    . '"',
            ]
        );
    } catch (\Throwable $e) {
        Log::error(
            'Failed to download certificate',
            [
                'certificate_id' =>
                    $certificate->id,

                'error' =>
                    $e->getMessage(),

                'file' =>
                    $e->getFile(),

                'line' =>
                    $e->getLine(),

                'trace' =>
                    $e->getTraceAsString(),
            ]
        );

        return back()->with(
            'error',
            'Failed to download certificate: '
            . $e->getMessage()
        );
    }
}

    /**
     * Show upload test report form
     *
     * @param Certificate $certificate
     * @return \Illuminate\View\View
     */
    // public function uploadTestReport(Certificate $certificate)
    // {
    //     return view('admin.certificates.upload-test-report', compact('certificate'));
    // }

    /**
     * Store test report attachment
     *
     * @param Request $request
     * @param Certificate $certificate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeTestReport(
    Request $request,
    Certificate $certificate
) {
    $user = auth()->user();

    abort_unless(
        $user && $user->can('perform_calibrations'),
        403
    );

    $validated = $request->validate([
        'calibration_item_id' => [
            'required',
            'integer',
            'exists:calibration_items,id',
        ],
        'equipment_id' => [
            'required',
            'integer',
            'exists:equipment,id',
        ],
        'test_report' => [
            'required',
            'file',
            'mimes:pdf',
            'max:10240',
        ],
        'test_engineer' => [
            'nullable',
            'string',
            'max:255',
        ],
        'test_date' => [
            'nullable',
            'date',
            'before_or_equal:today',
        ],
    ]);

    $certificate->load('calibration');

    $calibration = $certificate->calibration;

    abort_unless($calibration, 404);

    abort_unless(
        (int) $calibration->performed_by === (int) $user->id,
        403,
        'Only the assigned technician can upload this report.'
    );

    if ($calibration->status !== Calibration::STATUS_TESTING) {
        return back()->withErrors([
            'test_report' =>
                'The calibration must be in testing status.',
        ])->withInput();
    }

    $item = \App\Models\CalibrationItem::query()
        ->where('calibration_id', $calibration->id)
        ->findOrFail($validated['calibration_item_id']);

    if ($item->review_status !== 'testing') {
        return back()->withErrors([
            'calibration_item_id' =>
                'This equipment is not open for testing.',
        ])->withInput();
    }

    $machine = Equipment::query()
        ->whereKey($validated['equipment_id'])
        ->where('status', 'active')
        ->first();

    if (!$machine) {
        return back()->withErrors([
            'equipment_id' =>
                'Select an active laboratory testing machine.',
        ])->withInput();
    }

    $file = $request->file('test_report');

    $directory = sprintf(
        'calibrations/%d/items/%d/test-reports',
        $calibration->id,
        $item->id
    );

    $path = $file->store(
        $directory,
        'local'
    );

    if (!$path) {
        return back()->with(
            'error',
            'Unable to store the test report.'
        );
    }

    try {
        DB::transaction(function () use (
            $item,
            $machine,
            $user,
            $file,
            $path
        ) {
            $lockedItem = \App\Models\CalibrationItem::query()
                ->lockForUpdate()
                ->findOrFail($item->id);

            if ($lockedItem->review_status !== 'testing') {
                throw \Illuminate\Validation\ValidationException
                    ::withMessages([
                        'calibration_item_id' =>
                            'This equipment is no longer open for testing.',
                    ]);
            }

            $lockedItem->update([
                'laboratory_equipment_id' => $machine->id,
                'test_report_path' => $path,
                'test_report_original_name' =>
                    $file->getClientOriginalName(),
                'test_report_uploaded_by' => $user->id,
                'test_report_uploaded_at' => now(),
            ]);
        });
    } catch (\Throwable $e) {
        Storage::disk('local')->delete($path);

        throw $e;
    }

    Log::info('Equipment test report uploaded', [
        'certificate_id' => $certificate->id,
        'calibration_id' => $calibration->id,
        'calibration_item_id' => $item->id,
        'laboratory_equipment_id' => $machine->id,
        'uploaded_by' => $user->id,
    ]);

    return redirect()
        ->route('certificates.show', $certificate)
        ->with(
            'success',
            'Test Machine Report uploaded successfully for '
            . $item->equipment_name . '.'
        );
}

    /**
     * Download test report attachment
     *
     * @param Certificate $certificate
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function downloadTestReport(Certificate $certificate)
    {
        abort_unless(auth()->user()?->can('view_certificates'), 403);
        $certificate->loadMissing('calibrationItem');
        $item = $certificate->calibrationItem;
        $diskName = $item ? 'local' : 'public';
        $path = $item?->test_report_path ?? $certificate->test_report_attachment_path;
        if (!$path || !Storage::disk($diskName)->exists($path)) {
            return back()->with('error', 'The supporting test report was not found.');
        }
        $filename = $item?->test_report_original_name
            ?? $certificate->test_report_original_name
            ?? ('test-report-'.$certificate->id.'.pdf');
        return Storage::disk($diskName)->download($path, basename($filename));
    }

    /**
     * Download certificate with test report combined
     *
     * @param Certificate $certificate
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

public function downloadCombined(Certificate $certificate)
{
    if (!auth()->user()->can('view_certificates')) {
        abort(
            403,
            'Unauthorized to download this certificate.'
        );
    }

    try {
        $certificate->load([
            'calibration',
            'calibration.equipment',
            'customer',
            'generatedBy',
            'checkedBy',
            'approvedBy',
            'issuedBy',
            'sentBy',
        ]);

        $certificate->loadMissing('calibrationItem');
        $item = $certificate->calibrationItem;
        $reportDisk = $item ? 'local' : 'public';
        $reportRelativePath = $item?->test_report_path ?? $certificate->test_report_attachment_path;
        // Supporting reports are optional. Without one, return the normal certificate PDF.
        if (!$reportRelativePath || !Storage::disk($reportDisk)->exists($reportRelativePath)) {
            return $this->generateCertificatePdf($certificate)->download(
                'certificate-'.preg_replace('/[^A-Za-z0-9_-]/', '_', (string) ($certificate->certificate_number ?: $certificate->id)).'.pdf'
            );
        }
        $testReportPath = Storage::disk($reportDisk)->path($reportRelativePath);
        if (strtolower(pathinfo($testReportPath, PATHINFO_EXTENSION)) !== 'pdf') {
            return back()->with('error', 'The attached test report must be a PDF.');
        }
        $certificatePdf = $this->generateCertificatePdf($certificate);

        $cleanCertNumber = preg_replace(
            '/[^A-Za-z0-9\-_]/',
            '_',
            $certificate->certificate_number
                ?: 'certificate-' . $certificate->id
        );

        $directory = storage_path(
            'app/public/certificates'
        );

        if (
            !is_dir($directory)
            && !mkdir($directory, 0755, true)
            && !is_dir($directory)
        ) {
            throw new \RuntimeException(
                'Unable to create the certificates directory.'
            );
        }

        /*
         * Use unique temporary filenames to avoid collisions between
         * simultaneous downloads.
         */
        $uniqueSuffix = now()->format('YmdHis')
            . '-'
            . bin2hex(random_bytes(4));

        $temporaryCertificatePath =
            $directory
            . '/temp-certificate-'
            . $cleanCertNumber
            . '-'
            . $uniqueSuffix
            . '.pdf';

        $combinedPath =
            $directory
            . '/combined-certificate-'
            . $cleanCertNumber
            . '-'
            . $uniqueSuffix
            . '.pdf';

        $certificateOutput = $certificatePdf->output();

        if (
            file_put_contents(
                $temporaryCertificatePath,
                $certificateOutput
            ) === false
        ) {
            throw new \RuntimeException(
                'Unable to create the temporary certificate PDF.'
            );
        }

        /*
         * Merge the generated certificate first, followed by every
         * page of the uploaded test report.
         */
        $merger = new Fpdi();

        $certificatePageCount = 0;
        $testReportPageCount = 0;

        $sourceFiles = [
            [
                'path' => $temporaryCertificatePath,
                'type' => 'certificate',
            ],
            [
                'path' => $testReportPath,
                'type' => 'test_report',
            ],
        ];

        foreach ($sourceFiles as $sourceFile) {
            $sourcePath = $sourceFile['path'];

            if (!is_file($sourcePath)) {
                throw new \RuntimeException(
                    'PDF source file was not found: '
                    . basename($sourcePath)
                );
            }

            $pageCount = $merger->setSourceFile(
                $sourcePath
            );

            if ($sourceFile['type'] === 'certificate') {
                $certificatePageCount = $pageCount;
            } else {
                $testReportPageCount = $pageCount;
            }

            for (
                $pageNumber = 1;
                $pageNumber <= $pageCount;
                $pageNumber++
            ) {
                $templateId = $merger->importPage(
                    $pageNumber
                );

                $size = $merger->getTemplateSize(
                    $templateId
                );

                $orientation =
                    $size['width'] > $size['height']
                        ? 'L'
                        : 'P';

                $merger->AddPage(
                    $orientation,
                    [
                        $size['width'],
                        $size['height'],
                    ]
                );

                $merger->useTemplate(
                    $templateId,
                    0,
                    0,
                    $size['width'],
                    $size['height'],
                    true
                );
            }
        }

        if ($testReportPageCount < 1) {
            throw new \RuntimeException(
                'No pages were found in the attached test report.'
            );
        }

        /*
         * Correct FPDI argument order:
         * destination first, filename/path second.
         */
        $merger->Output(
            'F',
            $combinedPath
        );

        if (
            !is_file($combinedPath)
            || filesize($combinedPath) === 0
        ) {
            throw new \RuntimeException(
                'The combined PDF was not created successfully.'
            );
        }

        Log::info(
            'Certificate and test report merged successfully',
            [
                'certificate_id' =>
                    $certificate->id,

                'certificate_number' =>
                    $certificate->certificate_number,

                'certificate_pages' =>
                    $certificatePageCount,

                'test_report_pages' =>
                    $testReportPageCount,

                'combined_pages' =>
                    $certificatePageCount
                    + $testReportPageCount,

                'combined_path' =>
                    $combinedPath,

                'combined_size' =>
                    filesize($combinedPath),

                'user_id' =>
                    auth()->id(),
            ]
        );

        $downloadFilename =
            'complete-certificate-'
            . $cleanCertNumber
            . '.pdf';

        /*
         * Delete the generated combined file after it has been sent.
         */
        return response()
            ->download(
                $combinedPath,
                $downloadFilename,
                [
                    'Content-Type' => 'application/pdf',
                ]
            )
            ->deleteFileAfterSend(true);
    } catch (\Throwable $e) {
        Log::error(
            'Failed to combine certificate and test report',
            [
                'certificate_id' =>
                    $certificate->id,

                'error' =>
                    $e->getMessage(),

                'file' =>
                    $e->getFile(),

                'line' =>
                    $e->getLine(),

                'trace' =>
                    $e->getTraceAsString(),
            ]
        );

        return back()->with(
            'error',
            'Failed to combine certificate and test report: '
            . $e->getMessage()
        );
    } finally {
        /*
         * Remove the temporary certificate file.
         */
        if (
            isset($temporaryCertificatePath)
            && is_file($temporaryCertificatePath)
        ) {
            @unlink($temporaryCertificatePath);
        }
    }
}

    /**
     * Regenerate certificate PDF
     *
     * @param Certificate $certificate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerate(Certificate $certificate)
    {
        $user = Auth::user();

        if (!$user || !$user->can('view_certificates')) {
            abort(403, 'Unauthorized to regenerate this certificate.');
        }

        try {
            $this->regenerateCertificatePdf($certificate);

            return redirect()
                ->route('certificates.show', $certificate)
                ->with(
                    'success',
                    'Certificate PDF regenerated successfully using the latest workflow details.'
                );
        } catch (\Throwable $exception) {
            Log::error('Failed to regenerate certificate PDF', [
                'certificate_id' => $certificate->id,
                'certificate_number' => $certificate->certificate_number,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()->with(
                'error',
                'Failed to regenerate PDF: '
                    . $exception->getMessage()
            );
        }
    }

    /**
     * Show bulk upload form
     *
     * @return \Illuminate\View\View
     */
    public function bulkUpload()
    {
        $customers = Customer::where('is_active', 1)->pluck('company_name', 'id');
        $equipmentTypes = KplcEquipmentType::orderBy('name')->get();
        $serviceTypes = KplcServiceType::where('status', 1)->get();

        return view('calibrations.bulk-upload', compact('customers', 'equipmentTypes', 'serviceTypes'));
    }

    /**
     * Process bulk upload with batch support
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processBulkUpload(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'csv_file' => 'required|file|mimes:csv,txt|max:20480',
            'batch_size' => 'nullable|integer|min:100|max:10000',
            'service_type_ids' => 'nullable|array',
            'service_type_ids.*' => 'exists:kplc_service_types,id',
            'equipment_type_id' => 'nullable|exists:kplc_equipment_types,id',
        ]);

        $batchSize = $request->batch_size ?? 1000;
        $customerId = $request->customer_id;
        $serviceTypeIds = $request->service_type_ids ?? [];
        $defaultEquipmentTypeId = $request->equipment_type_id;

        // Parse CSV file
        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getPathname()));

        if (empty($csvData) || count($csvData) < 2) {
            return back()->with('error', 'CSV file is empty or invalid.');
        }

        // Get headers
        $headers = array_map('trim', $csvData[0]);
        $requiredHeaders = ['equipment_name', 'serial_number'];

        // Validate headers
        $headerLower = array_map('strtolower', $headers);
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $headerLower)) {
                return back()->with('error', "Missing required column: {$required}. Found: " . implode(', ', $headers));
            }
        }

        // Map header positions
        $headerMap = [];
        foreach ($headers as $index => $header) {
            $headerMap[strtolower(trim($header))] = $index;
        }

        // Process data in batches
        $totalRows = count($csvData) - 1;
        $processed = 0;
        $errors = [];
        $successCount = 0;
        $calibrationIds = [];
        $batchNumber = 1;

        DB::beginTransaction();

        try {
            // Create a batch ID for tracking
            $batchId = 'BATCH-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Process rows in batches
            for ($i = 1; $i < count($csvData); $i += $batchSize) {
                $batchRows = array_slice($csvData, $i, $batchSize);
                $batchData = [];

                foreach ($batchRows as $rowIndex => $row) {
                    $rowData = [];
                    foreach ($headerMap as $header => $position) {
                        $rowData[$header] = isset($row[$position]) ? trim($row[$position]) : '';
                    }

                    // Skip empty rows
                    if (empty($rowData['equipment_name']) && empty($rowData['serial_number'])) {
                        continue;
                    }

                    // Validate required fields
                    if (empty($rowData['equipment_name'])) {
                        $errors[] = "Row " . ($i + $rowIndex + 1) . ": Equipment Name is required.";
                        continue;
                    }

                    if (empty($rowData['serial_number'])) {
                        $errors[] = "Row " . ($i + $rowIndex + 1) . ": Serial Number is required.";
                        continue;
                    }

                    $batchData[] = $rowData;
                }

                if (empty($batchData)) {
                    continue;
                }

                // Process each batch
                $result = $this->processBatch($batchData, $customerId, $serviceTypeIds, $defaultEquipmentTypeId, $batchId, $batchNumber);

                $successCount += $result['success'];
                $calibrationIds = array_merge($calibrationIds, $result['calibration_ids']);
                $errors = array_merge($errors, $result['errors']);
                $processed += count($batchData);
                $batchNumber++;

                // Log batch progress
                Log::info("Batch processed", [
                    'batch_id' => $batchId,
                    'batch_number' => $batchNumber,
                    'processed' => $processed,
                    'total' => $totalRows,
                    'success' => $result['success']
                ]);
            }

            DB::commit();

            // Summary message
            $message = "Successfully created {$successCount} calibrations from {$processed} equipment items.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " errors encountered.";
                session()->flash('import_errors', array_slice($errors, 0, 50));
            }

            if ($successCount > 0) {
                // Generate job card for the batch
                $this->generateBatchJobCards($calibrationIds, $customerId);

                return redirect()->route('calibrations.bulk-upload.summary', ['batch_id' => $batchId])
                    ->with('success', $message)
                    ->with('calibration_ids', $calibrationIds)
                    ->with('batch_id', $batchId);
            }

            return back()->with('warning', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk upload failed', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Bulk upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Process a single batch of equipment
     *
     * @param array $batchData
     * @param int $customerId
     * @param array $serviceTypeIds
     * @param int|null $defaultEquipmentTypeId
     * @param string $batchId
     * @param int $batchNumber
     * @return array
     */
    private function processBatch($batchData, $customerId, $serviceTypeIds, $defaultEquipmentTypeId, $batchId, $batchNumber)
    {
        $success = 0;
        $errors = [];
        $calibrationIds = [];

        foreach ($batchData as $index => $row) {
            try {
                // Determine equipment type
                $equipmentTypeId = $defaultEquipmentTypeId;

                // Try to find equipment type by name if provided
                if (!empty($row['equipment_type'])) {
                    $type = KplcEquipmentType::where('name', 'LIKE', '%' . $row['equipment_type'] . '%')->first();
                    if ($type) {
                        $equipmentTypeId = $type->id;
                    }
                }

                // Determine equipment type from name if not set
                if (!$equipmentTypeId) {
                    $equipmentTypeId = $this->detectEquipmentType($row['equipment_name']);
                }

                // Get service types
                $serviceTypeIdsToUse = $serviceTypeIds;
                if (!empty($row['service_type'])) {
                    $service = KplcServiceType::where('serv_code', $row['service_type'])->first();
                    if ($service) {
                        $serviceTypeIdsToUse = [$service->id];
                    }
                }

                // Create calibration
                $calibration = Calibration::create([
                    'customer_id' => $customerId,
                    'equipment_name' => $row['equipment_name'],
                    'serial_number' => $row['serial_number'],
                    'manufacturer' => $row['manufacturer'] ?? null,
                    'model' => $row['model'] ?? null,
                    'accuracy_class' => $row['accuracy_class'] ?? null,
                    'ratings' => $row['rating'] ?? null,
                    'equipment_type_id' => $equipmentTypeId,
                    'equipment_condition' => $row['equipment_condition'] ?? 'Good',
                    'last_calibration_date' => !empty($row['last_calibration_date']) ? $row['last_calibration_date'] : null,
                    'notes' => $row['notes'] ?? null,
                    'status' => 'pending',
                    'is_external' => true,
                    'requires_payment' => true,
                    'calibration_location' => 'laboratory',
                    'priority' => 'normal',
                    'service_types' => json_encode($serviceTypeIdsToUse),
                    'batch_id' => $batchId,
                    'batch_number' => $batchNumber,
                    'created_by' => auth()->id(),
                ]);

                $calibrationIds[] = $calibration->id;
                $success++;

            } catch (\Exception $e) {
                $rowNumber = $index + 1;
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();

                Log::error("Bulk upload row failed", [
                    'row_number' => $rowNumber,
                    'error' => $e->getMessage(),
                    'row_data' => $row
                ]);
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'calibration_ids' => $calibrationIds,
        ];
    }

    /**
     * Auto-detect equipment type from name
     *
     * @param string $equipmentName
     * @return int|null
     */
    private function detectEquipmentType($equipmentName)
    {
        $name = strtolower($equipmentName);

        $typeMap = [
            'single phase' => '1 Phase watt hour meter',
            '3 phase' => '3 Phase Whole current watt hour Meter',
            'ct&pt' => '3 Phase CT&PT Connected watt hour Meter',
            'voltmeter' => 'Voltmeters',
            'ammeter' => 'Ammeters',
            'multimeter' => 'Multimeters',
            'megger' => 'Resistance(Meggers) Meters',
            'ct analyzer' => 'CT Analyzer',
            'current transformer' => 'Current Transformer',
            'frequency' => 'Frequency Meters',
            'clamp' => 'Clamp on Meters',
            'resistance' => 'Resistance boxes',
            'panel' => 'Panel Meter',
        ];

        foreach ($typeMap as $keyword => $typeName) {
            if (strpos($name, $keyword) !== false) {
                $type = KplcEquipmentType::where('name', $typeName)->first();
                if ($type) {
                    return $type->id;
                }
            }
        }

        // Default to Single Phase Meter if not detected
        $default = KplcEquipmentType::where('name', '1 Phase watt hour meter')->first();
        return $default ? $default->id : null;
    }

    /**
     * Generate job cards for batch calibrations
     *
     * @param array $calibrationIds
     * @param int $customerId
     * @return void
     */
    private function generateBatchJobCards($calibrationIds, $customerId)
    {
        foreach ($calibrationIds as $calibrationId) {
            try {
                $calibration = Calibration::find($calibrationId);
                if (!$calibration) {
                    continue;
                }

                // Check if job card already exists
                $jobCard = JobCard::where('calibration_id', $calibrationId)->first();
                if ($jobCard) {
                    continue;
                }

                // Generate job card number
                $jobCardNumber = JobCard::generateJobCardNumber();

                JobCard::create([
                    'customer_id' => $customerId,
                    'calibration_id' => $calibrationId,
                    'job_card_number' => $jobCardNumber,
                    'job_number' => $jobCardNumber,
                    'status' => 'pending',
                    'description' => 'Bulk calibration for ' . ($calibration->equipment_name ?? 'Equipment'),
                    'created_by' => auth()->id(),
                ]);

            } catch (\Exception $e) {
                Log::error("Failed to create job card for calibration", [
                    'calibration_id' => $calibrationId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Show bulk upload summary
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function bulkUploadSummary(Request $request)
    {
        $batchId = $request->batch_id;

        $calibrations = Calibration::where('batch_id', $batchId)
            ->with(['customer', 'equipmentType'])
            ->get();

        $total = $calibrations->count();
        $pending = $calibrations->where('status', 'pending')->count();
        $quoted = $calibrations->where('status', 'quoted')->count();
        $paid = $calibrations->where('status', 'paid')->count();
        $assigned = $calibrations->where('status', 'assigned')->count();
        $completed = $calibrations->where('status', 'completed')->count();

        $customer = $calibrations->first()?->customer;

        return view('calibrations.bulk-summary', compact(
            'calibrations',
            'batchId',
            'total',
            'pending',
            'quoted',
            'paid',
            'assigned',
            'completed',
            'customer'
        ));
    }

    /**
     * Process bulk payment for all calibrations in a batch
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkProcessPayment(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
            'payment_method' => 'required|string',
            'payment_reference' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $calibrations = Calibration::where('batch_id', $request->batch_id)
            ->where('status', 'quoted')
            ->get();

        if ($calibrations->isEmpty()) {
            return back()->with('error', 'No quoted calibrations found in this batch.');
        }

        DB::beginTransaction();

        try {
            $amountPerCalibration = $request->amount / $calibrations->count();

            foreach ($calibrations as $calibration) {
                $calibration->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'amount_paid' => $amountPerCalibration,
                    'amount_due' => $amountPerCalibration,
                    'payment_method' => $request->payment_method,
                    'payment_reference' => $request->payment_reference,
                    'payment_received_at' => now(),
                ]);

                // Update associated job card
                $jobCard = JobCard::where('calibration_id', $calibration->id)->first();
                if ($jobCard) {
                    $jobCard->update(['status' => 'pending']);
                }
            }

            DB::commit();

            Log::info('Bulk payment processed', [
                'batch_id' => $request->batch_id,
                'count' => $calibrations->count(),
                'amount' => $request->amount,
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'Bulk payment processed successfully for ' . $calibrations->count() . ' calibrations.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk payment failed', [
                'batch_id' => $request->batch_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Bulk payment failed: ' . $e->getMessage());
        }
    }

    private function generateCertificate(
    Calibration $calibration
) {
    try {

        /*
        |--------------------------------------------------------------------------
        | Existing certificate
        |--------------------------------------------------------------------------
        */

        if ($calibration->certificate) {

            return $calibration->certificate;
        }


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $calibration->status,
                [
                    Calibration::STATUS_COMPLETED,
                    Calibration::STATUS_CERTIFIED,
                ],
                true
            )
        ) {

            throw new \RuntimeException(
                'Calibration must be completed before generating a certificate.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Environmental conditions
        |--------------------------------------------------------------------------
        */

        /*
|--------------------------------------------------------------------------
| Validate all approved equipment and attached reports
|--------------------------------------------------------------------------
*/
try {
    app(CalibrationCertificateEligibility::class)
        ->validate($calibration);

} catch (\Illuminate\Validation\ValidationException $e) {
    return back()->withErrors($e->errors());
}


        /*
        |--------------------------------------------------------------------------
        | Test results
        |--------------------------------------------------------------------------
        */

        /*
|--------------------------------------------------------------------------
| Validate all approved equipment and attached reports
|--------------------------------------------------------------------------
*/
try {
    app(CalibrationCertificateEligibility::class)
        ->validate($calibration);

} catch (\Illuminate\Validation\ValidationException $e) {
    return back()->withErrors($e->errors());
}

        /*
        |--------------------------------------------------------------------------
        | Generate certificate
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $this->certificateService
            )
        ) {

            $certificate =
                $this->certificateService
                    ->generateFromCalibration(
                        $calibration
                    );

            $this->certificateService
                ->generatePDF(
                    $certificate
                );

        } else {

            $certificate =
                Certificate::create([

                    'calibration_id' =>
                        $calibration->id,

                    'customer_id' =>
                        $calibration
                            ->customer_id,

                    'certificate_number' =>
                        'CERT-'
                        . date('Ymd')
                        . '-'
                        . str_pad(
                            $calibration->id,
                            4,
                            '0',
                            STR_PAD_LEFT
                        ),

                    'status' =>
                        'generated',

                    'generated_by' =>
                        auth()->id(),

                    'generated_at' =>
                        now(),

                ]);
        }


        return $certificate;


    } catch (\Throwable $e) {

        Log::error(
            'Certificate generation failed',
            [

                'calibration_id' =>
                    $calibration->id,

                'error' =>
                    $e->getMessage(),

            ]
        );


        return null;
    }
}

    /**
     * Bulk assign technicians to a batch
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkAssignTechnicians(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
            'technician_id' => 'required|exists:users,id',
        ]);

        $calibrations = Calibration::where('batch_id', $request->batch_id)
            ->whereIn('status', ['pending', 'paid'])
            ->get();

        if ($calibrations->isEmpty()) {
            return back()->with('error', 'No assignable calibrations found in this batch.');
        }

        DB::beginTransaction();

        try {
            foreach ($calibrations as $calibration) {
                $calibration->update([
                    'performed_by' => $request->technician_id,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                ]);

                // Update job card
                $jobCard = JobCard::where('calibration_id', $calibration->id)->first();
                if ($jobCard) {
                    $jobCard->update([
                        'allocated_to' => $request->technician_id,
                        'status' => 'allocated',
                        'assigned_at' => now(),
                    ]);
                }
            }

            DB::commit();

            Log::info('Bulk technician assignment completed', [
                'batch_id' => $request->batch_id,
                'technician_id' => $request->technician_id,
                'count' => $calibrations->count(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'Technicians assigned to ' . $calibrations->count() . ' calibrations.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk technician assignment failed', [
                'batch_id' => $request->batch_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Bulk assignment failed: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV template
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadTemplate()
    {
        $headers = [
            'Equipment Name',
            'Serial Number',
            'Manufacturer',
            'Model',
            'Accuracy Class',
            'Ratings',
            'Equipment Type',
            'Equipment Condition',
            'Last Calibration Date',
            'Service Type',
            'Notes'
        ];

        $sampleRows = [
            ['Single Phase Meter', 'SN2024001', 'Hexing', 'HXE-320', '1.0', '230V, 10-100A, 50Hz', '1 Phase watt hour meter', 'Good', '2024-01-15', 'EM001', 'Routine calibration'],
            ['3 Phase Meter', 'SN2024002', 'Landis+Gyr', 'MK6E', '0.5S', '230V, 10-100A, 50Hz', '3 Phase Whole current watt hour Meter', 'Excellent', '2024-02-01', 'EM001', 'New equipment'],
            ['Current Transformer', 'SN2024003', 'Ritz', 'KSO 1811', '0.2', '100/5A, 50Hz', 'Current Transformer', 'Good', '', 'ET001', 'Calibration required'],
        ];

        $filename = 'bulk_calibration_template.csv';

        return response()->stream(function() use ($headers, $sampleRows) {
            $handle = fopen('php://output', 'w');

            // Write headers
            fputcsv($handle, $headers);

            // Write sample rows
            foreach ($sampleRows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }


public function uploadTestReport(Certificate $certificate)
{
    abort_unless(
        auth()->user()?->can('perform_calibrations'),
        403
    );

    $certificate->load([
        'calibration.items',
        'calibration.customer',
    ]);

    abort_unless(
        $certificate->calibration,
        404,
        'Calibration not found.'
    );

    $calibrationItems = $certificate->calibration
        ->items
        ->where('review_status', 'testing')
        ->values();

    $equipment = Equipment::query()
        ->where('status', 'active')
        ->orderBy('name')
        ->get([
            'id',
            'equipment_number',
            'name',
            'serial_number',
            'model',
            'manufacturer',
        ]);

    return view(
        'admin.certificates.upload-test-report',
        compact(
            'certificate',
            'calibrationItems',
            'equipment'
        )
    );
}

/**
 * Clean certificate number for use as a filename.
 */
private function cleanCertificateNumber(?string $certificateNumber): string
{
    $certificateNumber = $certificateNumber ?: 'certificate';

    return preg_replace(
        '/[\/\\\\:\*\?"<>\|]/',
        '_',
        $certificateNumber
    );
}

/**
 * Build download filename.
 */
private function certificateDownloadFilename(
    Certificate $certificate
): string {
    return 'certificate-'
        . $this->cleanCertificateNumber(
            $certificate->certificate_number
        )
        . '.pdf';
}

    private function generateCertificatePdf(Certificate $certificate)
    {
        $certificate->refresh();

        $certificate->load([
            'calibration',
            'calibration.customer',
            'calibration.equipment',
            'calibration.equipmentType',
            'calibrationItem',
            'calibrationItem.testResults',
            'customer',
            'generatedBy',
            'checkedBy',
            'approvedBy',
            'issuedBy',
            'sentBy',
            'calibrationItem',
'calibrationItem.testResults',
        ]);

        $testResults = $this->processTestResults(
            $certificate->calibration_id,
            $certificate->calibration_item_id
        );

        $serviceTypes = $this->getServiceTypes(
            $certificate
        );

        $item = $certificate->calibrationItem;

if ($certificate->calibration_item_id && !$item) {
    throw new \RuntimeException(
        'The equipment item associated with this certificate was not found.'
    );
}


        $data = [
            'certificate' => $certificate,
            'calibration' => $certificate->calibration,
            'customer' => $certificate->customer
                ?? $certificate->calibration?->customer,
            'service' => $certificate->calibration,
            'batch' => $certificate->customer
                ?? $certificate->calibration?->customer,
            'stypes' => $serviceTypes->first(),
            'serviceTypes' => $serviceTypes,
            'testResults' => $testResults,
            'calibrationItem' => $item,
        ];

        $pdf = PDF::loadView(
            'admin.certificates.certificate-pdf',
            $data
        );

        $pdf->setPaper('a4', 'portrait');

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
            'chroot' => base_path(),
        ]);

        return $pdf;
    }

    public function sendToCustomer(
        Request $request,
        Certificate $certificate
    ) {
        return $this->send($request, $certificate);
    }

/**
 * Notify active supervisors that a generated certificate
 * requires checking.
 */
protected function notifySupervisors(
    Certificate $certificate
): void {
    $certificate->loadMissing([
        'customer',
        'calibration.customer',
        'generatedBy',
    ]);

    $supervisors = User::role('supervisor')
        ->whereNotNull('email')
        ->where('email', '!=', '')
        ->when(
            \Illuminate\Support\Facades\Schema::hasColumn(
                'users',
                'is_active'
            ),
            fn ($query) => $query->where('is_active', true)
        )
        ->get();

    if ($supervisors->isEmpty()) {
        Log::warning(
            'Certificate generated but no supervisor recipient was found.',
            [
                'certificate_id' => $certificate->id,
                'certificate_number' =>
                    $certificate->certificate_number,
            ]
        );

        return;
    }

    foreach ($supervisors as $supervisor) {
        try {
            Mail::to($supervisor->email)->send(
                new CertificateRequiresCheckingMail(
                    $certificate
                )
            );
        } catch (\Throwable $exception) {
            Log::error(
                'Supervisor certificate notification failed.',
                [
                    'certificate_id' => $certificate->id,
                    'supervisor_id' => $supervisor->id,
                    'email' => $supervisor->email,
                    'message' => $exception->getMessage(),
                ]
            );
        }
    }
}

/**
 * Notify active laboratory managers that a checked
 * certificate requires approval.
 */
protected function notifyLabManagers(
    Certificate $certificate
): void {
    $certificate->loadMissing([
        'customer',
        'calibration.customer',
        'checkedBy',
    ]);

    $labManagers = User::role('lab_manager')
        ->whereNotNull('email')
        ->where('email', '!=', '')
        ->when(
            \Illuminate\Support\Facades\Schema::hasColumn(
                'users',
                'is_active'
            ),
            fn ($query) => $query->where('is_active', true)
        )
        ->get();

    if ($labManagers->isEmpty()) {
        Log::warning(
            'Certificate checked but no laboratory manager recipient was found.',
            [
                'certificate_id' => $certificate->id,
                'certificate_number' =>
                    $certificate->certificate_number,
            ]
        );

        return;
    }

    foreach ($labManagers as $labManager) {
        try {
            Mail::to($labManager->email)->send(
                new CertificateRequiresApprovalMail(
                    $certificate
                )
            );
        } catch (\Throwable $exception) {
            Log::error(
                'Laboratory manager certificate notification failed.',
                [
                    'certificate_id' => $certificate->id,
                    'lab_manager_id' => $labManager->id,
                    'email' => $labManager->email,
                    'message' => $exception->getMessage(),
                ]
            );
        }
    }
}

/**
 * Regenerate the stored certificate PDF after workflow changes.
 */
    private function regenerateCertificatePdf(
        Certificate $certificate
    ): void {
        $certificate->refresh();

        $pdf = $this->generateCertificatePdf(
            $certificate
        );

        $safeCertificateNumber = $this->cleanCertificateNumber(
            $certificate->certificate_number
                ?: 'certificate-' . $certificate->id
        );

        $relativePath =
            'certificates/certificate-'
            . $safeCertificateNumber
            . '.pdf';

        $disk = Storage::disk('public');

        if (!$disk->put($relativePath, $pdf->output())) {
            throw new \RuntimeException(
                'Unable to save the regenerated certificate PDF.'
            );
        }

        if ($certificate->pdf_path !== $relativePath) {
            $certificate->forceFill([
                'pdf_path' => $relativePath,
            ])->saveQuietly();
        }

        Log::info('Stored certificate PDF regenerated', [
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificate->certificate_number,
            'status' => $certificate->status,
            'pdf_path' => $relativePath,
            'user_id' => Auth::id(),
        ]);
    }
}
