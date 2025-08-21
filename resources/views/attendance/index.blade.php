@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Attendance List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Attendance List') }}</li>
@endsection

@section('action-button')

    @can('Create Attendance')
        <a  href="#" 
            class="btn btn-sm btn-primary"
            data-size="lg"
            data-url="{{ route('attendanceemployee.create') }}" 
            data-ajax-popup="true" 
            data-bs-toggle="tooltip"
            title=""
            data-title="{{ __('Create New Attendance') }}" 
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection


@push('script-page')
    <script>
        $('input[name="type"]:radio').on('change', function(e) {
            var type = $(this).val();

            if (type == 'monthly') {
                $('.month').addClass('d-block');
                $('.month').removeClass('d-none');
                $('.date').addClass('d-none');
                $('.date').removeClass('d-block');
            } else {
                $('.date').addClass('d-block');
                $('.date').removeClass('d-none');
                $('.month').addClass('d-none');
                $('.month').removeClass('d-block');
            }
        });

        $('input[name="type"]:radio:checked').trigger('change');
    </script>

@endpush
@section('content')
    @if (session('status'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {!! session('status') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['attendanceemployee.index'], 'method' => 'get', 'id' => 'attendanceemployee_filter']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">

                                <div class="col-3">
                                    <label class="form-label">{{ __('Type') }}</label> <br>

                                    <div class="form-check form-check-inline form-group">
                                        <input type="radio" id="monthly" value="monthly" name="type"
                                            class="form-check-input"
                                            {{ isset($_GET['type']) && $_GET['type'] == 'monthly' ? 'checked' : 'checked' }}>
                                        <label class="form-check-label" for="monthly">{{ __('Monthly') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline form-group">
                                        <input type="radio" id="daily" value="daily" name="type"
                                            class="form-check-input"
                                            {{ isset($_GET['type']) && $_GET['type'] == 'daily' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="daily">{{ __('Daily') }}</label>
                                    </div>

                                </div>

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                    <div class="btn-box">
                                        {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                        {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'month-btn form-control month-btn']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : '', ['class' => 'form-control month-btn']) }}
                                    </div>
                                </div>
                                

                                @if(\Auth::user()->type != 'employee')
                                    <div class="col-md-3">
                                        {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                        {{ Form::select('employee_id', ['' => 'All'] + $employeeList->toArray(), request('employee_id'), ['class' => 'form-control select']) }}
                                    </div>
                                @endif

                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto">

                                    <a href="#" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('attendanceemployee_filter').submit(); return false;"
                                        data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                        data-original-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>

                                    <a href="{{ route('attendanceemployee.index') }}" class="btn btn-sm btn-danger "
                                        data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                        data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off "></i></span>
                                    </a>

                                    <a href="#" data-url="{{ route('attendance.file.import') }}"
                                        data-ajax-popup="true" data-title="{{ __('Import  Attendance CSV File') }}"
                                        data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                        data-bs-original-title="{{ __('Import') }}">
                                        <i class="ti ti-file"></i>
                                    </a>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>



    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Employee') }}</th>
                                @endif
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Clock In') }}</th>
                                <th>{{ __('Clock Out') }}</th>
                                <!-- <th>{{ __('Late') }}</th>
                                <th>{{ __('Early Leaving') }}</th>
                                <th>{{ __('Overtime') }}</th> -->
                                <th>{{ __('Total Hours') }}
                                <th>{{ __('Total Break Log') }}</th>
                                @if (Gate::check('Edit Attendance') || Gate::check('Delete Attendance'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($attendanceEmployee as $attendance)
                                @php
                                    $carbonDate = \Carbon\Carbon::parse($attendance->date);
                                    $dayName = strtolower($carbonDate->format('D')); // 'sat', 'sun', etc.
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}
                                        @if($dayName == 'sat' || $dayName == 'sun')
                                            <span class="badge bg-danger px-1">{{$dayName}}</span>
                                        @endif
                                        @if($attendance->work_from_home)
                                            <span class="badge bg-secondary p-1 px-1">WFH</span>
                                        @endif
                                    </td>
                                    @if (\Auth::user()->type != 'employee')
                                        <td>{{ !empty($attendance->employee) ? $attendance->employee->name : '' }}</td>
                                    @endif
                                    
                                    <td>{{ $attendance->status }}</td>
                                    <td>{{ $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00' }}
                                        {!! Get_Device_Type_Icon($attendance->device_type_clockin,\Auth::user()->id) !!}
                                    </td>
                                    <td>
                                    
                                        @if ($attendance->clock_out == '00:00:00' && $attendance->date < date('Y-m-d'))
                                            <span class="badge bg-danger p-1 px-1">Missed Clock-out</span>
                                        @else
                                            @if ($attendance->date != $attendance->checkout_date)
                                                {{ 
                                                    $attendance->clock_out != '00:00:00' 
                                                    ?  date('d/m/Y', strtotime($attendance->checkout_date)).' '.date('h:i A', strtotime($attendance->clock_out))  
                                                    : '00:00' 
                                                }}
                                            @else
                                            {{ $attendance->clock_out != '00:00:00' ? date('h:i A', strtotime($attendance->clock_out)) : '00:00' }}
                                            @endif
                                        @endif
                                        {!! Get_Device_Type_Icon($attendance->device_type_clockout,\Auth::user()->id) !!}
                                    </td>
                                    <!-- <td>{{ $attendance->late }}</td>
                                    <td>{{ $attendance->early_leaving }}</td>
                                    <td>{{ $attendance->overtime }}</td> -->
                                    <td>{{ $attendance->checkout_time_diff != '' ? $attendance->checkout_time_diff : '00:00:00' }}</td>

                                    <td>{{ $attendance->totalBreakDuration ?? '00:00:00' }}</td>
                                    @if (Gate::check('Edit Attendance') || Gate::check('Delete Attendance'))
                                    <td class="Action">
                                        
                                        <div class="dt-buttons">
                                        <span>
                                                @can('Edit Attendance')
                                                    <div class="action-btn bg-info me-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="{{ URL::to('attendanceemployee/' . $attendance->id . '/edit') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Edit Attendance') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('Delete Attendance')
                                                    <div class="action-btn bg-danger">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['attendanceemployee.destroy', $attendance->id],
                                                            'id' => 'delete-form-' . $attendance->id,
                                                        ]) !!}
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="Delete" aria-label="Delete"><span class="text-white"><i
                                                                class="ti ti-trash"></i></span></a>
                                                        </form>
                                                    </div>
                                                @endcan
                                            </span>
                                        </div>
                                        
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
