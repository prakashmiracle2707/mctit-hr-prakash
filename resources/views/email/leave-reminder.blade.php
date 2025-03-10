<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request Reminder</title>
</head>
<body>

<p>Hello Sir,</p>

<p>This is a reminder regarding the leave request submitted by {{ $employeeName }} for <strong>{{ $leaveDate }}</strong>. Kindly review the request at your earliest convenience.</p>

<p>Thank you for your attention to this matter.</p>
<p>Regards</p>
<p class="signature">{{ $employeeName }}</p>
</body>
</html>
