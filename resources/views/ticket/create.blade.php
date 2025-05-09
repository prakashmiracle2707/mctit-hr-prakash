@php
    $chatgpt = App\Models\Utility::getValByName('enable_chatgpt');
@endphp

{{ Form::open(['url' => 'ticket', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">

    {{-- Subtask Hidden Input --}}
    @if(!empty($parentId))
        <input type="hidden" name="parent_id" value="{{ $parentId }}">
    @endif
    
    @if ($chatgpt == 'on')
        <div class="text-end">
            <a href="#" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true"
                data-url="{{ route('generate', ['ticket']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
                <i class="fas fa-robot"></i>{{ __(' Generate With AI') }}
            </a>
        </div>
    @endif

    <div class="row">

        <div class="form-group col-md-6">
            {{ Form::label('ticket_type_id', __('Ticket Type'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::select('ticket_type_id', $ticketTypes, null, [
                'class' => 'form-control select2',
                'required' => true,
                'placeholder' => __('Select Ticket Type')
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('priority_id', __('Priority'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::select('priority_id', $ticketPriorities, null, [
                'class' => 'form-control select2',
                'required' => true,
                'placeholder' => __('Select Priority')
            ]) }}
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('title', __('Subject'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Ticket Subject')]) }}
        </div>

        <!-- <div class="form-group col-md-6">
            {{ Form::label('project_id', __('Project'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::select('project_id', 
                $projects, 
                null, 
                ['class' => 'form-control select2', 'id' => 'project_id', 'placeholder' => __('Select Project'), 'required' => 'required']
            ) }}
        </div> -->
    </div>
    <div class="row">
        @if (\Auth::user()->type != 'employee')
            <div class="form-group col-md-6">
                {{ Form::label('employee_id', __('Assignee'), ['class' => 'col-form-label']) }} <x-required></x-required>
                {{ Form::select('employee_id', $employees, null, ['class' => 'form-control', 'required' => 'required', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
            </div>
        @else
            {!! Form::hidden('employee_id', !empty($employees) ? $employees->id : 0, ['id' => 'employee_id']) !!}
        @endif
    </div>
        

    <div class="row">   

        <div class="form-group col-md-6">
            {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::date('start_date', date('Y-m-d'), ['class' => 'form-control current_date', 'autocomplete' => 'off']) }}
        </div>

        <div class="form-group col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
                {{ Form::date('end_date', date('Y-m-d'), ['class' => 'form-control current_date', 'autocomplete' => 'off']) }}
            </div>
        </div>

        <!-- <div class="form-group col-md-6">
            {{ Form::label('status', __('Status'), ['class' => 'col-form-label']) }}
            {{ Form::select('status', $ticketStatuses, $defaultStatusId, ['class' => 'form-control', 'placeholder' => __('Select Status')]) }}
        </div> -->

        <div class="form-group col-md-12">
            {!! Form::label('description', __('Description'), ['class' => 'col-form-label']) !!}
            <textarea class="form-control summernote-simple-2" name="description" id="exampleFormControlTextarea1" rows="7"></textarea>
        </div>

        <div class="row">
            <div class="form-group col-md-6">
                <label class="form-label">{{ __('Attachments') }}</label>
                <div class="col-sm-12 col-md-12">
                    <div class="form-group col-lg-12 col-md-12">
                        <div class="choose-file form-group">
                            <label for="file" class="form-label">
                                <input type="file" name="attachment" id="attachment" class="form-control {{ $errors->has('attachment') ? ' is-invalid' : '' }}" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])" data-filename="attachment_selection">
                                <div class="invalid-feedback">
                                    {{ $errors->first('attachment') }}
                                </div>
                            </label>
                            <p class="attachment_selection"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-4">
                <label class="form-label"></label>
                <div class="col-sm-12 col-md-12">
                    <div class="form-group col-lg-12 col-md-12">
                        <img src="" id="blah" width="60%" />
                    </div>
                </div>
            </div>
        </div>

        

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
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

<script>
    $(document).ready(function () {
        // Initialize Select2
       

        // On project change
        $('#project_id').on('change', function () {
            var projectId = $(this).val();
            if (projectId) {
                var url = '{{ route("project.employees", ":id") }}'.replace(':id', projectId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (data) {
                        var $employeeSelect = $('#employee_id');

                        // Clear existing options
                        $employeeSelect.empty();

                        $employeeSelect.append(new Option('Select Employee', '', false, false));
                        // Append new options
                        $.each(data, function (id, name) {
                            var newOption = new Option(name, id, false, false);
                            $employeeSelect.append(newOption);
                        });

                    },
                    error: function () {
                        alert('Could not fetch employees.');
                    }
                });
            }
        });
    });
</script>
