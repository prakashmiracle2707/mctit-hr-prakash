<!DOCTYPE html>
<html>
<head>
    <title>Thank You – Payment Received</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .content {
            margin-bottom: 20px;
        }
        .thank-you {
            color: green;
            font-weight: bold;
            font-size: 16px;
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

    <p class="thank-you">Thank you – Payment Received</p>

    <p>Hello Nilesh Kalma,</p>

    <p>I hope you are doing well.</p>

    <p>I would like to sincerely thank you for processing the reimbursement payment for my approved request (Reference No: <strong>{{'#R00'.$reimbursement->id}}</strong>). I confirm that the payment has been successfully received.</p>

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

    <p>Thank you again for your prompt support and assistance.</p>

    <p>Best regards,<br>
    {{ ucwords($reimbursement->employee->name ?? '') }}</p>

</body>
</html>
