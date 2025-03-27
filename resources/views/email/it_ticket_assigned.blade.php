<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New IT Ticket Assigned - [Ticket TKT#000{{ $ticket->id }}]</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 6px;">

        <!-- Logo -->
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://mctit.appbuckets.com//storage/uploads/logo/dark_logo.png" alt="MCT Logo" style="max-height: 50px;">
        </div>

        <!-- <h2 style="color: #007bff;">New IT Ticket Assigned</h2> -->

        <p><b>Hello Krupesh Thakkar,</b></p>

        <p>You have been assigned a new IT ticket. Please review the details below and take necessary action:</p>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px;"><strong>Ticket Number:</strong></td>
                <td style="padding: 8px;">TKT#000{{ $ticket->id }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Employee:</strong></td>
                <td style="padding: 8px;">{{ ucwords($ticket->employee->name) ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Category:</strong></td>
                <td style="padding: 8px;">{{ $ticket->category->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Title:</strong></td>
                <td style="padding: 8px;">{{ $ticket->title->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Description:</strong></td>
                <td style="padding: 8px;">{{ $ticket->description }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Priority:</strong></td>
                <td style="padding: 8px;">
                    @if ($ticket->priority == 'Medium')
                        <span style="background-color: #ffc107; color: #000; padding: 5px 10px; border-radius: 4px; font-weight: bold;">
                            {{ strtoupper($ticket->priority) }}
                        </span>
                    @elseif($ticket->priority == 'Low')
                        <span style="background-color: #28a745; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold;">
                            {{ strtoupper($ticket->priority) }}
                        </span>
                    @elseif($ticket->priority == "High")
                        <span style="background-color: #dc3545; color: #fff; padding: 5px 10px; border-radius: 4px; font-weight: bold;">
                            {{ strtoupper($ticket->priority) }}
                        </span>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Date Submitted:</strong></td>
                <td style="padding: 8px;">{{ $ticket->created_at->format('d/m/Y h:i A') }}</td>
            </tr>
        </table>

        <p>You can manage the ticket by logging into the help desk system:</p>
        <p><a href="{{ url('/it-tickets') }}">View IT-Ticket</a></p>

        <br>
        <p>Regards,</p>
        <p>IT Help Desk<br>{{ config('app.name') }}</p>
    </div>
</body>
</html>
