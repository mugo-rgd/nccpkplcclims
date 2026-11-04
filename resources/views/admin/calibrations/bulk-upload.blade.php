@extends('layouts.app')

@section('title', 'Bulk Equipment Upload')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-file-upload"></i> Bulk Equipment Upload</h2>
                        <a href="{{ route('calibrations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Flash Messages -->
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if(session('warning'))
                        <div class="alert alert-warning">
                            {{ session('warning') }}
                            @if(session('import_errors'))
                                <ul class="mt-2 mb-0">
                                    @foreach(session('import_errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Instructions</h5>
                        <ol>
                            <li>Select a customer from the dropdown.</li>
                            <li>Upload a CSV file with equipment details.</li>
                            <li>Required columns: <strong>Equipment Name</strong> and <strong>Serial Number</strong>.</li>
                            <li>Download the sample template below.</li>
                            <li>Maximum file size: <strong>5MB</strong></li>
                        </ol>
                    </div>

                    <div class="text-center mb-4">
                        <a href="{{ route('calibrations.download-template') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-file-download"></i> Download Sample Template
                        </a>
                    </div>

                    <hr>

                    <form method="POST" action="{{ route('calibrations.bulk-upload.process') }}"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-building"></i> Customer <span class="text-danger">*</span>
                                </label>
                                <select name="customer_id" class="form-select" required>
                                    <option value="">— Select Customer —</option>
                                    @foreach($customers as $id => $name)
                                        <option value="{{ $id }}" {{ old('customer_id') == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-file-csv"></i> CSV File <span class="text-danger">*</span>
                                </label>
                                <div class="drop-zone" id="dropZone">
                                    <div class="drop-zone-content">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
                                        <p class="mt-2 mb-0"><strong>Drop your CSV file here</strong></p>
                                        <p class="text-muted">or click to browse</p>
                                        <small class="text-muted">Supported formats: .csv (Max 5MB)</small>
                                    </div>
                                    <input type="file" name="csv_file" id="fileInput" class="drop-zone-input" accept=".csv" required>
                                </div>
                                <div id="fileInfo" style="display:none;" class="mt-2">
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Selected file: <strong id="fileName"></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="uploadProgress" style="display:none;" class="mt-3">
                            <div class="progress">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                     role="progressbar" style="width: 0%">0%</div>
                            </div>
                            <p class="text-muted mt-1">Processing upload...</p>
                        </div>

                        <div id="csvPreview" style="display:none;" class="mt-3">
                            <h5><i class="fas fa-table me-1"></i> File Preview</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="previewTable">
                                    <thead id="previewHeader"></thead>
                                    <tbody id="previewBody"></tbody>
                                </table>
                            </div>
                            <p class="text-muted">Showing first 5 rows of uploaded file</p>
                        </div>

<!-- Equipment Type & Service Type Selection -->
<div class="row mt-3">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">
            <i class="fas fa-tools"></i> Default Equipment Type
        </label>
        <select name="equipment_type_id" class="form-select">
            <option value="">— Auto-detect —</option>
            @foreach($equipmentTypes as $type)
                <option value="{{ $type->id }}" {{ old('equipment_type_id') == $type->id ? 'selected' : '' }}>
                    {{ $type->name }} (KSh {{ number_format($type->rate, 2) }})
                </option>
            @endforeach
        </select>
        <small class="text-muted">Select a default type or let the system auto-detect</small>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">
            <i class="fas fa-cogs"></i> Service Types
        </label>
        <select name="service_type_ids[]" class="form-select" multiple>
            @foreach($serviceTypes as $service)
                <option value="{{ $service->id }}" {{ in_array($service->id, old('service_type_ids', [])) ? 'selected' : '' }}>
                    {{ $service->serv_code }} - {{ $service->serv_name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">
            <i class="fas fa-layer-group"></i> Batch Size
        </label>
        <select name="batch_size" class="form-select">
            <option value="100">100 rows per batch</option>
            <option value="500">500 rows per batch</option>
            <option value="1000" selected>1000 rows per batch</option>
            <option value="5000">5000 rows per batch</option>
            <option value="10000">10000 rows per batch</option>
        </select>
        <small class="text-muted">Larger batches process faster but may time out</small>
    </div>
</div>


                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload"></i> Upload & Process
                            </button>
                            <a href="{{ route('calibrations.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>

                    <hr>

                    <div class="mt-4">
                        <h5><i class="fas fa-list"></i> CSV File Format</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Column Name</th>
                                        <th>Required</th>
                                        <th>Example</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Equipment Name</td><td><span class="badge bg-danger">Required</span></td><td>Single Phase Meter</td></tr>
                                    <tr><td>Serial Number</td><td><span class="badge bg-danger">Required</span></td><td>SN2024001</td></tr>
                                    <tr><td>Manufacturer</td><td><span class="badge bg-secondary">Optional</span></td><td>Hexing</td></tr>
                                    <tr><td>Model</td><td><span class="badge bg-secondary">Optional</span></td><td>HXE-320</td></tr>
                                    <tr><td>Accuracy Class</td><td><span class="badge bg-secondary">Optional</span></td><td>1.0</td></tr>
                                    <tr><td>Ratings</td><td><span class="badge bg-secondary">Optional</span></td><td>230V, 10-100A, 50Hz</td></tr>
                                    <tr><td>Equipment Type</td><td><span class="badge bg-secondary">Optional</span></td><td>Single Phase Meter</td></tr>
                                    <tr><td>Equipment Condition</td><td><span class="badge bg-secondary">Optional</span></td><td>Good</td></tr>
                                    <tr><td>Last Calibration Date</td><td><span class="badge bg-secondary">Optional</span></td><td>2024-01-15</td></tr>
                                    <tr><td>Notes</td><td><span class="badge bg-secondary">Optional</span></td><td>Routine calibration</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.drop-zone {
    border: 2px dashed #ccc;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: #fafafa;
    position: relative;
}

.drop-zone:hover {
    border-color: #0d6efd;
    background: #f0f7ff;
}

.drop-zone.dragover {
    border-color: #0d6efd;
    background: #e3f2fd;
}

.drop-zone-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.drop-zone-content {
    pointer-events: none;
}

.drop-zone-content i {
    color: #0d6efd;
}

.progress {
    height: 25px;
}

.progress-bar {
    font-size: 0.9rem;
    line-height: 25px;
    font-weight: 600;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const csvPreview = document.getElementById('csvPreview');
    const previewHeader = document.getElementById('previewHeader');
    const previewBody = document.getElementById('previewBody');
    const progressBar = document.getElementById('progressBar');
    const uploadProgress = document.getElementById('uploadProgress');

    // Click to browse
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });

    // Drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFile(fileInput.files[0]);
        }
    });

    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files.length) {
            handleFile(this.files[0]);
        }
    });

    function handleFile(file) {
        // Validate file type
        if (!file.name.endsWith('.csv') && !file.name.endsWith('.txt')) {
            alert('Please upload a CSV file.');
            fileInput.value = '';
            return;
        }

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit.');
            fileInput.value = '';
            return;
        }

        // Show file info
        fileInfo.style.display = 'block';
        fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';

        // Preview CSV
        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            previewCSV(text);
        };
        reader.readAsText(file);
    }

    function previewCSV(text) {
        const lines = text.split('\n').filter(line => line.trim() !== '');
        if (lines.length < 2) {
            return;
        }

        csvPreview.style.display = 'block';

        // Headers
        const headers = lines[0].split(',');
        previewHeader.innerHTML = '<tr>' + headers.map(h => `<th>${h.trim()}</th>`).join('') + '</tr>';

        // Body (first 5 rows)
        const maxRows = Math.min(5, lines.length - 1);
        let bodyHTML = '';
        for (let i = 1; i <= maxRows; i++) {
            const row = lines[i].split(',');
            bodyHTML += '<tr>' + row.map(c => `<td>${c.trim() || '&nbsp;'}</td>`).join('') + '</tr>';
        }
        previewBody.innerHTML = bodyHTML;
    }

    // Form submit with progress
    document.querySelector('form').addEventListener('submit', function(e) {
        const customerSelect = document.querySelector('select[name="customer_id"]');
        const fileInputCheck = document.getElementById('fileInput');

        if (!customerSelect.value) {
            e.preventDefault();
            alert('Please select a customer.');
            return;
        }

        if (!fileInputCheck.files.length) {
            e.preventDefault();
            alert('Please select a CSV file to upload.');
            return;
        }

        // Show progress
        uploadProgress.style.display = 'block';
        progressBar.style.width = '50%';
        progressBar.textContent = '50%';

        // Simulate progress (actual progress handled by server)
        setTimeout(function() {
            progressBar.style.width = '75%';
            progressBar.textContent = '75%';
        }, 1000);
    });

    // Prevent form submission timeout
    const form = document.querySelector('form');
    let progressInterval = null;

    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        // Start progress polling
        progressInterval = setInterval(checkProgress, 5000);
    });

    function checkProgress() {
        fetch('{{ route("calibrations.bulk-upload.progress") }}')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'processing') {
                    const progress = (data.processed / data.total * 100);
                    document.getElementById('progressBar').style.width = progress + '%';
                    document.getElementById('progressBar').textContent = Math.round(progress) + '%';
                    document.getElementById('progressText').textContent =
                        `Processing ${data.processed} of ${data.total} items...`;
                } else if (data.status === 'completed' || data.status === 'failed') {
                    clearInterval(progressInterval);
                }
            })
            .catch(error => {
                console.error('Progress check failed:', error);
            });
    }
});
</script>
@endpush
