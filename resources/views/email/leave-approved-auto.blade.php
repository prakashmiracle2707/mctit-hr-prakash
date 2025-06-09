<!DOCTYPE html>
<html>
<head>
    <title>Leave Request – Approved</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .status {
            font-weight: bold;
            color: green;
        }

        .status-blue {
            font-weight: bold;
            color: blue;
        }
    </style>
</head>
<body>
    <p>Hello {{ ucwords($employeeName ?? 'Employee') }},</p>

    <p>We are pleased to inform you that your <strong>leave request</strong> has been <span class="status">approved</span> <strong class="status-blue">by the system automatically. </strong></p>

    <p>Below are the approved details of your request:</p>

    <table>
        <thead>
            <tr>
                <th>Leave Type</th>
                <th>Reason</th>
                @if($start_date == $end_date)
                    <th>Date</th>
                @else
                    <th>Start Date</th>
                    <th>End Date</th>
                @endif
                <th>Total Days</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $leaveType ?? '' }}</td>
                <td>{{ $leaveReason ?? '' }}</td>
                @if($start_date == $end_date)
                    <td>{{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</td>
                @else
                    <td>{{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</td>
                @endif
                <td>{{ $total_leave_days }}</td>
                <td><strong class="status">Approved</strong></td>
            </tr>
        </tbody>
    </table>

    @if(!empty($remark))
        <p><strong>Director's Note:</strong> {{ $remark }}</p>
    @endif

    <p><strong>Approved By:</strong> <strong class="status-blue">System – Auto Approved</strong></p>

    <p>You may now proceed with your plans as per the approved dates. We wish you a restful and enjoyable leave.</p>

    <p>Best regards,<br>
    {{ config('app.name') }} HR Team</p>
</body>
</html>
