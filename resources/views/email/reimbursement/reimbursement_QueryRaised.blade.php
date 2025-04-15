<!DOCTYPE html>
<html>
<head>
    <title>Reimbursement Request – Query Raised</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .content {
            margin-top: 15px;
        }
        .note {
            background-color: #fff3cd;
            padding: 10px;
            border: 1px solid #ffeeba;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <p class="header">Hello {{ ucwords($reimbursement->employee->name ?? '') }},</p>

    <p>Your reimbursement request titled <strong>{{ $reimbursement->title }}</strong>, submitted on 
    <strong>{{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d M Y') }}</strong> 
    has been reviewed by the accounts team and marked with the status: <strong>Query Raised</strong>.</p>

    @if (!empty($reimbursement->accountant_comment))
        <div class="note">
            <strong>Accountant's Query:</strong><br>
            {{ $reimbursement->accountant_comment }}
        </div>
    @endif

    <div class="content">
        <p>Please review the query raised and update your reimbursement request accordingly.</p>
        <p><strong>Click below to update your response:</strong></p>
        <p>
            <a href="{{ url('/reimbursements') }}" style="color: #007bff;">
                Update Reimbursement Request
            </a>
        </p>
    </div>

    <div class="footer">
        <p>Thank you,<br>Accounts Team</p>
    </div>
</body>
</html>
