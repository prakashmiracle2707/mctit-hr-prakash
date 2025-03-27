<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Complaint Resolved Notification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
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

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header img {
            max-height: 50px;
        }

        h2 {
            color: #0d6efd;
        }

        .button {
            display: inline-block;
            margin-top: 20px;
            background-color: #0d6efd;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
        }

        .footer {
            margin-top: 40px;
            font-size: 13px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Logo --}}
        <div class="header">
            <img src="https://mctit.appbuckets.com/storage/uploads/logo/dark_logo.png" alt="MCT Logo">
        </div>

        <p>Hello <strong>{{ ucwords($complaint->employee->name) }}</strong>,</p>

        <p>We would like to inform you that your complaint titled <strong>"{{ $complaint->title->name }}"</strong> has been marked as <strong>Resolved</strong>.</p>

        @if ($complaint->remark)
            <p><strong>Reviewer’s Remark: </strong>{{ $complaint->remark }}.</p>
        @endif

        <p>We kindly request you to review the resolution and confirm by closing the complaint if you are satisfied with the outcome.</p>

        <p>
            <a href="{{ route('complaints.index') }}">View Complaint</a>
        </p>

        <p>If you have any further concerns, please do not hesitate to contact the support team.</p>

        <br>
        <p>Best regards,<br><strong>{{ config('app.name') }} Support Team</strong></p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
