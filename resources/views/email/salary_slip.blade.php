<!DOCTYPE html>
<html>
<head>
    <title>Salary Slip - {{ $month }} {{ $year }}</title>
</head>
<body>
    <p>Hi {{ $employee->name }},</p>

    <p>Hope you are doing well.</p>

    <p>Please find the attached salary slip for the month of <strong>{{ strtoupper($month) }}-{{ $year }}</strong>.</p>

    <p>Let us know if there are any queries.</p>

    <p>Regards,</p>
    <p>HR Team</p>
</body>
</html>
