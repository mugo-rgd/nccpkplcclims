<table
    role="presentation"
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
    style="
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    "
>
    <thead>
        <tr style="background: #e9ecef;">
            <th style="
                padding: 8px;
                border: 1px solid #ccc;
                text-align: left;
                font-size: 12px;
            ">
                Equipment
            </th>

            <th style="
                padding: 8px;
                border: 1px solid #ccc;
                text-align: left;
                font-size: 12px;
            ">
                Equipment No.
            </th>

            <th style="
                padding: 8px;
                border: 1px solid #ccc;
                text-align: left;
                font-size: 12px;
            ">
                Serial No.
            </th>

            <th style="
                padding: 8px;
                border: 1px solid #ccc;
                text-align: left;
                font-size: 12px;
            ">
                Due Date
            </th>

            <th style="
                padding: 8px;
                border: 1px solid #ccc;
                text-align: left;
                font-size: 12px;
            ">
                Status
            </th>
        </tr>
    </thead>

    <tbody>
        @foreach($equipmentItems as $equipment)
            @php
                $dueDate = $equipment->next_calibration_date
                    ? \Carbon\Carbon::parse(
                        $equipment->next_calibration_date
                    )
                    : null;

                $daysRemaining = $dueDate
                    ? now()->startOfDay()->diffInDays(
                        $dueDate->copy()->startOfDay(),
                        false
                    )
                    : null;

                $displayStatus = match (true) {
                    $daysRemaining === null =>
                        'Unknown',

                    $daysRemaining < 0 =>
                        abs($daysRemaining) . ' day(s) overdue',

                    $daysRemaining === 0 =>
                        'Due today',

                    default =>
                        'Due in ' . $daysRemaining . ' day(s)',
                };
            @endphp

            <tr>
                <td style="
                    padding: 8px;
                    border: 1px solid #ccc;
                    font-size: 12px;
                ">
                    <strong>
                        {{ $equipment->name ?? 'Unnamed Equipment' }}
                    </strong>

                    @if(!empty($equipment->model))
                        <br>

                        <span style="color: #777;">
                            Model: {{ $equipment->model }}
                        </span>
                    @endif
                </td>

                <td style="
                    padding: 8px;
                    border: 1px solid #ccc;
                    font-size: 12px;
                ">
                    {{ $equipment->equipment_number ?? 'N/A' }}
                </td>

                <td style="
                    padding: 8px;
                    border: 1px solid #ccc;
                    font-size: 12px;
                ">
                    {{ $equipment->serial_number ?? 'N/A' }}
                </td>

                <td style="
                    padding: 8px;
                    border: 1px solid #ccc;
                    font-size: 12px;
                ">
                    {{ $dueDate?->format('d/m/Y') ?? 'N/A' }}
                </td>

                <td style="
                    padding: 8px;
                    border: 1px solid #ccc;
                    font-size: 12px;
                ">
                    {{ $displayStatus }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
