{{-- Place this inside the existing supervisor certificate check form (POST to certificates.check). --}}
@php
    $overall = strtolower((string) ($stats['overall_result'] ?? 'Not Evaluated'));
    $suggestedConformity = match ($overall) {
        'pass' => 'The equipment meets the specified requirements according to the applicable standards and the documented decision rule.',
        'fail' => 'The equipment does not meet the specified requirements according to the applicable standards and the documented decision rule.',
        default => 'Conformity cannot be determined until the results and applicable decision rule have been reviewed.',
    };
    $item = $certificate->calibrationItem;
    $instruments = $item ? $item->referenceInstruments : collect();
    $hasReferences = $instruments->isNotEmpty() || (bool) $item?->laboratoryEquipment;
    $suggestedTraceability = $hasReferences
        ? 'The measurement results are traceable to the International System of Units (SI) through the reference instruments identified in the certificate, subject to verification of their calibration certificates and traceability chains.'
        : 'Metrological traceability has not yet been demonstrated. Reference instruments and supporting calibration records require review.';
@endphp
<div class="mb-3">
    <label for="statement_conformity" class="form-label fw-bold">Statement of Conformity</label>
    <textarea id="statement_conformity" name="statement_conformity" rows="3" maxlength="5000" required class="form-control @error('statement_conformity') is-invalid @enderror">{{ old('statement_conformity', $certificate->statement_conformity ?: $suggestedConformity) }}</textarea>
    @error('statement_conformity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <small class="text-muted">Confirm the applicable standard, decision rule, and all evaluated test results. Suggested wording is not an automatic conformity determination.</small>
</div>
<div class="mb-3">
    <label for="traceability_information" class="form-label fw-bold">Metrological Traceability</label>
    <textarea id="traceability_information" name="traceability_information" rows="4" maxlength="5000" required class="form-control @error('traceability_information') is-invalid @enderror">{{ old('traceability_information', $certificate->traceability_information ?: $suggestedTraceability) }}</textarea>
    @error('traceability_information')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <small class="text-muted">Verify instrument identity, certificate numbers, validity at testing date, and the documented SI traceability chain. Do not claim CIPM MRA signatory status without evidence.</small>
</div>
<div class="form-check mb-2">
    <input class="form-check-input" type="checkbox" name="conformity_review_confirmed" value="1" id="conformity_review_confirmed" required>
    <label class="form-check-label" for="conformity_review_confirmed">I have reviewed the conformity statement against the results and decision rule.</label>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="traceability_review_confirmed" value="1" id="traceability_review_confirmed" required>
    <label class="form-check-label" for="traceability_review_confirmed">I have reviewed the traceability statement and its supporting records.</label>
</div>
