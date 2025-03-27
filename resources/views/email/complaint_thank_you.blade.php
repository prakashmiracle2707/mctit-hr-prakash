<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Complaint Acknowledgement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 30px;
            color: #333;
        }

        .container {
            max-width: 650px;
            margin: auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.05);
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #0d6efd;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">

        <p>Hello <strong>{{ ucwords($complaint->employee->name) }}</strong>,</p>

        <p>Thank you for submitting your complaint <strong>(CMP#00{{ $complaint->id }})</strong>. We have received it and will get back to you shortly.</p>

        <p><strong>Title:</strong> {{ $complaint->title->name ?? '-' }}</p>
        <p><strong>Priority:</strong> {{ ucfirst($complaint->priority) }}</p>

        <p>You can track the status of your complaint at any time:</p>

        <a href="{{ route('complaints.index') }}">View Complaint</a>

        <br>
        <p>Best regards,<br><strong>{{ config('app.name') }} Support Team</strong></p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
