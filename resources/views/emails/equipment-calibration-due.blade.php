<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Equipment Calibration Due Report</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background: #f4f6f8;
    font-family: Arial, Helvetica, sans-serif;
    color: #333;
">
<table
    role="presentation"
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
    style="background: #f4f6f8; padding: 20px 0;"
>
    <tr>
        <td align="center">
            <table
                role="presentation"
                width="700"
                cellspacing="0"
                cellpadding="0"
                border="0"
                style="
                    width: 700px;
                    max-width: 95%;
                    background: #fff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
                "
            >
                <tr>
                    <td style="
                        padding: 22px;
                        background: #1a237e;
                        color: #fff;
                        text-align: center;
                    ">
                        <h1 style="
                            margin: 0;
                            font-size: 22px;
                        ">
                            Meter Central Laboratory
                        </h1>

                        <p style="
                            margin: 6px 0 0;
                            font-size: 14px;
                        ">
                            Equipment Calibration Due Report
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 24px;">
                        <p style="margin-top: 0;">
                            Dear Lab Manager,
                        </p>

                        <p>
                            The system has identified laboratory equipment that
                            requires calibration attention.
                        </p>

                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                            style="margin: 20px 0;"
                        >
                            <tr>
                                <td style="
                                    width: 33.33%;
                                    padding: 10px;
                                    text-align: center;
                                    background: #f8d7da;
                                    color: #842029;
                                    border: 4px solid #fff;
                                ">
                                    <strong style="
                                        display: block;
                                        font-size: 24px;
                                    ">
                                        {{ $overdueEquipment->count() }}
                                    </strong>

                                    Overdue
                                </td>

                                <td style="
                                    width: 33.33%;
                                    padding: 10px;
                                    text-align: center;
                                    background: #fff3cd;
                                    color: #664d03;
                                    border: 4px solid #fff;
                                ">
                                    <strong style="
                                        display: block;
                                        font-size: 24px;
                                    ">
                                        {{ $dueTodayEquipment->count() }}
                                    </strong>

                                    Due Today
                                </td>

                                <td style="
                                    width: 33.33%;
                                    padding: 10px;
                                    text-align: center;
                                    background: #cff4fc;
                                    color: #055160;
                                    border: 4px solid #fff;
                                ">
                                    <strong style="
                                        display: block;
                                        font-size: 24px;
                                    ">
                                        {{ $upcomingEquipment->count() }}
                                    </strong>

                                    Due Soon
                                </td>
                            </tr>
                        </table>

                        @if($overdueEquipment->isNotEmpty())
                            <h2 style="
                                margin-top: 26px;
                                color: #842029;
                                font-size: 18px;
                            ">
                                Overdue Equipment
                            </h2>

                            @include(
                                'emails.partials.equipment-calibration-table',
                                [
                                    'equipmentItems' => $overdueEquipment,
                                    'statusLabel' => 'Overdue',
                                ]
                            )
                        @endif

                        @if($dueTodayEquipment->isNotEmpty())
                            <h2 style="
                                margin-top: 26px;
                                color: #664d03;
                                font-size: 18px;
                            ">
                                Equipment Due Today
                            </h2>

                            @include(
                                'emails.partials.equipment-calibration-table',
                                [
                                    'equipmentItems' => $dueTodayEquipment,
                                    'statusLabel' => 'Due Today',
                                ]
                            )
                        @endif

                        @if($upcomingEquipment->isNotEmpty())
                            <h2 style="
                                margin-top: 26px;
                                color: #055160;
                                font-size: 18px;
                            ">
                                Equipment Due Within 30 Days
                            </h2>

                            @include(
                                'emails.partials.equipment-calibration-table',
                                [
                                    'equipmentItems' => $upcomingEquipment,
                                    'statusLabel' => 'Due Soon',
                                ]
                            )
                        @endif

                        <div style="
                            margin-top: 24px;
                            padding: 14px;
                            border-left: 4px solid #1a237e;
                            background: #f4f6f8;
                        ">
                            <strong>Required action:</strong>

                            Review the affected equipment and schedule calibration,
                            withdrawal from service, or another appropriate action.
                        </div>

                        @if(Route::has('equipment.index'))
                            <p style="
                                margin: 24px 0;
                                text-align: center;
                            ">
                                <a
                                    href="{{ route('equipment.index') }}"
                                    style="
                                        display: inline-block;
                                        padding: 11px 20px;
                                        border-radius: 5px;
                                        background: #1a237e;
                                        color: #fff;
                                        text-decoration: none;
                                        font-weight: bold;
                                    "
                                >
                                    Review Equipment
                                </a>
                            </p>
                        @endif

                        <p>
                            Regards,<br>
                            <strong>Meter Central Laboratory System</strong>
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="
                        padding: 14px 20px;
                        background: #f4f6f8;
                        color: #777;
                        text-align: center;
                        font-size: 12px;
                    ">
                        This is an automatically generated notification.
                        Please do not reply to this email.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
