@php
    use Carbon\Carbon;

    // Parse as day/month/year
    $date = Carbon::createFromFormat('d/m/Y', $leaveDate);

    $isToday = $date->isToday();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Late Office Request - {{ $leaveDate }}</title>
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
        .button-container {
            margin-top: 20px;
            text-align: center;
        }
        .button-container a {
            display: inline-block;
            background-color: #008ECC;
            border-radius: 6px;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="content">
        <p>Hello Sir,</p>

        <p>I hope this message finds you well.</p>

        <p>
            I would like to inform you that I will be <strong>coming late to the office</strong> 
            on 
            <strong>
                {{ $isToday ? 'today' : \Carbon\Carbon::parse($leaveDate)->format('d/m/Y') }}
            </strong>.  
            
        </p>

        @if(!empty($leaveReason))
        <p>
            The reason for the delay is: <strong>{{ $leaveReason }}</strong>.
        </p>
        @endif

        <p>
            I will ensure to complete my assigned tasks for the day and make up for the lost time.
        </p>

        <br>
        <p>Thank you for your understanding.</p>
        <p>Regards,</p>
        <p class="signature">{{ $employeeName }}</p>
    </div>

    <div class="button-container">
        <a href="{{ route('leave.review', ['id' => $leaveId]) }}">
            Review Request
        </a>
    </div>
</div>

</body>
</html>
