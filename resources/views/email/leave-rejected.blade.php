<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request Rejected</title>
</head>
<body>

<p>Hello {{ $employeeName }},</p>

<p>We regret to inform you that your leave request for <strong>{{ $leaveDate }}</strong> has been rejected.</p>

@if($remark != '')
<p style="color:red;">Remark : <b>{{ $remark }} </b></p>
@endif

<p>Thank you</p>

</body>
</html>
