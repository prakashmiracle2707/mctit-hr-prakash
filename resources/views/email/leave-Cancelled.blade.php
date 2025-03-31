<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Cancellation Notification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border-radius: 6px;">

        <h4 style="color: #dc3545;">Leave Cancelled by {{ $employeeName }}</h4>

        <p>Hello Sir,</p>

        <p>This is to inform you that <strong>{{ $employeeName }}</strong> has cancelled their previously submitted leave request. Please find the details below:</p>

        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tr>
                <td style="padding: 8px;"><strong>Leave Type:</strong></td>
                <td style="padding: 8px;">{{ $leaveType }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Leave Dates:</strong></td>
                <td style="padding: 8px;">
                    {{ $startDate }}
                    @if($startDate != $endDate)
                        to {{ $endDate }}
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Reason for Cancellation:</strong></td>
                <td style="padding: 8px;">{{ $remark_cancelled }}</td>
            </tr>
        </table>

        <p>We apologize for any inconvenience caused and appreciate your understanding.</p>

        <p>Thank you,<br><strong>{{ $employeeName }}</strong></p>
    </div>
</body>
</html>
