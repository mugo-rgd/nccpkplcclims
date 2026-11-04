@extends('layouts.app')

@section('title', 'Configuration Audit Trail')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1">
                        <i class="fas fa-clock-rotate-left text-primary me-2"></i>
                        Configuration Audit Trail
                    </h2>

                    <p class="text-muted mb-0">
                        Review changes made to laboratory services, pricing,
                        categories and manufacturers.
                    </p>
                </div>

                <a
                    href="{{ route('laboratory-configuration.index') }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="fas fa-arrow-left me-1"></i>
                    Configuration
                </a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <form
                method="GET"
                action="{{ route(
                    'laboratory-configuration.audit'
                ) }}"
                class="row g-2"
            >
                <div class="col-md-6">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Search description or user"
                    >
                </div>

                <div class="col-md-4">
                    <select
                        name="action"
                        class="form-select"
                    >
                        <option value="">
                            All actions
                        </option>

                        @foreach($actions as $actionOption)
                            <option
                                value="{{ $actionOption }}"
                                @selected(
                                    $action === $actionOption
                                )
                            >
                                {{ ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $actionOption
                                    )
                                ) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date and Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Changes</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($activities as $activity)
                            @php
                                $properties =
                                    $activity->properties ?? [];

                                $oldValues =
                                    $properties['old'] ?? [];

                                $newValues =
                                    $properties['new'] ?? [];
                            @endphp

                            <tr>
                                <td>
                                    {{ $activity->created_at
                                        ?->format('d/m/Y H:i:s')
                                        ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $activity->user?->name
                                        ?? 'System' }}
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        {{ ucwords(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $activity->action
                                            )
                                        ) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $activity->description }}
                                </td>

                                <td>
                                    @if(
                                        !empty($oldValues)
                                        || !empty($newValues)
                                    )
                                        <details>
                                            <summary class="text-primary">
                                                View changes
                                            </summary>

                                            <div class="small mt-2">
                                                @foreach(
                                                    array_unique(
                                                        array_merge(
                                                            array_keys(
                                                                $oldValues
                                                            ),
                                                            array_keys(
                                                                $newValues
                                                            )
                                                        )
                                                    )
                                                    as $field
                                                )
                                                    <div class="mb-1">
                                                        <strong>
                                                            {{ ucwords(
                                                                str_replace(
                                                                    '_',
                                                                    ' ',
                                                                    $field
                                                                )
                                                            ) }}:
                                                        </strong>

                                                        <span class="text-danger">
                                                            {{
                                                                is_bool(
                                                                    $oldValues[
                                                                        $field
                                                                    ] ?? null
                                                                )
                                                                    ? (
                                                                        $oldValues[
                                                                            $field
                                                                        ]
                                                                            ? 'Yes'
                                                                            : 'No'
                                                                    )
                                                                    : (
                                                                        $oldValues[
                                                                            $field
                                                                        ]
                                                                        ?? 'N/A'
                                                                    )
                                                            }}
                                                        </span>

                                                        <i class="fas fa-arrow-right mx-1"></i>

                                                        <span class="text-success">
                                                            {{
                                                                is_bool(
                                                                    $newValues[
                                                                        $field
                                                                    ] ?? null
                                                                )
                                                                    ? (
                                                                        $newValues[
                                                                            $field
                                                                        ]
                                                                            ? 'Yes'
                                                                            : 'No'
                                                                    )
                                                                    : (
                                                                        $newValues[
                                                                            $field
                                                                        ]
                                                                        ?? 'N/A'
                                                                    )
                                                            }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-muted">
                                            No field details
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $activity->ip_address ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="text-center text-muted py-5"
                                >
                                    No configuration activities found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($activities->hasPages())
            <div class="card-footer bg-white">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
