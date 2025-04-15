{{ Form::open(['url' => 'reimbursements/changeaction', 'method' => 'post',  'enctype' => 'multipart/form-data', 'id' => 'reimbursement-action-form']) }}

<style>
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    .custom-table th, .custom-table td {
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        text-align: left;
    }

    .custom-table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .badge-status {
        padding: 5px 10px;
        font-size: 14px;
        border-radius: 5px;
        font-weight: bold;
    }

    .status-paid {
        background-color: #28a745;
        color: white;
    }

    .btn-view {
        background-color: #007bff;
        color: white;
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 14px;
        text-decoration: none;
    }

    .btn-view:hover {
        background-color: #0056b3;
    }

</style>

<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="custom-table">
                <tr>
                    <th>{{ __('Subject') }}</th>
                    <td colspan="3">{{ $reimbursement->title ?? '' }}</td>
                </tr>

                <tr>
                    <th>{{ __('Employee') }}</th>
                    <td>{{ ucfirst($reimbursement->employee->name ?? '') }}</td>
                    <th>{{ __('Amount') }}</th>
                    <td>&#x20B9; {{ number_format($reimbursement->amount, 2) }}</td>
                </tr>

                <tr>
                    <th>{{ __('Expense Date') }}</th>
                    <td>{{ \Carbon\Carbon::parse($reimbursement->expense_date)->format('d/m/Y') }}</td>
                    <th>{{ __('Status') }}</th>
                    <td>
                        @if ($reimbursement->status == 'Pending')
                            <div class="badge bg-warning p-2 px-3 ">{{ $reimbursement->status }}</div>
                        @elseif($reimbursement->status == 'Approved')
                            <div class="badge bg-success p-2 px-3 ">{{ $reimbursement->status }}</div>
                        @elseif($reimbursement->status == "Reject")
                            <div class="badge bg-danger p-2 px-3 ">{{ $reimbursement->status }}</div>
                        @elseif($reimbursement->status == "Draft")
                            <div class="badge bg-info p-2 px-3 ">{{ $reimbursement->status }}</div>
                        @elseif($reimbursement->status == "Paid")
                            <div class="badge p-2 px-3" style="background: green;">{{ $reimbursement->status }}</div>
                        @elseif($reimbursement->status == "Not_Received")
                            <div class="badge bg-danger p-2 px-3 ">Not Received</div>
                        @elseif($reimbursement->status == "Received")
                            <div class="badge bg-success p-2 px-3 ">Received</div>
                        @elseif($reimbursement->status == "Query_Raised")
                            <div class="badge bg-warning p-2 px-3 ">Query Raised</div>
                        @elseif($reimbursement->status == "Submitted")
                            <div class="badge bg-success p-2 px-3 ">Submitted</div>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>{{ __('Approved By') }}</th>
                    <td>{{ $reimbursement->assignedUser->name ?? 'Not Approved' }}</td>
                    <th>{{ __('Approved At') }}</th>
                    <td>{{ $reimbursement->approved_at ? $reimbursement->approved_at->format('d/m/Y h:i A') : 'Not Approved' }}</td>
                </tr>

                <tr>
                    <th>{{ __('Paid By') }}</th>
                    <td>{{ $reimbursement->payer->name ?? 'N/A' }}</td>
                    <th>{{ __('Paid At') }}</th>
                    <td>{{ $reimbursement->paid_at ? $reimbursement->paid_at->format('Y/m/d h:i A') : 'Not Paid' }}</td>
                </tr>

                <tr>
                    <th>{{ __('Receipt') }}  @if($reimbursement->self_receipt) <span style="color: #ff3a6e;"> (Self)</span> @endif</th>
                    <td>
                        @if ($reimbursement->file_path)
                            <a href="{{ asset('public/uploads/reimbursements/' . $reimbursement->file_path) }}" target="_blank" class="btn-view">
                                <i class="ti ti-eye"></i> {{ __('View') }}
                            </a>
                        @endif
                    </td>
                    <th>{{ __('Created On') }}</th>
                    <td>{{ \Carbon\Carbon::parse($reimbursement->created_at)->format('d/m/Y') }}</td>
                </tr>

                <tr>
                    <th>{{ __('Description') }}</th>
                    <td colspan="3">{{ $reimbursement->description ?? '' }}</td>
                </tr>

                @if ($reimbursement->status != 'Draft')
                    <tr>
                        <th>{{ __('Remark') }}</th>
                        <td colspan="3">
                            @if (Auth::user()->type == 'CEO' && in_array($reimbursement->status, ['Pending', 'Approved', 'Reject']))
                                {{ Form::textarea('remark', $reimbursement->remark, ['class' => 'form-control', 'placeholder' => __('Reimbursement Remark'), 'rows' => '3']) }}
                            @else
                                {{ $reimbursement->remark ?? '' }}
                            @endif
                        </td>
                    </tr>
                @endif

                @if($reimbursement->accountant_comment != '')
                    <tr>
                        <th>{{ __('Accountant\'s Query') }}</th>
                        <td colspan="3">
                            <span style="color:red;">{{ $reimbursement->accountant_comment ?? '' }}</span>
                        </td>
                    </tr>
                @endif

                @if (Auth::user()->type == 'management' && in_array($reimbursement->status, ['Approved','Not_Received','Submitted']))

                    <tr>
                        <th>{{ __('Status') }}</th>
                        <td colspan="3">
                            {{ Form::select('status', ['Query Raised' => 'Query Raised', 'Mark as Paid' => 'Mark as Paid'], null, [
                                'class' => 'form-control',
                                'placeholder' => 'Select Status',
                                'id' => 'status_selector',
                                'required'
                            ]) }}
                        </td>
                    </tr>

                    {{-- Payment Type & Receipt Upload (only for Mark as Paid) --}}
                    <tr id="payment_type_row" style="display: none;">
                        <th>{{ __('Payment Type') }}</th>
                        <td colspan="3">
                            {{ Form::select('payment_type', ['Cash' => 'Cash', 'Online' => 'Online'], $reimbursement->payment_type ?? null, [
                                'class' => 'form-control',
                                'id' => 'payment_type',
                                'placeholder' => 'Select Payment Type'
                            ]) }}
                        </td>
                    </tr>

                    <tr id="receipt_upload_row" style="display: none;">
                        <th>{{ __('Upload Paid Receipt') }}</th>
                        <td colspan="3">
                            {{ Form::file('paid_receipt', ['class' => 'form-control', 'id' => 'paid_receipt_input', 'accept' => 'image/*,application/pdf']) }}
                            <small class="text-muted">{{ __('Accepted formats: PDF, JPG, PNG (Max: 2MB)') }}</small>
                            @if ($reimbursement->paid_receipt)
                                <br><br>
                                <a href="{{ asset('public/uploads/reimbursements/' . $reimbursement->paid_receipt) }}" target="_blank" class="btn-view">
                                    <i class="ti ti-eye"></i> {{ __('View Uploaded Receipt') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                    {{-- Accountant Query (only for Query Raised) --}}
                    <tr id="accountant_query_row" style="display: none;">
                        <th>{{ __('Accountant\'s Query') }}</th>
                        <td colspan="3">
                            {{ Form::textarea('accountant_comment', $reimbursement->accountant_comment ?? null, [
                                'class' => 'form-control',
                                'rows' => 3,
                                'id' => 'accountant_comment',
                                'placeholder' => 'Enter any comments or query from the accountant'
                            ]) }}
                        </td>
                    </tr>
                @endif

                @if (in_array($reimbursement->status, ['Paid','Received','Not_Received']))
                    @if($reimbursement->payment_type)
                        <tr>
                            <th>{{ __('Payment Type') }}</th>
                            <td colspan="3">
                                {{$reimbursement->payment_type}}
                            </td>
                        </tr>
                    @endif
                    
                    @if($reimbursement->paid_receipt)
                        <tr>
                            <th>{{ __('Paid Receipt') }}</th>
                            <td colspan="3">
                                    @if ($reimbursement->paid_receipt)
                                        <a href="{{ asset('public/uploads/reimbursements/' . $reimbursement->paid_receipt) }}" target="_blank" class="btn-view">
                                            <i class="ti ti-eye"></i> {{ __('View Paid Receipt') }}
                                        </a>
                                    @endif
                            </td>
                        </tr>
                    @endif 
                @endif

                <input type="hidden" name="reimbursement_id" value="{{ $reimbursement->id }}">
                <input type="hidden" name="reimbursement_page" value="index">
            </table>
        </div>
    </div>
</div>

<div class="modal-footer">
    @if (Auth::user()->type == 'CEO' && in_array($reimbursement->status, ['Pending', 'Approved', 'Reject']))
        <input type="submit" value="{{ __('Approved') }}" class="btn btn-success rounded" name="status">
        <input type="submit" value="{{ __('Reject') }}" class="btn btn-danger rounded" name="status">
    @endif

    @if (Auth::user()->type == 'management' && ($reimbursement->status == 'Approved' || $reimbursement->status == 'Not_Received' || $reimbursement->status == 'Submitted'))
        <input type="submit" value="{{ __('Query Raised') }}" class="btn btn-danger rounded" name="status" id="btn_query_raised" style="display: none;">
        <input type="submit" value="{{ __('Mark as Paid') }}" class="btn btn-info rounded" name="status" id="btn_mark_paid" style="display: none;">
    @endif

    @if (Auth::user()->type == 'employee' && $reimbursement->status == 'Paid')
    <input type="submit" value="{{ __('Yes Received') }}" class="btn btn-success rounded" name="status">
    <input type="submit" value="{{ __('Not Received') }}" class="btn btn-danger rounded" name="status">
    @endif

    @php
        $createdDate = \Carbon\Carbon::parse($reimbursement->created_at);
        $fifteenDaysPassed = $createdDate->diffInDays(now()) >= 15;
    @endphp

    @if (Auth::user()->type == 'employee' && $reimbursement->status == 'Pending' && $reimbursement->follow_up_email == 0 && $fifteenDaysPassed)
        <input type="submit" value="{{ __('Send Follow Up Email') }}" class="btn btn-danger rounded" name="status">
    @endif
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function () {
        function toggleFieldsAndButtons() {
            let selectedStatus = $('#status_selector').val();

            // Hide everything first
            $('#accountant_query_row').hide();
            $('#payment_type_row').hide();
            $('#receipt_upload_row').hide();
            $('#btn_query_raised').hide();
            $('#btn_mark_paid').hide();

            if (selectedStatus === 'Query Raised') {
                $('#accountant_query_row').show();
                $('#btn_query_raised').show();
            } else if (selectedStatus === 'Mark as Paid') {
                $('#payment_type_row').show();
                $('#receipt_upload_row').show();
                $('#btn_mark_paid').show();
                $('#accountant_query_row').show();
            }
        }

        // Run on change
        $('#status_selector').on('change', toggleFieldsAndButtons);

        // Run on page load
        toggleFieldsAndButtons();


        // Form submit validation (optional double-check)
        $('#reimbursement-action-form').on('submit', function (e) {
            const paymentType = $('#payment_type').val();
            const fileInput = $('#paid_receipt_input').val();
            const status_selector = $('#status_selector').val();
            const comment = $('#accountant_comment').val();
            
            if(status_selector === 'Mark as Paid'){

                // Check if both fields are empty
                if ((paymentType === '' || paymentType === null) && fileInput === '') {
                    toastr.error('Please select Payment Type and upload Paid Receipt.');
                    e.preventDefault();
                    return false;
                }

                @if ($reimbursement->paid_receipt == '' || $reimbursement->paid_receipt == null)
                    if (paymentType === 'Online' && fileInput === '') {
                        toastr.error('Please upload the Paid Receipt when payment type is Online.');
                        e.preventDefault();
                        return false;
                    }
                @endif
            }


            if (status_selector === 'Query Raised' && comment.trim() === '') {
                toastr.error('Please enter the accountant\'s query comment.');
                e.preventDefault();
                return false;
            }

            
        });
    });
</script>

{{ Form::close() }}
