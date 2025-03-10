<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Approved</title>
</head>
<body>

<p>Hello {{ $employeeName }},</p>

<p>Your leave request for <strong>{{ $leaveDate }}</strong> has been approved.</p>

@if($remark != '')
<p style="color:green;">Remark : <b>{{ $remark }} </b></p>
@endif

<p>Thank You</p>

</body>
</html>
