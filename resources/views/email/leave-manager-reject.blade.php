<!DOCTYPE html>
<html>
<head>
    <title>Leave Request – Rejected by Manager</title>
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
            color: red;
        }
    </style>
</head>
<body>
    <p>Hello {{ ucwords($employeeName ?? 'Employee') }},</p>

    <p>I hope you are doing well.</p>

    <p>We regret to inform you that your leave request has been <strong class="status">rejected by your manager</strong>. Below are the details of your request:</p>

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
                <td>{{ $total_leave_days }}</td>
                <td><strong class="status">Rejected by Manager</strong></td>
            </tr>
        </tbody>
    </table>

    @if(!empty($remark))
        <p><strong>Rejection Reason:</strong> {{ $remark }}</p>
    @endif

    <p>If you believe this was made in error or require clarification, you may contact your manager or HR for further discussion. You are also welcome to resubmit the request with any necessary changes.</p>

    <p>Thank you for your understanding.</p>

    <p>Best regards,<br>
    {{ config('app.name') }} HR Team</p>
</body>
</html>
