<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quality Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #1a237e;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a237e;
            margin-top: 15px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        th {
            background: #1a237e;
            color: white;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #1a237e;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }
        .summary-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-card {
            flex: 1;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            color: white;
        }
        .summary-card.total { background: #1a237e; }
        .summary-card.tracked { background: #28a745; }
        .summary-card.at-risk { background: #ffc107; color: #333; }
        .summary-card.not-met { background: #dc3545; }
        .summary-card h3 { margin: 0; font-size: 20px; }
        .summary-card p { margin: 5px 0 0; font-size: 10px; }
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
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-secondary { background: #6c757d; color: white; }
        .status-ontrack { color: #28a745; font-weight: bold; }
        .status-atrisk { color: #ffc107; font-weight: bold; }
        .status-notmet { color: #dc3545; font-weight: bold; }
        .status-pending { color: #6c757d; font-weight: bold; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Quality Management Report</h1>
        <p>{{ config('app.name', 'Meter Central Laboratory') }}</p>
        <p>
            <strong>FYR:</strong> {{ $fyr }} |
            <strong>Section:</strong> {{ $section }} |
            <strong>Status:</strong> {{ $status }}
        </p>
        <p><strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }} |
        <strong>By:</strong> {{ $generated_by }}</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card total">
            <h3>{{ $summary['total'] }}</h3>
            <p>Total Objectives</p>
        </div>
        <div class="summary-card tracked">
            <h3>{{ $summary['on_track'] }}</h3>
            <p>On Track</p>
        </div>
        <div class="summary-card at-risk">
            <h3>{{ $summary['at_risk'] }}</h3>
            <p>At Risk</p>
        </div>
        <div class="summary-card not-met">
            <h3>{{ $summary['not_met'] }}</h3>
            <p>Not Met</p>
        </div>
    </div>

    <!-- Performance Summary -->
    <div class="section-title">Performance Summary</div>
    <table>
        <tr>
            <td width="50%"><strong>Overall Achievement:</strong></td>
            <td>{{ $summary['avg_achievement'] }}%</td>
        </tr>
        <tr>
            <td><strong>Completion Rate:</strong></td>
            <td>{{ $summary['completion_rate'] }}%</td>
        </tr>
        <tr>
            <td><strong>Tracked Objectives:</strong></td>
            <td>{{ $summary['tracked'] }} / {{ $summary['total'] }}</td>
        </tr>
        <tr>
            <td><strong>Pending:</strong></td>
            <td>{{ $summary['pending'] }}</td>
        </tr>
    </table>

    <!-- Section Analysis -->
    <div class="section-title">Section Analysis</div>
    <table>
        <thead>
            <tr>
                <th>Section</th>
                <th>Total</th>
                <th>On Track</th>
                <th>At Risk</th>
                <th>Not Met</th>
                <th>Avg Achievement</th>
            </tr>
        </thead>
        <tbody>
            @foreach($section_analysis as $section => $data)
            <tr>
                <td><strong>{{ $section }}</strong></td>
                <td>{{ $data['total'] }}</td>
                <td>{{ $data['on_track'] }}</td>
                <td>{{ $data['at_risk'] }}</td>
                <td>{{ $data['not_met'] }}</td>
                <td>{{ $data['avg_achievement'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Detailed Objectives -->
    <div class="section-title">Detailed Objectives</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Description</th>
                <th>Section</th>
                <th>Target</th>
                <th>Achievement</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($objectives as $objective)
            @php
                $achievement = $objective->achievements->first();
            @endphp
            <tr>
                <td>{{ $objective->objective_id }}</td>
                <td>{{ \Illuminate\Support\Str::limit($objective->description, 40) }}</td>
                <td>{{ $objective->section }}</td>
                <td>{{ $objective->target_value }}</td>
                <td>
                    @if($achievement)
                        {{ $achievement->actual_achievement }}
                        ({{ round($achievement->achievement_percentage ?? 0, 2) }}%)
                    @else
                        <span class="text-muted">Not tracked</span>
                    @endif
                </td>
                <td>
                    @if($achievement)
                        @if($achievement->status == 'On Track')
                            <span class="status-ontrack">✅ On Track</span>
                        @elseif($achievement->status == 'At Risk')
                            <span class="status-atrisk">⚠️ At Risk</span>
                        @elseif($achievement->status == 'Not Met')
                            <span class="status-notmet">❌ Not Met</span>
                        @else
                            <span class="status-pending">⏳ Pending</span>
                        @endif
                    @else
                        <span class="status-pending">⏳ Pending</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No objectives found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>{{ config('app.name') }} - Meter Central Laboratory</p>
        <p>Report generated on {{ $generated_at->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
