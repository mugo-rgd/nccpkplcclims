@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-tools"></i> Equipment Inventory</h2>
                        <a href="{{ route('equipment.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Equipment
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search and Filters -->
                    <form method="GET" class="row g-3 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Search equipment..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="in_use" {{ request('status') == 'in_use' ? 'selected' : '' }}>In Use</option>
                                <option value="under_repair" {{ request('status') == 'under_repair' ? 'selected' : '' }}>Under Repair</option>
                                <option value="calibration_due" {{ request('status') == 'calibration_due' ? 'selected' : '' }}>Calibration Due</option>
                                <option value="retired" {{ request('status') == 'retired' ? 'selected' : '' }}>Retired</option>
                            </select>
                        </div>

                        <div class="col-md-3">
    <label
        for="configuration_status"
        class="form-label"
    >
        Configuration Status
    </label>

    <select
        id="configuration_status"
        name="configuration_status"
        class="form-select"
    >
        <option value="">
            All Equipment
        </option>

        <option
            value="complete"
            @selected(
                request('configuration_status')
                === 'complete'
            )
        >
            Configuration Complete
        </option>

        <option
            value="incomplete"
            @selected(
                request('configuration_status')
                === 'incomplete'
            )
        >
            Configuration Incomplete
        </option>
    </select>
</div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>

                        <a
    href="{{ route('equipment.index') }}"
    class="btn btn-outline-secondary"
>
    <i class="fas fa-rotate-left me-1"></i>
    Clear Filters
</a>
                    </form>
                    <div class="card configuration-table-card border-0 mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                <i class="fas fa-list-check text-primary me-2"></i>
                Equipment Configuration Compliance
            </h5>

            @if(Route::has('equipment.index'))
                <a
                    href="{{ route('equipment.index', [
                        'configuration_status' =>
                            'incomplete',
                    ]) }}"
                    class="btn btn-sm btn-outline-warning"
                >
                    Review Incomplete Equipment
                </a>
            @endif
        </div>
    </div>

    <div class="card-body">
        @php
            $totalEquipment =
                $configurationStatistics['total'] ?? 0;

            $completeEquipment =
                $configurationStatistics['complete'] ?? 0;

            $configurationPercentage =
                $totalEquipment > 0
                    ? round(
                        (
                            $completeEquipment
                            / $totalEquipment
                        ) * 100,
                        1
                    )
                    : 100;
        @endphp

        <div class="d-flex justify-content-between mb-2">
            <span class="fw-semibold">
                Configuration completion
            </span>

            <strong>
                {{ $configurationPercentage }}%
            </strong>
        </div>

        <div
            class="progress mb-4"
            style="height:12px;"
        >
            <div
                class="progress-bar bg-success"
                role="progressbar"
                style="width:{{
                    min(
                        $configurationPercentage,
                        100
                    )
                }}%;"
                aria-valuenow="{{
                    $configurationPercentage
                }}"
                aria-valuemin="0"
                aria-valuemax="100"
            ></div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="configuration-compliance-item">
                    <span class="text-muted">
                        Missing Category
                    </span>

                    <strong class="text-warning">
                       {{ number_format(
    $configurationStatistics['missing_category'] ?? 0
) }}
                    </strong>
                </div>
            </div>

            <div class="col-md-4">
                <div class="configuration-compliance-item">
                    <span class="text-muted">
                        Missing Manufacturer
                    </span>

                    <strong class="text-warning">
                      {{ number_format(
    $configurationStatistics['missing_manufacturer'] ?? 0
) }}
                    </strong>
                </div>
            </div>

            <div class="col-md-4">
                <div class="configuration-compliance-item">
                    <span class="text-muted">
                        Missing Pricing Type
                    </span>

                    <strong class="text-warning">
                       {{ number_format(
    $configurationStatistics['missing_type'] ?? 0
) }}
                    </strong>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <a
            href="{{ route('equipment.index', [
                'configuration_status' => 'complete',
            ]) }}"
            class="text-decoration-none"
        >
            <div class="card configuration-summary-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="configuration-summary-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-circle-check"></i>
                    </span>

                    <div>
                        <small class="text-muted">
                            Fully Configured
                        </small>

                        <h3 class="fw-bold text-success mb-0">
                            {{
                                number_format(
                                    $configurationStatistics['complete']
                                    ?? 0
                                )
                            }}
                        </h3>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6">
        <a
            href="{{ route('equipment.index', [
                'configuration_status' => 'incomplete',
            ]) }}"
            class="text-decoration-none"
        >
            <div class="card configuration-summary-card border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span class="configuration-summary-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-triangle-exclamation"></i>
                    </span>

                    <div>
                        <small class="text-muted">
                            Incomplete
                        </small>

                        <h3 class="fw-bold text-warning mb-0">
                            {{
                                number_format(
                                    $configurationStatistics['incomplete']
                                    ?? 0
                                )
                            }}
                        </h3>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card configuration-summary-card border-0 h-100">
            <div class="card-body d-flex align-items-center">
                <span class="configuration-summary-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="fas fa-layer-group"></i>
                </span>

                <div>
                    <small class="text-muted">
                        Missing Category
                    </small>

                    <h3 class="fw-bold mb-0">
                        {{
                            number_format(
                                $configurationStatistics[
                                    'missing_category'
                                ] ?? 0
                            )
                        }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card configuration-summary-card border-0 h-100">
            <div class="card-body d-flex align-items-center">
                <span class="configuration-summary-icon bg-danger bg-opacity-10 text-danger me-3">
                    <i class="fas fa-tags"></i>
                </span>

                <div>
                    <small class="text-muted">
                        Missing Pricing Type
                    </small>

                    <h3 class="fw-bold mb-0">
                        {{
                            number_format(
                                $configurationStatistics[
                                    'missing_type'
                                ] ?? 0
                            )
                        }}
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div>
                    <!-- Equipment Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Serial Number</th>
                                    <th>Model</th>
                                    <th>Manufacturer</th>
                                    <th>Status</th>
                                    <th class="text-center"> Configuration</th>
                                    <th>Condition</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
    @forelse($equipment as $item)
        @php
            $missingConfiguration = collect([
                'Category' =>
                    empty($item->category_id),

                'Manufacturer' =>
                    empty($item->manufacturer_id),

                'Pricing Type' =>
                    empty($item->type_id),
            ])->filter();

            $status = strtolower(
                (string) ($item->status ?? 'unknown')
            );

            $statusColor = match ($status) {
                'active' => 'success',
                'in_use' => 'info',
                'under_repair' => 'warning',
                'calibration_due' => 'danger',
                'retired' => 'secondary',
                default => 'secondary',
            };

            $condition = strtolower(
                (string) ($item->condition ?? 'unknown')
            );

            $conditionColor = match ($condition) {
                'excellent' => 'success',
                'good' => 'primary',
                'fair' => 'warning',
                'poor',
                'needs_repair' => 'danger',
                default => 'secondary',
            };
        @endphp

        <tr>
            <td>
                {{ $item->id }}
            </td>

            <td>
                <strong>
                    {{ $item->name ?? 'Unnamed Equipment' }}
                </strong>

                @if($item->categoryRecord)
                    <small class="d-block text-muted">
                        {{ $item->categoryRecord->code }}
                        -
                        {{ $item->categoryRecord->name }}
                    </small>
                @endif
            </td>

            <td>
                {{ $item->serial_number ?? 'N/A' }}
            </td>

            <td>
                {{ $item->model ?? 'N/A' }}
            </td>

            <td>
                @if($item->manufacturerRecord)
                    <strong>
                        {{ $item->manufacturerRecord->name }}
                    </strong>

                    <small class="d-block text-muted">
                        {{ $item->manufacturerRecord->code }}
                    </small>
                @elseif(!empty($item->getRawOriginal('manufacturer')))
                    {{ $item->getRawOriginal('manufacturer') }}

                    <small class="d-block text-warning">
                        Legacy value
                    </small>
                @else
                    <span class="text-muted">
                        N/A
                    </span>
                @endif
            </td>

            <td>
                <span class="badge bg-{{ $statusColor }}">
                    {{ ucwords(
                        str_replace('_', ' ', $status)
                    ) }}
                </span>
            </td>

            <td class="text-center">
                @if($missingConfiguration->isEmpty())
                    <span class="badge bg-success">
                        <i class="fas fa-check me-1"></i>
                        Complete
                    </span>
                @else
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        Incomplete
                    </span>

                    <small class="d-block text-muted mt-1">
                        Missing:
                        {{ $missingConfiguration
                            ->keys()
                            ->implode(', ') }}
                    </small>
                @endif
            </td>

            <td>
                <span class="badge bg-{{ $conditionColor }}">
                    {{ ucwords(
                        str_replace('_', ' ', $condition)
                    ) }}
                </span>
            </td>

            <td>
                <div class="d-flex flex-wrap gap-1">
                    @if(Route::has('equipment.show'))
                        <a
                            href="{{ route('equipment.show', $item) }}"
                            class="btn btn-sm btn-info"
                            title="View Equipment"
                        >
                            <i class="fas fa-eye"></i>
                        </a>
                    @endif

                    <a
    href="{{ route('equipment.edit', $item) }}"
    class="btn btn-sm {{
        $missingConfiguration->isEmpty()
            ? 'btn-outline-primary'
            : 'btn-warning'
    }}"
    title="{{
        $missingConfiguration->isEmpty()
            ? 'Edit Equipment'
            : 'Edit and Complete Configuration'
    }}"
>
    <i class="fas {{
        $missingConfiguration->isEmpty()
            ? 'fa-edit'
            : 'fa-triangle-exclamation'
    }}"></i>
</a>



                    @if(Route::has('equipment.destroy'))
                        <form
                            action="{{ route(
                                'equipment.destroy',
                                $item
                            ) }}"
                            method="POST"
                            class="d-inline"
                            onsubmit="
                                return confirm(
                                    'Are you sure you want to delete this equipment?'
                                );
                            "
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-sm btn-danger"
                                title="Delete Equipment"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center text-muted py-4">
                <i class="fas fa-tools fa-2x d-block mb-2"></i>
                No equipment found.
            </td>
        </tr>
    @endforelse
</tbody>
                        </table>
                    </div>

                    {{-- {{ $equipment->links() }} --}}
                    {{ $equipment->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
    .configuration-summary-card {
        border-radius: 0.9rem;
        color: #212529;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .configuration-summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.13);
    }

    .configuration-summary-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        flex-shrink: 0;
        border-radius: 50%;
        font-size: 1.25rem;
    }
    .configuration-compliance-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.9rem 1rem;
    border: 1px solid #e9ecef;
    border-radius: 0.65rem;
    background: #f8f9fa;
}
</style>
@endpush
