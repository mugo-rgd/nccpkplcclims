{{-- Include inside the Customer Equipment table's Actions <td>, within the $equipmentItems loop. --}}
@php
    $existingItemCertificate = collect($calibration->certificates ?? [])
        ->first(fn ($certificate) => (int) $certificate->calibration_item_id === (int) $item->id);
    $hasMachineReport = filled($item->test_report_path);
@endphp
<div class="d-flex flex-wrap gap-1 justify-content-end align-items-center">
    @if($existingItemCertificate)
        @can('view_certificates')
            <a href="{{ route('certificates.show', $existingItemCertificate) }}" class="btn btn-sm btn-info">
                <i class="fas fa-certificate me-1"></i> View Certificate
            </a>
        @endcan
    @elseif($isKplcStaffUser && in_array($calibration->status, ['completed','certified'], true))
        @can('perform_calibrations')
            <form method="POST" action="{{ route('certificates.generate-item', ['item' => $item->id]) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-file-pdf me-1"></i> Generate Certificate
                </button>
            </form>
        @endcan
    @endif
    @if($hasMachineReport)
        <span class="badge bg-success">Report attached</span>
    @else
        <span class="badge bg-secondary">Report optional</span>
    @endif
</div>
