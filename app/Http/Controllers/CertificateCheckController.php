<?php

namespace App\Http\Controllers;

use App\Models\Calibration;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateCheckController extends Controller
{
    /**
     * Certificate review workflow:
     *
     * generated -> checked -> approved -> issued -> sent
     *
     * Supervisor checks.
     * Lab Manager approves and issues.
     * Segregation of duties prevents the generator/checker from approving
     * the same certificate.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:supervisor|lab_manager|super_admin');
    }

    /**
     * Display certificates in the review workflow.
     */
    public function index(Request $request): View
    {
        $query = Certificate::query()
            ->with([
                'calibration',
                'calibration.customer',
                'calibration.performedBy',
                'customer',
                'generatedBy',
                'checkedBy',
                'approvedBy',
                'issuedBy',
                'sentBy',
        'calibrationItem',
            ])
            ->whereIn('status', [
                'draft',
                'pending',
                'generated',
                'checked',
                'approved',
                'rejected',
                'issued',
                'sent',
            ])
            ->latest('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'LIKE', "%{$search}%")
                    ->orWhere('equipment_name', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhere('reference_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where(
                            'company_name',
                            'LIKE',
                            "%{$search}%"
                        );
                    });
            });
        }

        $certificates = $query
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_pending' => Certificate::query()
                ->whereIn('status', ['draft', 'pending'])
                ->count(),
            'total_generated' => Certificate::query()
                ->where('status', 'generated')
                ->count(),
            'total_checked' => Certificate::query()
                ->where('status', 'checked')
                ->count(),
            'total_approved' => Certificate::query()
                ->where('status', 'approved')
                ->count(),
            'total_rejected' => Certificate::query()
                ->where('status', 'rejected')
                ->count(),
            'total_issued' => Certificate::query()
                ->where('status', 'issued')
                ->count(),
            'total_sent' => Certificate::query()
                ->where('status', 'sent')
                ->count(),
        ];

        return view(
            'certificates.check.index',
            compact('certificates', 'stats')
        );
    }


    public function export(): StreamedResponse
{
    abort_unless(
        auth()->user()?->can('view_certificates'),
        403,
        'You are not authorized to export certificates.'
    );

    $filename = 'certificate-check-' . now()->format('Ymd-His') . '.csv';

    return response()->streamDownload(function () {

        $file = fopen('php://output', 'w');

        fputcsv($file, [
            'Certificate Number',
            'Status',
            'Generated At',
            'Checked At',
            'Approved At',
        ]);

        Certificate::query()
            ->orderBy('id')
            ->chunkById(500, function ($certificates) use ($file) {

                foreach ($certificates as $certificate) {
                    fputcsv($file, [
                        $certificate->certificate_number,
                        $certificate->status,
                        $certificate->generated_at,
                        $certificate->checked_at,
                        $certificate->approved_at,
                    ]);
                }
            });

        fclose($file);

    }, $filename, [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
}
    /**
     * Display one certificate for review.
     */
    public function show(int $id): View
    {
        $certificate = Certificate::query()
            ->with([
                'calibration',
                'calibration.customer',
                'calibration.performedBy',
                'customer',
                'generatedBy',
                'checkedBy',
                'approvedBy',
                'issuedBy',
                'sentBy',
                'calibrationItem',
'calibrationItem.testResults',
            ])
            ->findOrFail($id);

        $calibration = $certificate->calibration;

        $reviewStatuses = [
            'draft',
            'pending',
            'generated',
            'checked',
            'approved',
        ];

        $previous = Certificate::query()
            ->where('id', '<', $certificate->id)
            ->whereIn('status', $reviewStatuses)
            ->latest('id')
            ->first();

        $next = Certificate::query()
            ->where('id', '>', $certificate->id)
            ->whereIn('status', $reviewStatuses)
            ->oldest('id')
            ->first();

        $technicians = User::role([
            'lab_technician',
            'technician',
        ])
            ->orderBy('name')
            ->pluck('name', 'id');

        return view(
            'certificates.check.show',
            compact(
                'certificate',
                'calibration',
                'previous',
                'next',
                'technicians'
            )
        );
    }

    /**
     * Show the latest certificate for a calibration.
     */
    public function showByCalibration(Calibration $calibration): View|RedirectResponse
    {
        $certificate = $calibration
            ->certificates()
            ->latest('id')
            ->first();

        if (!$certificate) {
            return redirect()
                ->route('certificates.check.index')
                ->with(
                    'warning',
                    'No certificate was found for this calibration. Generate a certificate first.'
                );
        }

        return $this->show($certificate->id);
    }

    /**
     * Supervisor checks the certificate.
     */
    public function check(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $certificate = Certificate::query()->findOrFail($id);

        if (!$user->hasAnyRole(['supervisor', 'super_admin'])) {
            abort(403, 'Only a supervisor can check certificates.');
        }

        if ($certificate->status !== 'generated') {
    return back()->with(
        'error',
        'Only generated certificates can be checked.'
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
            'check_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();

        try {
            $certificate->update([
                'status' => 'checked',
                'checked_by' => $user->id,
                'checked_at' => now(),
                'comments' => $this->appendComment(
                    $certificate->comments,
                    'Check Notes',
                    $validated['check_notes'] ?? null
                ),
            ]);

            DB::commit();

            Log::info('Certificate checked', [
                'certificate_id' => $certificate->id,
                'checked_by' => $user->id,
            ]);

            return redirect()
                ->route('certificates.check.show', $certificate)
                ->with(
                    'success',
                    'Certificate checked successfully. The live PDF preview will now show the checker details.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Certificate check failed', [
                'certificate_id' => $certificate->id,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'Failed to check certificate: ' . $exception->getMessage()
            );
        }
    }

    /**
     * Lab Manager approves a checked certificate.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $certificate = Certificate::query()
            ->with('calibration')
            ->findOrFail($id);

            if ($certificate->calibration_item_id !== null) {
    return back()->with(
        'error',
        'Equipment-specific certificate approval is disabled until '
        . 'technical evaluation and PDF verification are completed.'
    );
}

        if (!$user->hasAnyRole(['lab_manager', 'super_admin'])) {
            abort(403, 'Only a lab manager can approve certificates.');
        }

        if ($certificate->status !== 'checked') {
            return back()->with(
                'error',
                'The certificate must be checked before approval.'
            );
        }

        if (
            $certificate->checked_by
            && (int) $certificate->checked_by === (int) $user->id
        ) {
            return back()->with(
                'error',
                'You cannot approve a certificate that you checked.'
            );
        }

        if (
            $certificate->generated_by
            && (int) $certificate->generated_by === (int) $user->id
        ) {
            return back()->with(
                'error',
                'You cannot approve a certificate that you generated.'
            );
        }

        $validated = $request->validate([
            'approval_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();

        try {
            $certificate->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'issued_date' => $certificate->issued_date ?? now(),
                'expiry_date' => $certificate->expiry_date ?? now()->addYear(),
                'is_signed' => true,
                // signed_by is VARCHAR in the current certificates table.
                'signed_by' => $user->name,
                'signed_at' => now(),
                'comments' => $this->appendComment(
                    $certificate->comments,
                    'Approval Notes',
                    $validated['approval_notes'] ?? null
                ),
            ]);

            if ($certificate->calibration) {
                $certificate->calibration->update([
                    'status' => 'completed',
                    'completed_at' => $certificate->calibration->completed_at ?? now(),
                ]);
            }

            DB::commit();

            Log::info('Certificate approved', [
                'certificate_id' => $certificate->id,
                'approved_by' => $user->id,
            ]);

            return redirect()
                ->route('certificates.check.show', $certificate)
                ->with(
                    'success',
                    'Certificate approved successfully. The live PDF preview will now show both checker and approver details.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Certificate approval failed', [
                'certificate_id' => $certificate->id,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'Failed to approve certificate: ' . $exception->getMessage()
            );
        }
    }

    /**
     * Reject a certificate during review.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $certificate = Certificate::query()->findOrFail($id);

        if (!in_array($certificate->status, ['draft', 'pending', 'generated', 'checked'], true)) {
            return back()->with(
                'error',
                'This certificate cannot be rejected in its current state.'
            );
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        DB::beginTransaction();

        try {
            $certificate->update([
                'status' => 'rejected',
                'comments' => $this->appendComment(
                    $certificate->comments,
                    'Rejection Reason',
                    $validated['rejection_reason']
                ),
            ]);

            DB::commit();

            return redirect()
                ->route('certificates.check.index')
                ->with('success', 'Certificate rejected successfully.');
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Certificate rejection failed', [
                'certificate_id' => $certificate->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'Failed to reject certificate: ' . $exception->getMessage()
            );
        }
    }

    /**
     * Return a certificate for modification.
     */
    public function requestModification(Request $request, int $id): RedirectResponse
    {
        $certificate = Certificate::query()->findOrFail($id);

        if (!in_array($certificate->status, ['draft', 'pending', 'generated', 'checked'], true)) {
            return back()->with(
                'error',
                'This certificate cannot be returned for modification in its current state.'
            );
        }

        $validated = $request->validate([
            'modification_notes' => ['required', 'string', 'min:10', 'max:1000'],
            'send_back_to' => ['required', 'integer', 'exists:users,id'],
        ]);

        $recipient = User::query()->findOrFail($validated['send_back_to']);

        if (!$recipient->hasAnyRole(['lab_technician', 'technician'])) {
            return back()
                ->withInput()
                ->with('error', 'The selected recipient is not a laboratory technician.');
        }

        DB::beginTransaction();

        try {
            $certificate->update([
                'status' => 'draft',
                'checked_by' => null,
                'checked_at' => null,
                'approved_by' => null,
                'approved_at' => null,
                'is_signed' => false,
                'signed_by' => null,
                'signed_at' => null,
                'comments' => $this->appendComment(
                    $certificate->comments,
                    'Modification Request',
                    $validated['modification_notes']
                ),
            ]);

            DB::commit();

            return redirect()
                ->route('certificates.check.show', $certificate)
                ->with(
                    'success',
                    'Modification requested. Previous check/approval details were cleared so the revised certificate can go through review again.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Certificate modification request failed', [
                'certificate_id' => $certificate->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'Failed to request modification: ' . $exception->getMessage()
            );
        }
    }

    /**
     * Issue an approved certificate.
     */
    public function issue(int $id): RedirectResponse
    {
        $user = Auth::user();
        $certificate = Certificate::query()->findOrFail($id);

        if (!$user->hasAnyRole(['lab_manager', 'super_admin'])) {
            abort(403, 'Only a lab manager can issue certificates.');
        }
if ($certificate->calibration_item_id !== null) {
    return back()->with(
        'error',
        'Equipment-specific certificate approval is disabled until '
        . 'technical evaluation and PDF verification are completed.'
    );
}
        if ($certificate->status !== 'approved') {
            return back()->with(
                'error',
                'Only an approved certificate can be issued.'
            );
        }

        DB::beginTransaction();

        try {
            $certificate->update([
                'status' => 'issued',
                'issued_by' => $user->id,
                'issued_date' => $certificate->issued_date ?? now(),
                'expiry_date' => $certificate->expiry_date ?? now()->addYear(),
                'is_signed' => true,
                'signed_by' => $certificate->signed_by ?: $user->name,
                'signed_at' => $certificate->signed_at ?? now(),
            ]);

            DB::commit();

            return redirect()
                ->route('certificates.check.show', $certificate)
                ->with(
                    'success',
                    'Certificate issued successfully. Use the live PDF preview or force-download to obtain the current signed version.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Certificate issue failed', [
                'certificate_id' => $certificate->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'Failed to issue certificate: ' . $exception->getMessage()
            );
        }
    }

    /**
     * Fallback status update after the actual customer-delivery action.
     *
     * If your main CertificateController::sendToCustomer() sends the email,
     * prefer that route from the Blade. This method is retained only for
     * projects still using certificates.check.send.
     */
    public function send(int $id): RedirectResponse
    {
        $certificate = Certificate::query()->findOrFail($id);

        if ($certificate->status !== 'issued') {
            return back()->with(
                'error',
                'Issue the certificate before marking it as sent.'
            );
        }

        if ($certificate->calibration_item_id !== null) {
    return back()->with(
        'error',
        'Equipment-specific certificate approval is disabled until '
        . 'technical evaluation and PDF verification are completed.'
    );
}

        DB::beginTransaction();

        try {
            $certificate->update([
                'status' => 'sent',
                'sent_by' => Auth::id(),
                'sent_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('certificates.check.show', $certificate)
                ->with('success', 'Certificate marked as sent successfully.');
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Certificate send status update failed', [
                'certificate_id' => $certificate->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'Failed to update certificate send status: ' . $exception->getMessage()
            );
        }
    }

    /**
     * Bulk approval is limited to certificates that have already been checked.
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['lab_manager', 'super_admin'])) {
            abort(403, 'Only a lab manager can bulk approve certificates.');
        }

    

        $validated = $request->validate([
            'certificate_ids' => ['required', 'array', 'min:1'],
            'certificate_ids.*' => ['integer', 'exists:certificates,id'],
        ]);

        $certificates = Certificate::query()
            ->with('calibration')
            ->whereIn('id', $validated['certificate_ids'])
            ->where('status', 'checked')
            ->get();

        if ($certificates->isEmpty()) {
            return back()->with(
                'error',
                'No checked certificates were selected for approval.'
            );
        }

            if ($certificates->calibration_item_id !== null) {
    return back()->with(
        'error',
        'Equipment-specific certificate approval is disabled until '
        . 'technical evaluation and PDF verification are completed.'
    );
}

        $approvedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($certificates as $certificate) {
                $segregationConflict =
                    ((int) $certificate->checked_by === (int) $user->id)
                    || ((int) $certificate->generated_by === (int) $user->id);

                if ($segregationConflict) {
                    $skippedCount++;
                    continue;
                }

                $certificate->update([
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'issued_date' => $certificate->issued_date ?? now(),
                    'expiry_date' => $certificate->expiry_date ?? now()->addYear(),
                    'is_signed' => true,
                    'signed_by' => $user->name,
                    'signed_at' => now(),
                ]);

                if ($certificate->calibration) {
                    $certificate->calibration->update([
                        'status' => 'completed',
                        'completed_at' => $certificate->calibration->completed_at ?? now(),
                    ]);
                }

                $approvedCount++;
            }

            DB::commit();

            $message = "{$approvedCount} certificate(s) approved successfully.";

            if ($skippedCount > 0) {
                $message .= " {$skippedCount} certificate(s) were skipped because of segregation-of-duties rules.";
            }

            return redirect()
                ->route('certificates.check.index')
                ->with('success', $message);
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Bulk certificate approval failed', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'Failed to bulk approve certificates: ' . $exception->getMessage()
            );
        }
    }

    /**
     * Review statistics.
     */
    public function stats()
    {
        return response()->json([
            'pending' => Certificate::query()
                ->where('status', 'pending')
                ->count(),
            'generated' => Certificate::query()
                ->where('status', 'generated')
                ->count(),
            'checked' => Certificate::query()
                ->where('status', 'checked')
                ->count(),
            'approved_today' => Certificate::query()
                ->where('status', 'approved')
                ->whereDate('approved_at', today())
                ->count(),
            'rejected_today' => Certificate::query()
                ->where('status', 'rejected')
                ->whereDate('updated_at', today())
                ->count(),
            'total_this_week' => Certificate::query()
                ->whereIn('status', ['approved', 'rejected', 'issued', 'sent'])
                ->whereBetween('updated_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])
                ->count(),
        ]);
    }

    /**
     * Append a labelled workflow note without destroying existing comments.
     */
    private function appendComment(
        ?string $existing,
        string $label,
        ?string $value
    ): ?string {
        $value = trim((string) $value);

        if ($value === '') {
            return $existing;
        }

        $entry = "{$label}: {$value}";
        $existing = trim((string) $existing);

        return $existing === ''
            ? $entry
            : $existing . PHP_EOL . PHP_EOL . $entry;
    }
}
