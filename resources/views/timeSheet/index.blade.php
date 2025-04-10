@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Timesheet') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Timesheet') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('timesheet.export', [
        'start_date' => request('start_date'),
        'end_date' => request('end_date'),
        'employee' => request('employee'),
        'project_id' => request('project_id'),
        'month' => request('month'),
    ]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a>

    <!-- <a href="#" data-url="{{ route('timesheet.file.import') }}" data-ajax-popup="true"
        data-title="{{ __('Import Timesheet CSV file') }}" data-bs-toggle="tooltip" title=""
        class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Import') }}">
        <i class="ti ti-file-import"></i>
    </a> -->


    @can('Create TimeSheet')
        <a href="#" data-url="{{ route('timesheet.create') }}" data-ajax-popup="true" data-size="lg"
            data-title="{{ __('Create New Timesheet') }}" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection


<style>
    table th.task-column,
    table td.task-column {
        max-width: 300px; /* You can adjust this value */
        word-wrap: break-word;
        white-space: normal;
        overflow-wrap: break-word;
    }
</style>
@section('content')
    {{-- <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
        <div class=" mt-2 " id="multiCollapseExample1" style="">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['timesheet.index'], 'method' => 'get', 'id' => 'timesheet_filter']) }}
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mx-2">
                            <div class="btn-box">
                                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                {{ Form::text('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'month-btn form-control d_week current_date', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mx-2">
                            <div class="btn-box">
                                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                {{ Form::text('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'month-btn form-control d_week current_date', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                        @if (\Auth::user()->type == 'employee')
                            {!! Form::hidden('employee', !empty($employeesList) ? $employeesList->id : 0, ['id' => 'employee_id']) !!}
                        @else
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mx-2">
                                <div class="btn-box">
                                    {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                                    {{ Form::select('employee', $employeesList, isset($_GET['employee']) ? $_GET['employee'] : '', ['class' => 'form-control select ', 'id' => 'employee_id']) }}
                                </div>
                            </div>
                        @endif
                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('timesheet_filter').submit(); return false;"
                                data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{ route('timesheet.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                title="" data-bs-original-title="Reset">
                                <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off "></i></span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div> --}}

    <div class="col-sm-12">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['timesheet.index'], 'method' => 'get', 'id' => 'timesheet_filter']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <!-- <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box"></div>
                                </div> -->
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('project_id', __('Project'), ['class' => 'form-label']) }}
                                        {{ Form::select('project_id', $projects, isset($_GET['project_id']) ? $_GET['project_id'] : '', ['class' => 'form-control select', 'id' => 'project_id', 'placeholder' => 'Select Project']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                        {{ Form::month('month', request('month', now()->format('Y-m')), ['class' => 'month-btn form-control', 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                                <!-- <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'month-btn form-control  current_date', 'autocomplete' => 'off', 'id' => 'current_date']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'month-btn form-control current_date', 'autocomplete' => 'off', 'id' => 'current_date']) }}
                                    </div>
                                </div> -->
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    @if (\Auth::user()->type == 'employee')
                                        <div class="btn-box" id="employee_div">
                                        {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                                        {{ Form::select('employee', $employeesList, isset($_GET['employee']) ? $_GET['employee'] : '', ['class' => 'form-control select ', 'id' => 'employee_id']) }}
                                        </div>
                                    @else
                                        <div class="btn-box">
                                        {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                                        {{ Form::select('employee', $employeesList, isset($_GET['employee']) ? $_GET['employee'] : '', ['class' => 'form-control select ', 'id' => 'employee_id']) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto mt-4">
                                    <a href="#" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('timesheet_filter').submit(); return false;"
                                        data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('timesheet.index') }}" class="btn btn-sm btn-danger"
                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Reset">
                                        <span class="btn-inner--icon"><i
                                                class="ti ti-refresh text-white-off "></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    @if($groupedTimeSheets)
    <h5 class="mt-4">{{ __('Summary of Hours by Project & Employee') }}</h5>

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="card-body py-0">
                    <div class="table-responsive">
                        <br />
                        <br />
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="padding-left:25px;border-left: 1px solid #f1f1f1;">{{ __('Project') }}</th>
                                    <th  style="padding-left:25px;border-left: 1px solid #f1f1f1;">{{ __('Employee') }}</th>
                                    <th style="padding-left:25px;border-left: 1px solid #f1f1f1;border-right: 1px solid #f1f1f1;">{{ __('Hours') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($groupedTimeSheets as $project => $employees)
                                @php $rowCount = count($employees); $first = true; @endphp
                                @foreach($employees as $employee => $hours)
                                    @php
                                        // Convert total minutes to hours and minutes
                                        $totalHours = floor($hours / 60);
                                        $remainingMinutes = $hours % 60;
                                    @endphp
                                    <tr>
                                        @if($first)
                                            <td rowspan="{{ $rowCount }}" style="padding-left:25px;border-left: 1px solid #f1f1f1;">{{ $project }}</td>
                                            @php $first = false; @endphp
                                        @endif
                                        <td style="padding-left:25px;border-left: 1px solid #f1f1f1;">{{ ucfirst($employee) }}</td>
                                        <td style="padding-left:25px;border-left: 1px solid #f1f1f1;border-right: 1px solid #f1f1f1;">{{ str_pad($totalHours, 2, '0', STR_PAD_LEFT) . ":" . str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT)}}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="card-body py-0">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    @if (\Auth::user()->type != 'employee')
                                        <th>{{ __('Employee') }}</th>
                                    @else
                                        @if(count($employeesList) == 0)
                                            <th class="">{{ __('Employee') }}</th>
                                        @endif
                                    @endif
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Milestone') }}</th>
                                    <th class="task-column">{{ __('Task') }}</th>
                                    <!-- <th>{{ __('OLD Hours') }}</th> -->
                                    <th>{{ __('Hours') }}</th>
                                    <!-- <th>{{ __('Remark') }}</th> -->
                                    <th width="200ox">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>


                                @foreach ($timeSheets as $timeSheet)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($timeSheet->date)->format('d/m/Y') }}</td>
                                        @if (\Auth::user()->type != 'employee')
                                            <td>{{ !empty($timeSheet->employee) ? $timeSheet->employee->name : '' }}</td>
                                        @else
                                            @if(count($employeesList) == 0)
                                                <td class="">{{ !empty($timeSheet->employee) ? $timeSheet->employee->name : '' }}</td>
                                            @endif
                                        @endif
                                        <td>{{ !empty($timeSheet->project) ? $timeSheet->project->name : '-' }}</td>
                                        <td>{{ !empty($timeSheet->milestone) ? $timeSheet->milestone->name : '-' }}</td>
                                        <td class="task-column">{{ $timeSheet->task_name ?? '-' }}</td>
                                        <!-- <td >{{ $timeSheet->hours ?? '-' }}</td> -->
                                        <td>{{ str_pad($timeSheet->workhours, 2, '0', STR_PAD_LEFT).":".str_pad($timeSheet->workminutes, 2, '0', STR_PAD_LEFT) }}</td>
                                        <!-- <td>{{ $timeSheet->remark }}</td> -->
                                        <td class="Action">
                                            <div class="dt-buttons">
                                            <span>
                                                @can('Edit TimeSheet')
                                                    <div class="action-btn bg-info me-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-url="{{ route('timesheet.edit', $timeSheet->id) }}"
                                                            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Edit Timesheet') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <span class="text-white"><i class="ti ti-pencil "></i></span>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('Delete TimeSheet')
                                                    <div class="action-btn">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['timesheet.destroy', $timeSheet->id],
                                                            'id' => 'delete-form-' . $timeSheet->id,
                                                        ]) !!}
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="Delete" aria-label="Delete"><span class="text-white"><i
                                                                class="ti ti-trash "></i></span></a>
                                                        </form>
                                                    </div>
                                                @endcan
                                            </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('script-page')
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

        {{-- 🔁 AJAX Script --}}
        
        @if (\Auth::user()->type == 'employee')
            <script>
                @if(count($employeesList) == 0)
                    $('#employee_div').css('display','none');
                    $('.employee_cls').css('display','none');
                @endif  
                $(document).ready(function () {
                    $('#project_id').on('change', function () {
                        const projectId = $(this).val();
                        
                        const $employee = $('#employee_id');

                        if (projectId) {

                            const url = '{{ route("projects.manager.employees.by.project", ":id") }}'.replace(':id', projectId);
                            $.ajax({
                                url: url,
                                type: 'GET',
                                success: function (response) {
                                    $('#employee_div').css('display','block');
                                    $employee.empty();
                                    $employee.append(`<option value="all">All</option>`);
                                    $.each(response, function (id, name) {
                                        $employee.append(`<option value="${id}">${name}</option>`);
                                    });

                                },
                                error: function (xhr) {
                                    $employee.empty();
                                    $employee.append(`<option value="">No access / No employees</option>`);
                                    console.error('Error:', xhr.responseText);
                                    $('#employee_div').css('display','none');
                                }
                            });
                        } else {
                            $employee.empty().append(`<option value="">All</option>`);
                        }
                    });
                });
            </script>
        @else
            <script>
                $(document).ready(function () {
                    $('#project_id').on('change', function () {
                        const projectId = $(this).val();
                        const $employee = $('#employee_id');

                        if (projectId) {
                            $.ajax({
                                url: '{{ route("timesheet.project.employees") }}',
                                type: 'GET',
                                data: { project_id: projectId },
                                success: function (response) {
                                    console.log("Response:", response);
                                    $employee.empty();
                                    $employee.append('<option value="">All</option>');
                                    $.each(response, function (id, name) {
                                        $employee.append(`<option value="${id}">${name}</option>`);
                                    });
                                },
                                error: function () {
                                    alert('Could not load employees for selected project.');
                                }
                            });
                        } else {
                            // Reset dropdown to original full list if project is unselected
                            // $employee.empty().append('<option value="">All</option>');
                            @foreach($employeesList as $id => $name)
                                $employee.append('<option value="{{ $id }}">{{ $name }}</option>');
                            @endforeach
                        }
                    });
                });
            </script>
        @endif
        
    @endpush
