<!DOCTYPE html>
<html>
<head>
    <title>Reminder: Follow-up on Approved Reimbursement – Payment Not Received</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            font-weight: bold;
        }
    </style>
</head>
    <body>
        <p>Hello Nilesh Kalma,</p>

        <p>I hope you are doing well.</p>

        <p>I am writing to follow up on my approved reimbursement request (Reference No: {{'#R00'.$reimbursement->id}}) submitted on {{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d/m/Y') }}. While the request has already been approved by the management and I have been informed that the payment has been processed from your end, I would like to inform you that I have not yet received the credited amount.</p>

        <p>Please find the details of the expenses below:</p>

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

        <p class="total">Total Amount: ₹ {{ number_format($reimbursement->amount, 2) }}</p>

        <p>I kindly request you to verify the transaction from your side and let me know if you need any information from my side to assist with the verification.</p>

        <p><strong>Click the link below to review the reimbursement request:</strong></p>

        <a href="{{ url('/reimbursements') }}" class="review-link">Review Request</a>

        <p>Thank you for your attention and support.</p>

        <p>Best regards,<br>
        {{ ucwords($reimbursement->employee->name ?? '') }}</p>
    </body>
</html>
