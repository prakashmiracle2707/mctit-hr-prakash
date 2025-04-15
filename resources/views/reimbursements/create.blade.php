{{ Form::open(['route' => 'reimbursements.store', 'method' => 'POST', 'class' => 'needs-validation', 'novalidate', 'enctype' => 'multipart/form-data', 'id' => 'reimbursement-create-form']) }}
    <div class="modal-body">
        <div class="row">
            <!-- Title -->
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('title', __('Email Subject'), ['class' => 'col-form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('title', null, ['class' => 'form-control', 'required']) }}
                <div class="invalid-feedback">{{ __('Please enter a title.') }}</div>
            </div>

            <!-- Expense Date -->
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('expense_date', __('Expense Date'), ['class' => 'col-form-label']) }}<span class="text-danger">*</span>
                {{ Form::date('expense_date', null, ['class' => 'form-control', 'required']) }}
                <div class="invalid-feedback">{{ __('Please select an expense date.') }}</div>
            </div>

            <!-- Amount -->
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }}<span class="text-danger">*</span>
                {{ Form::number('amount', null, ['class' => 'form-control', 'required', 'min' => '0', 'step' => '0.01']) }}
                <div class="invalid-feedback">{{ __('Please enter a valid amount.') }}</div>
            </div>

            <!-- Description (Textarea) -->
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}<span class="text-danger">*</span>
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 5, 'required']) }}
                <div class="invalid-feedback">{{ __('Please enter a description.') }}</div>
            </div>

            <!-- Receipt Upload -->
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('file', __('Upload Receipt'), ['class' => 'col-form-label']) }}
                {{ Form::file('file', ['class' => 'form-control', 'accept' => 'image/*,application/pdf']) }}
                <small class="text-muted">{{ __('Accepted formats: PDF, JPG, PNG (Max: 2MB)') }}</small>
                 <!-- Self Receipt Checkbox -->
                <br />
                {{ Form::checkbox('self_receipt', 1, false, ['class' => 'form-check-input', 'id' => 'self_receipt']) }}
                    <label class="form-check-label" for="self_receipt">{{ __('Self-Generated Receipt') }}</label>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <a href="{{ route('reimbursements.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>

        <!-- Hidden Input to Detect Draft Submission -->
        <input type="hidden" name="status" id="status" value="Pending">

        <!-- Save as Draft Button -->
        <button type="submit" class="btn btn-danger" onclick="document.getElementById('status').value='Draft'">
            {{ __('Save as Draft') }}
        </button>

        <!-- Submit Request Button -->
        <button type="submit" class="btn btn-primary">{{ __('Apply Request') }}</button>
    </div>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function () {
        // On form submit
        $('#reimbursement-create-form').on('submit', function (e) {
            const status = $('#status').val(); // Will be 'Draft' or 'Pending'
            const file = $('input[name="file"]').val();

            // If NOT saving as draft and no file + not self receipt
            if (status !== 'Draft' && !file) {
                e.preventDefault();
                toastr.error('Please upload a receipt.');
                return false;
            }
        });
    });
</script>
{{ Form::close() }}
