{{ Form::model($reimbursement, ['route' => ['reimbursements.update', $reimbursement->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
    <div class="modal-body">
        <div class="row">
            <!-- Title -->
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('title', __('Email Subject'), ['class' => 'col-form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('title', null, ['class' => 'form-control', 'required']) }}
            </div>

            <!-- Expense Date -->
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('expense_date', __('Expense Date'), ['class' => 'col-form-label']) }}<span class="text-danger">*</span>
                {{ Form::date('expense_date', $reimbursement->expense_date, ['class' => 'form-control', 'required']) }}
            </div>

            <!-- Amount -->
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('amount', null, ['class' => 'form-control', 'required', 'min' => '0', 'step' => '0.01']) }}
            </div>

            <!-- Description -->
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}<span class="text-danger">*</span>
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 5, 'required']) }}
            </div>

            <!-- Remark -->
            <!-- <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('remark', __('Remark'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('remark', null, ['class' => 'form-control', 'rows' => 5]) }}
            </div> -->

            <!-- Receipt Upload -->
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('file_path', __('Upload Receipt'), ['class' => 'col-form-label']) }}
                {{ Form::file('file_path', ['class' => 'form-control', 'accept' => 'image/*,application/pdf']) }}
                <small class="text-muted">{{ __('Accepted formats: PDF, JPG, PNG (Max: 2MB)') }}</small>
                
                @if($reimbursement->file_path)
                    <p class="mt-2">
                        <a href="{{ asset('public/uploads/reimbursements/' . $reimbursement->file_path) }}" target="_blank" class="btn btn-primary btn-sm">
                            <i class="ti ti-eye"></i> {{ __('View Existing Receipt') }}
                        </a>
                    </p>
                @endif

                <div class="form-check mt-2">
                    {{ Form::checkbox('self_receipt', 1, $reimbursement->self_receipt ?? false, ['class' => 'form-check-input', 'id' => 'self_receipt']) }}
                    <label class="form-check-label" for="self_receipt">{{ __('Self-Generated Receipt') }}</label>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <a href="{{ route('reimbursements.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>

        <!-- Hidden Input to Detect Draft Submission -->
        <!-- <input type="hidden" name="status" id="status" value="Pending"> -->
        <input type="hidden" name="status" id="status" value="{{ $reimbursement->status }}">
        @if($reimbursement->status == 'Draft')
            <!-- Save as Draft Button (Only Visible If Current Status is Draft) -->
            <button type="submit" class="btn btn-danger" onclick="document.getElementById('status').value='Draft'">
                {{ __('Save as Draft') }}
            </button>
        @endif

        @if(in_array($reimbursement->status, ['Query_Raised']))
            <button type="submit" class="btn btn-danger" onclick="document.getElementById('status').value='Submitted'">
                {{ __('Submit Response') }}
            </button>
        @else
            <button type="submit" class="btn btn-primary" onclick="document.getElementById('status').value='Pending'">
                {{ __('Apply Request') }}
            </button>
        @endif
        
        <!-- <button type="submit" class="btn btn-primary" name="status" value="Pending">{{ __('Apply Request') }}</button> -->
        
    </div>
{{ Form::close() }}
