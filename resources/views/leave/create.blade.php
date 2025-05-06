@php
    $setting = App\Models\Utility::settings();
    $chatgpt = App\Models\Utility::getValByName('enable_chatgpt');
@endphp
{{ Form::open(['url' => 'leave', 'method' => 'post', 'class' => 'needs-validation', 'novalidate', 'id' => 'leave-form']) }}
<div class="modal-body">

    @if ($chatgpt == 'on')
        <div class="card-footer text-end">
            <a href="#" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true"
                data-url="{{ route('generate', ['leave']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
                <i class="fas fa-robot"></i>{{ __(' Generate With AI') }}
            </a>
        </div>
    @endif

    @if (\Auth::user()->type != 'employee')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}<x-required></x-required>
                    {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
                </div>
            </div>
        </div>
    @else
        {{-- @foreach ($employees as $employee) --}}
        {!! Form::hidden('employee_id', !empty($employees) ? $employees->id : 0, ['id' => 'employee_id']) !!}
        {{-- @endforeach --}}
    @endif
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('leave_type_id', __('Leave Type'), ['class' => 'col-form-label']) }}<x-required></x-required>
                <select name="leave_type_id" id="leave_type_id" class="form-control select" required>
                    <option value="">{{ __('Select Leave Type') }}</option>
                    @foreach ($leavetypes as $leave)
                        <option value="{{ $leave->id }}">{{ $leave->title }} (<span class="float-right pr-5">
                                {{ $leave->days }}</span>)</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6" id="half_day_type_div">
            <div class="form-group">
                {{ Form::label('half_day_type', __('Leave(Full/Half Day)'), ['class' => 'col-form-label']) }}
                <select name="half_day_type" id="half_day_type" class="form-control" required>
                    <option value="full_day">{{ __('Full Day') }}</option>
                    <option value="morning">{{ __('First Half (Morning)') }}</option>
                    <option value="afternoon">{{ __('Second Half (Afternoon)') }}</option>
                </select>
            </div>
        </div>

        

        <div class="col-md-6" id="time_dropdown_wrapper" style="display: none;">
            <div class="form-group">
                {{ Form::label('leave_time', __('Select Time'), ['class' => 'col-form-label']) }}
                <select name="leave_time" id="leave_time" class="form-control">
                    <option value="">{{ __('Select Time') }}</option>
                    <option value="4:00 PM">4:00 PM</option>
                    <option value="4:15 PM">4:15 PM</option>
                    <option value="4:30 PM">4:30 PM</option>
                    <option value="4:30 PM">4:45 PM</option>
                    <option value="5:00 PM">5:00 PM</option>
                    <option value="4:15 PM">5:15 PM</option>
                    <option value="4:30 PM">5:30 PM</option>
                    <option value="4:30 PM">5:45 PM</option>
                    <option value="6:00 PM">6:00 PM</option>
                </select>
                <span style="color:#ff3a6e;font-size: 11px;"><b>Note :</b>Only one Early Leave is allowed per month. Must complete 8 hours. Applying on Same day is restricted.</span>
            </div>
        </div>
    </div>

    <!-- <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('approver', __('To (Approver)'), ['class' => 'col-form-label']) }}
                <input type="text" class="form-control" id="approver" name="approver" value="Ravi Brahmbhatt" disabled>
            </div>
        </div>
    </div> -->
    <div class="row">
        <!-- <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('managers[]', __('Managers'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::select('managers[]', 
                    $managerList, 
                    old('managers', $defaultManagerList), 
                    ['class' => 'form-control select2', 'multiple' => 'multiple', 'id' => 'managers']
                ) }}
                <small class="text-muted">{{ __('You can select multiple reporting managers.') }}</small>
            </div>
        </div> -->
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('approver', __('To (Approver)'), ['class' => 'col-form-label']) }}
                <input type="text" class="form-control" id="approver" name="approver" value="Ravi Brahmbhatt" disabled>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('cc_email', __('CC Email'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::select('cc_email_id[]', 
                    $employeesList->pluck('name', 'id'),
                    $employeesList->whereIn('id', [2])->pluck('id')->toArray(),
                    ['class' => 'form-control select2', 'id' => 'cc_email_id', 'multiple' => 'multiple']
                ) }}
                <span style="color:#6f42c1;font-size: 11px;"><b>Note :</b>Nilesh Kalma is added by default to cc.</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('start_date', null, ['class' => 'form-control d_week current_date', 'autocomplete' => 'off', 'placeholder' => 'Select start date', 'id' => 'start_date']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('end_date', null, ['class' => 'form-control d_week current_date', 'autocomplete' => 'off', 'placeholder' => 'Select end date', 'id' => 'end_date']) }}
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_reason', __('Leave Reason'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::textarea('leave_reason', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Leave Reason'), 'rows' => '3']) }}
            </div>
        </div>
    </div>

    @if (Auth::user()->type != 'employee')
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remark'), ['class' => 'col-form-label']) }}
                @if ($chatgpt == 'on')
                    <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true"
                        id="grammarCheck" data-url="{{ route('grammar', ['grammar']) }}" data-bs-placement="top"
                        data-title="{{ __('Grammar check with AI') }}">
                        <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                    </a>
                @endif
                {{ Form::textarea('remark', null, ['class' => 'form-control grammer_textarea', 'placeholder' => __('Leave Remark'), 'rows' => '3']) }}
            </div>
        </div>
    </div>
    @endif

    @if (isset($setting['is_enabled']) && $setting['is_enabled'] == 'on')
        <div class="form-group col-md-6">
            {{ Form::label('synchronize_type', __('Synchroniz in Google Calendar ?'), ['class' => 'form-label']) }}
            <div class=" form-switch">
                <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow"
                    value="google_calender">
                <label class="form-check-label" for="switch-shadow"></label>
            </div>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>

    @if (Auth::user()->type != 'employee')
        <input type="submit" value="{{ __('Create') }}" id="apply-btn"  class="btn  btn-primary">
    @else
        <input type="submit" value="{{ __('Apply') }}"  id="apply-btn" class="btn  btn-primary">
        <button type="button" class="btn btn-danger" id="save-draft-btn">{{ __('Save as Draft') }}</button>
    @endif
    
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        var now = new Date();
        var month = (now.getMonth() + 1);
        var day = now.getDate();
        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;
        var today = now.getFullYear() + '-' + month + '-' + day;
        $('.current_date').val(today);
    });
</script>

<script>
    $(document).ready(function() {
        setTimeout(() => {
            var employee_id = $('#employee_id').val();
            if (employee_id) {
                $('#employee_id').trigger('change');
            }
        }, 100);
    });
</script>

<script>
    $(document).ready(function() {

        $('#leave_type_id').on('change', function () {
            var selectedValue = $(this).val();
            
            if (selectedValue == "3" || selectedValue == "4" || selectedValue == "5") {
                $('#half_day_type').val('full_day').prop('disabled', true);
            } else {
                $('#half_day_type').prop('disabled', false);
            }

            if(selectedValue == "4" || selectedValue == "5"){
                $('#start_date').val('');
                $('#end_date').val('');
                $('#end_date').prop('disabled', true);
            }else{
                $('#end_date').prop('disabled', false);
            }
        });

        $('#start_date').on('blur', function () {
            var selectedValue = $('#leave_type_id').val();
            
            if (selectedValue == "4" || selectedValue == "5") {
                var startDate = $(this).val();
                $('#end_date').val(startDate);
            }
        });


        $('#save-draft-btn').on('click', function() {


            // Get the Start Date and End Date values
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();

            // Check if Start Date and End Date are provided
            if (!startDate || !endDate) {
                alert('{{ __('Please select both Start Date and End Date.') }}');
                e.preventDefault(); // Prevent form submission
                return;
            }

            // Convert dates to Date objects for comparison
            var startDateObj = new Date(startDate);
            var endDateObj = new Date(endDate);

            // Check if End Date is later than Start Date
            if (endDateObj < startDateObj) {
                alert('{{ __('End Date must be later than Start Date.') }}');
                e.preventDefault(); // Prevent form submission
                return;
            }

            var draftField = $('<input>').attr({
                type: 'hidden',
                name: 'status',
                value: 'draft'
            });
            $('#leave-form').append(draftField); // Append the hidden field to the form

            $('#half_day_type').prop('disabled', false);
            $('#end_date').prop('disabled', false);

            $('#leave-form').submit(); // Submit the form
        });

        // Confirmation before form submission with date validation
        $('#apply-btn').on('click', function(e) {
            // Get the Start Date and End Date values
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();

            // Check if Start Date and End Date are provided
            if (!startDate || !endDate) {
                alert('{{ __('Please select both Start Date and End Date.') }}');
                e.preventDefault(); // Prevent form submission
                return;
            }

            // Convert dates to Date objects for comparison
            var startDateObj = new Date(startDate);
            var endDateObj = new Date(endDate);

            // Check if End Date is later than Start Date
            if (endDateObj < startDateObj) {
                alert('{{ __('End Date must be later than Start Date.') }}');
                e.preventDefault(); // Prevent form submission
                return;
            }

            // Confirmation alert before form submission
            if (!confirm('{{ __('Are you sure you want to apply for leave?') }}')) {
                e.preventDefault(); // If user clicks 'Cancel', prevent form submission
            }else{
                $('#half_day_type').prop('disabled', false);
                $('#end_date').prop('disabled', false);
            }
        });


        $('#leave_type_id').on('change', function () {
            var selectedValue = $(this).val();

            if (selectedValue == "5") {
                $('#time_dropdown_wrapper').show();
                $('#half_day_type_div').css('display','none');
            } else {
                $('#time_dropdown_wrapper').hide();
                $('#leave_time').val('');
                $('#half_day_type_div').css('display','block');
            }
        });
    });
</script>
