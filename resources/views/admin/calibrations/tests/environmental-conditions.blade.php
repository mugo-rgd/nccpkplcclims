
@extends('layouts.app')

@section('title', 'Equipment Environmental Conditions')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                Environmental Conditions / Test Parameters
            </h5>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                <strong>Calibration:</strong> #{{ $calibration->id }}<br>
                <strong>Equipment:</strong> {{ $item->equipment_name }}<br>
                <strong>Serial Number:</strong> {{ $item->serial_number }}
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('calibrations.items.environment.update', [
                      'calibration' => $calibration->id,
                      'item' => $item->id
                  ]) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    @foreach([
                        'temperature_start' => ['Temperature Start', '°C', '0.01'],
                        'temperature_end' => ['Temperature End', '°C', '0.01'],
                        'humidity_start' => ['Humidity Start', '%', '0.01'],
                        'humidity_end' => ['Humidity End', '%', '0.01'],
                        'supply_voltage' => ['Supply Voltage', 'V', '0.01'],
                        'supply_frequency' => ['Supply Frequency', 'Hz', '0.01']
                    ] as $field => $details)
                        <div class="col-md-6">
                            <label for="{{ $field }}" class="form-label">
                                {{ $details[0] }} ({{ $details[1] }})
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                id="{{ $field }}"
                                name="{{ $field }}"
                                step="{{ $details[2] }}"
                                class="form-control @error($field) is-invalid @enderror"
                                value="{{ old($field, $item->{$field}) }}"
                                required
                            >

                            @error($field)
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('calibrations.tests.index', $calibration) }}"
                       class="btn btn-outline-secondary">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        Save Environmental Conditions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
