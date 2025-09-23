<!DOCTYPE html>
<html>
<head>
    <title>Leave Request – Rejected</title>
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

    <p>We regret to inform you that your <strong>leave request</strong> has been <span class="status">rejected</span> after review.</p>

    <p>Below are the details of your request for your reference:</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
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
                <td>{{ $leaveId ?? '' }}</td>
                <td>{{ $leaveType ?? '' }}</td>
                <td>{{ $leaveReason ?? '' }}</td>
                @if($start_date == $end_date)
                <td>{{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</td>
                @else
                <td>{{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</td>
                @endif
                <td>{{ $total_leave_days }}</td>
                <td><strong class="status">Rejected</strong></td>
            </tr>
        </tbody>
    </table>

    @if(!empty($remark))
        <p><strong>Reason for Rejection:</strong> {{ $remark }}</p>
    @endif

    <p><strong>Reviewed By:</strong> Ravi Brahmbhatt</p>

    <p>We understand this may be disappointing. You may reach out to your supervisor or HR team for clarification or to discuss alternative arrangements if necessary.</p>

    <p>Thank you for your understanding.</p>

    <p>Best regards,<br>
    {{ config('app.name') }} HR Team</p>
</body>
</html>
