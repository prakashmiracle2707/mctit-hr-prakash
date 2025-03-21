<!DOCTYPE html>
<html>
<head>
    <title>Reimbursement Request – Not Approved</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .highlight {
            font-weight: bold;
            color: red;
        }
    </style>
</head>
<body>

    <p>Hello {{ ucwords($reimbursement->employee->name ?? '') }},</p>

    <p>Thank you for submitting your reimbursement request dated {{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d/m/Y') }} for the amount of ₹ {{ number_format($reimbursement->amount, 2) }}.</p>

    <p>After reviewing the details and supporting documents, we regret to inform you that the request <span class="highlight">cannot be approved</span> at this time due to the following reason(s):</p>

    @if($reimbursement->remark != "")
    <b>
        <ul>
            <li>{{ $reimbursement->remark ?? '' }}</li>
        </ul>
    </b>
    @endif

    <p>If you believe there has been an oversight or if you have additional documentation to support your claim, please feel free to reach out or resubmit the request with the necessary corrections.</p>

    <p>Thank you for your understanding.</p>

    <p>Best regards,</p>
    <p>{{ $reimbursement->assignedUser->name ?? '' }}</p>
</body>
</html>
