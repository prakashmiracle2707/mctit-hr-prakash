<!DOCTYPE html>
<html>
<head>
    <title>Follow-Up: Reimbursement Approval Pending</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .content {
            margin-bottom: 20px;
        }
        .highlight {
            font-weight: bold;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f8f8f8;
        }
        
    </style>
</head>
<body>

    <p>Hello Sir,</p>

    <p>I hope this message finds you well.</p>

    <p>
        I am writing to follow up on my reimbursement request 
        <span class="highlight">(Reference No: {{ '#R00'.$reimbursement->id }})</span> 
        submitted on <span class="highlight">{{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d/m/Y') }}</span>. 
        The request is still pending your approval.
    </p>

    <p>
        I would sincerely appreciate it if you could kindly review and approve the request 
        at your earliest convenience so it can proceed for payment. 
        If there is any additional information required from my side, please feel free to let me know.
    </p>

    <p>Here are the details of the reimbursement for your reference:</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d/m/Y') }}</td>
                <td>{{ $reimbursement->description ?? '' }}</td>
                <td>₹ {{ number_format($reimbursement->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p>
        <strong>Click the link below to review and take action on the reimbursement request:</strong><br>
        <a href="{{ url('/reimbursements') }}" class="link">Review Reimbursement Request</a>
    </p>

    <p>Thank you very much for your support and understanding.</p>

    <p>Best regards,<br>
    {{ ucwords($reimbursement->employee->name ?? '') }}</p>

</body>
</html>
