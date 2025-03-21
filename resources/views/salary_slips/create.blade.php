{{ Form::open(['route' => 'salary_slips.preview', 'method' => 'POST', 'class' => 'needs-validation', 'novalidate', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <!-- Month Selection -->
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('month', __('Month'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::select('month', [
                'January' => 'January', 'February' => 'February', 'March' => 'March', 'April' => 'April', 
                'May' => 'May', 'June' => 'June', 'July' => 'July', 'August' => 'August',
                'September' => 'September', 'October' => 'October', 'November' => 'November', 'December' => 'December'
            ], null, ['class' => 'form-control select2', 'required']) }}
        </div>

        <!-- Year Selection -->
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('year', __('Year'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::number('year', date('Y'), ['class' => 'form-control', 'required', 'min' => '2000', 'max' => date('Y')]) }}
        </div>

        <!-- Bulk Salary Slip Upload -->
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('salary_slips[]', __('Upload Salary Slips (Multiple)'), ['class' => 'col-form-label']) }}
            {{ Form::file('salary_slips[]', ['class' => 'form-control', 'accept' => 'image/*,application/pdf', 'multiple', 'required']) }}
            <small class="text-muted">{{ __('Ensure that filenames follow the pattern: EmployeeName_Month-Year.pdf (e.g., Arwa_JAN-25.pdf)') }}</small>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Preview') }}" class="btn btn-primary">
</div>
{{ Form::close() }}