<!DOCTYPE html>
<html>
<head>
    <title>Request for Reimbursement</title>
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
        <p>Hello Sir,</p>
        
        <p>I hope this message finds you well.</p>

        <p>I am writing to submit a request for the reimbursement of office-related expenses incurred on {{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d/m/Y') }}. Please find the details of the expenses below:</p>

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

        <p>The supporting bills/invoices are attached for your reference. Kindly let me know if any additional information is required. I would appreciate it if the reimbursement could be processed at the earliest convenience.</p>

        <p><strong>Click the link below to review and process the reimbursement request:</strong></p>

        <a href="{{ url('/reimbursements') }}" class="review-link">Review & Approve Request</a>

        <p>Thank you for your support.</p>

        <p>Best regards,<br>
        {{ ucwords($reimbursement->employee->name ?? '') }}</p>
    </body>
</html>
