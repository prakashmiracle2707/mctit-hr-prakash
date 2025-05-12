<!DOCTYPE html>
<html>
<head>
    <title>Leave Request – Pending Director Approval</title>
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
            color: orange;
        }
    </style>
</head>
<body>
    <p>Hello {{ ucwords($employeeName ?? 'Employee') }},</p>

    <p>I hope you are doing well.</p>

    <p>Your leave request has been <strong>approved by your manager</strong> and is currently <span class="status">pending final approval from our Director</span>. Please find the summary of your request below:</p>

    <table>
        <thead>
            <tr>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total Days</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $leaveType ?? '' }}</td>
                <td>{{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</td>
                <td>{{ $total_leave_days}}</td>
                <td><strong class="status">Awaiting Director Approval</strong></td>
            </tr>
        </tbody>
    </table>

    @if(!empty($remark))
        <p><strong>Note:</strong> {{ $remark }}</p>
    @endif

    <p><strong>Approved By (Manager):</strong> {{ ucwords($approved_by_name ?? 'Manager') }}</p>

    <p>We will notify you once your leave is fully approved. Please refrain from making plans or changes until the final confirmation is received.</p>

    <p>Thank you for your patience.</p>

    <p>Best regards,<br>
    {{ config('app.name') }} HR Team</p>
</body>
</html>
