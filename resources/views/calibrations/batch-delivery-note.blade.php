@php
    // Set default delivery type if not defined
    $delivery_type = $delivery_type ?? 'delivery';
    $isCollection = $delivery_type == 'collection';
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $isCollection ? 'Batch Collection Note' : 'Batch Delivery Note' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            border-bottom: 2px solid #1a237e;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            padding: 5px;
            vertical-align: middle;
            border: none;
        }
        .header-logo {
            width: 100px;
            text-align: center;
        }
        .header-logo img {
            height: 60px;
            width: auto;
        }
        .header-title {
            text-align: center;
        }
        .header-title h1 {
            margin: 0;
            font-size: 20px;
            color: #1a237e;
        }
        .header-title .subtitle {
            font-size: 12px;
            color: #666;
        }
        .header-right {
            text-align: right;
            width: 180px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-delivery { background: #28a745; color: white; }
        .badge-collection { background: #17a2b8; color: white; }
        .badge-primary { background: #1a237e; color: white; }
        .info-section {
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .info-section table {
            width: 100%;
        }
        .info-section td {
            padding: 3px 5px;
            border: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background: #1a237e;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        .signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature div {
            width: 200px;
        }
        .signature hr {
            margin: 5px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .acknowledgment {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .acknowledgment table {
            margin-bottom: 0;
        }
        .acknowledgment td {
            border: none;
            padding: 8px;
        }
        .status-completed { color: #28a745; font-weight: bold; }
        .status-progress { color: #17a2b8; font-weight: bold; }
        .status-pending { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    @php
                        $logoPath = public_path('img/logo.png');
                        $logoExists = file_exists($logoPath);
                    @endphp
                    @if($logoExists)
                        <img src="{{ public_path('img/logo.png') }}" alt="{{ config('app.name') }}">
                    @else
                        <div style="width: 60px; height: 60px; background: #1a237e; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: bold;">MCL</div>
                    @endif
                </td>
                <td class="header-title">
                    <h1>{{ $isCollection ? 'BATCH COLLECTION NOTE' : 'BATCH DELIVERY NOTE' }}</h1>
                    <div class="subtitle">{{ config('app.name', 'Meter Central Laboratory') }}</div>
                </td>
                <td class="header-right">
                    <p><span class="badge {{ $isCollection ? 'badge-collection' : 'badge-delivery' }}">
                        {{ $isCollection ? 'COLLECTION' : 'DELIVERY' }}
                    </span></p>
                    <p><strong>Batch ID:</strong> {{ $batch_id }}</p>
                    <p><strong>Date:</strong> {{ $generated_at->format('Y-m-d') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Customer Info -->
    <div class="info-section">
        <table>
            <tr>
                <td width="15%"><strong>Customer:</strong></td>
                <td width="35%">{{ $customer->company_name ?? 'N/A' }}</td>
                <td width="15%"><strong>Contact:</strong></td>
                <td>{{ $customer->contact_person ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Phone:</strong></td>
                <td>{{ $customer->phone ?? 'N/A' }}</td>
                <td><strong>Email:</strong></td>
                <td>{{ $customer->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Address:</strong></td>
                <td colspan="3">{{ $customer->address ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Summary -->
    <div style="margin-bottom: 15px;">
        <table>
            <tr>
                <td width="33%"><strong>Total Equipment:</strong> <span style="font-size: 18px; font-weight: bold;">{{ $total_items }}</span></td>
                <td width="33%"><strong>Completed:</strong> <span style="font-size: 18px; font-weight: bold; color: #28a745;">{{ $completed_items ?? $total_items }}</span></td>
                <td width="33%"><strong>Note Type:</strong> <span style="font-size: 14px; font-weight: bold; color: #1a237e;">{{ strtoupper($delivery_type) }}</span></td>
            </tr>
        </table>
    </div>

    <!-- Equipment List -->
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%">Equipment Name</th>
                <th width="15%">Serial Number</th>
                <th width="15%">Manufacturer</th>
                <th width="15%">Model</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($calibrations as $index => $cal)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $cal->equipment_name }}</td>
                <td>{{ $cal->serial_number }}</td>
                <td>{{ $cal->manufacturer ?? 'N/A' }}</td>
                <td>{{ $cal->model ?? 'N/A' }}</td>
                <td>
                    @if($cal->status == 'completed')
                        <span class="status-completed">✓ Completed</span>
                    @elseif(in_array($cal->status, ['assigned', 'in_progress', 'testing']))
                        <span class="status-progress">⏳ In Progress</span>
                    @else
                        <span class="status-pending">⏰ Pending</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No equipment found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Acknowledgment -->
    <div class="acknowledgment">
        <table>
            <tr>
                <td width="50%">
                    <p><strong>{{ $isCollection ? 'Collected By:' : 'Received By:' }}</strong></p>
                    <p style="margin-top: 20px;">Name: ________________________</p>
                    <p>Signature: ________________________</p>
                    <p>Date: ________________________</p>
                    <p>ID/Staff No: ________________________</p>
                </td>
                <td width="50%">
                    <p><strong>{{ $isCollection ? 'Issued By:' : 'Delivered By:' }}</strong></p>
                    <p style="margin-top: 20px;">Name: ________________________</p>
                    <p>Signature: ________________________</p>
                    <p>Date: ________________________</p>
                    <p>ID/Staff No: ________________________</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>{{ config('app.name') }} - Meter Central Laboratory</p>
        <p>Batch {{ ucfirst($delivery_type) }} Note #{{ $batch_id }} | Generated: {{ $generated_at->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
