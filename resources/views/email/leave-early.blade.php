<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><strong>Early Leave on {{ $leaveDate }}</strong></title>
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
        .button-container button {
            padding: 10px 20px;
            margin: 5px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }
        .approved {
            background-color: #4CAF50;
            color: white;
        }
        .reject {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="content">
        <p>Hello Sir,</p>

        <p>I hope this message finds you well. I would like to inform that I have to leave early on <strong>{{ $leaveDate }}</strong> at <strong>{{ $leaveTime }}</strong> due to <strong>{{ $leaveReason }}</strong>.</p>

        <p>
            I will ensure that all my tasks for the day are completed before I leave, and I’m happy to make up for any lost time if needed.
        </p>

        <p>Thank you for your understanding and support.</p>

        <br>

        <p>Thank You,</p>
        <p>Regards</p>
        <p class="signature">{{ $employeeName }}</p>
    </div>

    <div class="button-container">
        <a href="{{ route('leave.review', ['id' => $leaveId]) }}" 
           style="background-color: #008ECC;border-radius:6px; color: white; padding: 10px; text-decoration: none;">
           Review Leave
        </a>
    </div>
</div>

</body>
</html>