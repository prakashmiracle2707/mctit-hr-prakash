<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Complaint Assigned - [Complaint #{{ $complaint->id }}]</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 6px;">

        <!-- Logo -->
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://mctit.appbuckets.com//storage/uploads/logo/dark_logo.png" alt="MCT Logo" style="max-height: 50px;">
        </div>

        <!-- <h2 style="color: #007bff;">New Complaint Assigned</h2> -->

        <p><b>Hello Janki Desai,</b></p>

        <p>A new complaint has been assigned to you. Please review the details below and take the necessary action:</p>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px;"><strong>Complaint Number:</strong></td>
                <td style="padding: 8px;">CMP#000{{ $complaint->id }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Employee:</strong></td>
                <td style="padding: 8px;">{{ ucwords($complaint->employee->name) ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Category:</strong></td>
                <td style="padding: 8px;">{{ $complaint->category->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Title:</strong></td>
                <td style="padding: 8px;">{{ $complaint->title->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Priority:</strong></td>
                <td style="padding: 8px;">
                    @if ($complaint->priority == 'Medium')
                        <span style="background-color: #ffa21d; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold;">
                            {{ strtoupper($complaint->priority) }}
                        </span>
                    @elseif($complaint->priority == 'Low')
                        <span style="background-color: #6fd943; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold;">
                            {{ strtoupper($complaint->priority) }}
                        </span>
                    @elseif($complaint->priority == "High")
                        <span style="background-color: #ff3a6e; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold;">
                            {{ strtoupper($complaint->priority) }}
                        </span>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Description:</strong></td>
                <td style="padding: 8px;">{{ $complaint->description }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Date Submitted:</strong></td>
                <td style="padding: 8px;">{{ $complaint->created_at->format('d/m/Y h:i A') }}</td>
            </tr>
        </table>

        <p>You can manage the complaint by logging into the system:</p>
        <p><a href="{{ url('/complaints') }}">View Complaint</a></p>

        <br>
        <p>Best regards,<br><strong>{{ config('app.name') }} Support Team</strong></p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
