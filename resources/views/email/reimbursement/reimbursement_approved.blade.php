<!DOCTYPE html>
<html>
<head>
    <title>Approval for Reimbursement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
    </style>
</head>
<body>
    <p>Hello {{ ucwords($reimbursement->employee->name ?? '') }},</p>

    <p>I have reviewed your request for the reimbursement of office expenses dated {{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d/m/Y') }}, along with the submitted receipts and supporting documents.</p>

    <p>I am pleased to inform you that your reimbursement request for the total amount of ₹ {{ number_format($reimbursement->amount, 2) }} has been approved. <b>Mr. Nilesh Kalma(CFO)</b> has been notified and will process the reimbursement shortly.</p>

    @if($reimbursement->remark != "")
    <b>
        <ul>
            <li>{{ $reimbursement->remark ?? '' }}</li>
        </ul>
    </b>
    @endif

    <p>Please let me know if you have any questions or need further assistance.</p>

    <p>Best regards,</p>
    <p>{{ $reimbursement->assignedUser->name ?? '' }}</p>
</body>
</html>
