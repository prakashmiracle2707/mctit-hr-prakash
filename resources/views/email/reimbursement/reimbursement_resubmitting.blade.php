<!DOCTYPE html>
<html>
<head>
    <title>Resubmission of Reimbursement Request</title>
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

    <p>Following the query raised regarding my previous reimbursement request, I have made the necessary updates and am resubmitting the request for your kind review.</p>

    <p><strong>Reimbursement Details:</strong></p>

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
                <td>{{ $reimbursement->description ?? '-' }}</td>
                <td>₹ {{ number_format($reimbursement->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p class="total">Total Amount: ₹ {{ number_format($reimbursement->amount, 2) }}</p>

    @if(!empty($reimbursement->accountant_comment))
    <p><strong>Previous Comment from Accountant:</strong><br>
    "{{ $reimbursement->accountant_comment }}"</p>
    @endif

    <p>Updated supporting documents, if applicable, are attached for your reference.</p>

    <p>You may click the link below to review and process the resubmitted request:</p>

    <a href="{{ url('/reimbursements') }}" class="review-link">→ Review Reimbursement Request</a>

    <p>Thank you for your time and support.</p>

    <p>Warm regards,<br>
    {{ ucwords($reimbursement->employee->name ?? 'Employee') }}</p>
</body>
</html>
