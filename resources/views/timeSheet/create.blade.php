@php
    $chatgpt = App\Models\Utility::getValByName('enable_chatgpt');
@endphp

{{ Form::open(['route' => ['timesheet.store'], 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">

    @if ($chatgpt == 'on')
    <div class="card-footer text-end">
        <a href="#" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true"
            data-url="{{ route('generate', ['timesheet']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
            title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
            <i class="fas fa-robot"></i>{{ __(' Generate With AI') }}
        </a>
    </div>
    @endif

    <div class="row">

        @if (\Auth::user()->type != 'employee')
            <div class="form-group col-md-12">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {!! Form::select('employee_id', $employees, null, ['class' => 'form-control  select2' , 'id'=>'choices-multiple', 'required' => 'required' ,'placeholder'=>'Select employee']) !!}
            </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::text('date', '', ['class' => 'form-control d_week current_date', 'autocomplete' => 'off', 'required' => 'required' ,'placeholder'=>'Select date']) }}
        </div>
        <!-- <div class="form-group col-md-6">
            {{ Form::label('hours', __('Hours'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::number('hours', '', ['class' => 'form-control','autocomplete' => 'off' ,'required' => 'required', 'step' => '0.01' ,'placeholder'=>__('Enter hours')]) }}
        </div> -->

        <div class="form-group col-md-3">
            {{ Form::label('workhours', __('Hours'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::select('workhours', array_combine(range(0, 8), range(0, 8)), null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => 'Select']) }}
        </div>

        <div class="form-group col-md-3">
            @php
                $minuteOptions = [];
                foreach (range(0, 59) as $i) {
                    $formatted = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $minuteOptions[$formatted] = $formatted;
                }
            @endphp

            {{  Form::label('workminutes', __('Minutes'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{  Form::select('workminutes', $minuteOptions, null, [
                    'class' => 'form-control select2',
                    'required' => 'required',
                    'placeholder' => 'Select'
                ]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('project_id', __('Project'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {!! Form::select('project_id', $projects, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => 'Select Project']) !!}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('milestone_id', __('Milestone'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {!! Form::select('milestone_id', $milestones, $defaultMilestoneId, ['class' => 'form-control select2','placeholder' => 'Select Milestone','id' => 'milestone_id','required' => 'required']) !!}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('task_name', __('Task'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::text('task_name', '', ['class' => 'form-control', 'autocomplete' => 'off', 'required' => 'required', 'placeholder' => __('Enter Task')]) }}
        </div>

        <div class="form-group  col-md-12">
            {{ Form::label('remark', __('Work Description'), ['class' => 'col-form-label']) }}
            {!! Form::textarea('remark', null, ['class' => 'form-control', 'rows' => '10' ,'placeholder'=>__('Enter Description')]) !!}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
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
