@php
    $chatgpt = App\Models\Utility::getValByName('enable_chatgpt');
@endphp

{{ Form::model($timeSheet, ['route' => ['timesheet.update', $timeSheet->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
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
                {!! Form::select('employee_id', $employees, null, ['class' => 'form-control font-style select2', 'id'=>'choices-multiple','required' => 'required']) !!}
            </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::text('date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off', 'required' => 'required']) }}
        </div>
        <!-- <div class="form-group col-md-6">
            {{ Form::label('hours', __('Hours'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::number('hours', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01']) }}
        </div> -->

        <div class="form-group col-md-3">
            {{ Form::label('workhours', __('Work Hours'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::select('workhours', array_combine(range(0, 8), range(0, 8)), $timeSheet->workhours ?? null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => 'Select']) }}
        </div>

        @php
            $minutes = [];
            foreach (range(0, 59) as $i) {
                $formatted = str_pad($i, 2, '0', STR_PAD_LEFT);
                $minutes[$formatted] = $formatted;
            }

            $selectedMinutes = isset($timeSheet->workminutes) ? str_pad($timeSheet->workminutes, 2, '0', STR_PAD_LEFT) : null;
        @endphp

        <div class="form-group col-md-3">
            {{ Form::label('workminutes', __('Work Minutes'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::select('workminutes', $minutes, $selectedMinutes, [
                'class' => 'form-control select2',
                'required' => 'required',
                'placeholder' => 'Select'
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('project_id', __('Project'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {!! Form::select('project_id', $projects, $timeSheet->project_id, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => 'Select Project']) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('milestone_id', __('Milestone'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {!! Form::select('milestone_id', $milestones, $timeSheet->milestone_id, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => 'Select Milestone']) !!}
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('task_name', __('Task'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::text('task_name', $timeSheet->task_name, ['class' => 'form-control', 'autocomplete' => 'off', 'required' => 'required', 'placeholder' => __('Enter Task')]) }}
        </div>

        <div class="form-group  col-md-12">
            {{ Form::label('remark', __('Work Description'), ['class' => 'col-form-label']) }}
            {!! Form::textarea('remark', null, ['class' => 'form-control', 'rows' => '10' ,'placeholder'=>__('Enter Description')]) !!}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

</div>
{{ Form::close() }}
