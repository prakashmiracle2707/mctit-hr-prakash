<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>IT-Ticket Acknowledgement</title>
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

        <p>Hello <strong>{{ ucwords($ticket->employee->name) }}</strong>,</p>

        <p>Thank you for submitting your IT-Ticket <strong>(TKT#000{{ $ticket->id }})</strong>. We have received it and will get back to you shortly.</p>

        <p><strong>Title:</strong> {{ $ticket->title->name ?? '-' }}</p>
        <p><strong>Priority:</strong> {{ ucfirst($ticket->priority) }}</p>

        <p>You can track the status of your ticket at any time:</p>

        <p><a href="{{ url('/it-tickets') }}">View IT-Ticket</a></p>

        <br>
        <p>Best regards,<br><strong>{{ config('app.name') }} Support Team</strong></p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
