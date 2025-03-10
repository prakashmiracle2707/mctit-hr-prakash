<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .content {
            font-size: 16px;
            color: #333;
            line-height: 1.6;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
            text-align: center;
        }
        .signature {
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="content">
        <p>Hello Sir,</p>

        <p>I hope you are doing well.</p>

        <p>Requesting you to <b>{{ $leaveType }} </b> on <b>{{ $leaveDate }} </b> as {{ $leaveReason }}</p>

        <p>So requesting you to kindly approve my application for the above mentioned days.</p>

        <p>I will be highly obliged,</p>

        <p>Thanking You,</p>
        <p>Regards</p>
        <p class="signature">{{ $employeeName }}</p>
    </div>
</div>

</body>
</html>
