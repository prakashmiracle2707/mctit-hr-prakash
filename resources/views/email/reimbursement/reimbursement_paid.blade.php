<!DOCTYPE html>
<html>
<head>
    <title>Reimbursement Payment Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .highlight {
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>
    <p>Hello {{ ucwords($reimbursement->employee->name ?? '') }},</p>

    <p>This is to confirm that the reimbursement amount of <strong>₹ {{ number_format($reimbursement->amount, 2) }}</strong> 
        for your approved expense claim dated <strong>{{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d/m/Y') }}</strong> has been <span class="highlight">successfully paid</span> 
        via <strong>cash/online payment</strong> on <strong>{{ $reimbursement->paid_at ? $reimbursement->paid_at->format('Y-m-d h:i A') : 'Not Paid' }}</strong>.
    </p>

    <p>Kindly verify the payment and let us know if you face any issues.</p>

    <p>Thank you.</p>

    <p>Best regards,</p>
    <p>{{ $reimbursement->payer->name ?? 'N/A' }}</p>
</body>
</html>
