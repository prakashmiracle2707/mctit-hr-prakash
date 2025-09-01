{{ Form::model($attendanceEmployee, ['route' => ['attendanceemployee.update', $attendanceEmployee->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-lg-6 col-md-6 ">
            {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
            {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2']) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('date', __('Date In'), ['class' => 'col-form-label']) }}
            {{ Form::date('date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off']) }}
        </div>

        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('clock_in', __('Clock In'), ['class' => 'col-form-label']) }}
            {{ Form::time('clock_in', null, ['class' => 'form-control pc-timepicker-2', 'id' => 'clock_in']) }}
        </div>

        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('checkout_date', __('Clock Out Date'), ['class' => 'col-form-label']) }}
            {{ Form::date('checkout_date', null, ['class' => 'form-control', 'autocomplete' => 'off']) }}
        </div>

        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('clock_out', __('Clock Out'), ['class' => 'col-form-label']) }}
            {{ Form::time('clock_out', null, ['class' => 'form-control pc-timepicker-2 ', 'id' => 'clock_out']) }}
        </div>

        <!-- Work from Home Checkbox -->
        <div class="form-group col-lg-6 col-md-6">
            <div class="form-check mt-4">
                {{ Form::checkbox('work_from_home', 1, $attendanceEmployee->work_from_home, [
                    'class' => 'form-check-input',
                    'id' => 'work_from_home'
                ]) }}
                <label class="form-check-label" for="work_from_home">{{ __('Work from Home') }}</label>
            </div>
        </div>

        <!-- Is Leave Checkbox -->
        <div class="form-group col-lg-6 col-md-6">
            <div class="form-check mt-4">
                {{ Form::checkbox('is_leave', 1, $attendanceEmployee->is_leave, [
                    'class' => 'form-check-input',
                    'id' => 'is_leave'
                ]) }}
                <label class="form-check-label" for="is_leave">{{ __('Is Leave') }}</label>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
