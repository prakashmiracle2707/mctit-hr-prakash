@extends('layouts.admin')

@section('page-title')
    {{ __('Dashboard') }}
@endsection

@php
    $setting = App\Models\Utility::settings();
    $icons = \App\Models\Utility::get_file('uploads/job/icons/');
@endphp

{{-- @section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
@endsection --}}

@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif


    @if (\Auth::user()->type == 'employee')
        <div class="col-xxl-6 col-md-6" >
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <h5>{{ __('Calendar') }}</h5>
                            <input type="hidden" id="path_admin" value="{{ url('/') }}">
                        </div>
                        <div class="col-lg-6">
                            {{-- <div class="form-group"> --}}
                                <label for=""></label>
                                @if (isset($setting['is_enabled']) && $setting['is_enabled'] == 'on')
                                    <!-- <select class="form-control" name="calender_type" id="calender_type"
                                    style="float: right;width: 155px;" onchange="get_data()">
                                        <option value="google_calender">{{ __('Google Calendar') }}</option>
                                        <option value="local_calender" selected="true">
                                            {{ __('Local Calendar') }}</option>
                                    </select> -->
                                @endif
                            {{-- </div> --}}

                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- <div id='event_calendar' class='calendar'></div> -->
                    <iframe src="https://calendar.google.com/calendar/embed?src={{\Auth::user()->email}}&ctz=Asia/Kolkata&mode=AGENDA&showPrint=0" style="border: 0" width="100%" height="400" frameborder="0" scrolling="no"></iframe>
                </div>
            </div>
        </div>
        <div class="col-xxl-6 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Mark Attandance') }}</h5>
                </div>
                <div class="card-body">

                    @if (!empty($employeeAttendance))
                    <div class="row">
                        <div class="col-xxl-6 col-md-12">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="badge theme-avtar bg-primary">
                                                <i class="ti ti-file-report"></i>
                                            </div>
                                            <div class="ms-3">
                                                <h5 class="mb-0">Clock In Time</h5>
                                                <p class="text-muted text-sm mb-0">
                                                    @if (!empty($employeeAttendance))
                                                        {{ \Carbon\Carbon::parse($employeeAttendance->clock_in)->format('h:i A') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-6 col-md-12">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="badge theme-avtar bg-secondary">
                                                <i class="ti ti-calendar-time"></i>
                                            </div>
                                            <div class="ms-3">
                                                <h5 class="mb-0">Clock Out Time</h5>
                                                <p class="text-muted text-sm mb-0">
                                                    @if (!empty($employeeAttendance))
                                                    {{ __($employeeAttendance->clock_out != '00:00:00' 
    ? \Carbon\Carbon::parse($employeeAttendance->clock_out)->format('h:i A') 
    : 'Not Clocked Out (' . \Carbon\Carbon::parse($employeeAttendance->clock_in)->addHours(9)->format('h:i A') . ')') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-6 col-md-12">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="badge theme-avtar bg-primary">
                                                <i class="ti ti-wallet"></i>
                                            </div>
                                            <div class="ms-3">
                                                <h5 class="mb-0">Work Time</h5>
                                                <p class="text-muted text-sm mb-0"><span id="total_work_time">--:--:--</span></p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    
                    <div class="row">
                        <div class="col-6  float-right border-right">
                            {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post']) }}
                            @if (empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn-primary">{{ __('CLOCK IN') }}</button>
                                <div class="form-check">
                                    {{ Form::checkbox('work_from_home', 1, false, ['class' => 'form-check-input', 'id' => 'work_from_home_in']) }}
                                    <label class="form-check-label" for="work_from_home_in">{{ __('Work from Home') }}</label>
                                </div>
                            @else
                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn-primary disabled" disabled>{{ __('CLOCK IN') }}</button>
                                
                                <div class="form-check" style="margin-top:5px;">
                                    {{ Form::checkbox('work_from_home', 1, $isWorkFromHome, [
                                        'class' => 'form-check-input', 
                                        'id' => 'work_from_home_in',
                                        $disableCheckbox ? 'disabled' : ''
                                    ]) }}
                                    <label class="form-check-label" for="work_from_home_in">{{ __('Work from Home') }}</label>
                                </div>
                            @endif
                            {{ Form::close() }}
                        </div>

                        <div class="col-6 float-left">
                            @if (!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                {{ Form::model($employeeAttendance, ['route' => ['attendanceemployee.update', $employeeAttendance->id], 'method' => 'PUT']) }}
                                <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger" style="float: right;" onclick="return confirmClockOut();">{{ __('CLOCK OUT') }}</button>
                            @else
                                <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger disabled float-right" disabled style="float: right;">{{ __('CLOCK OUT') }}</button>
                            @endif
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Function to show confirmation alert before clocking out
                function confirmClockOut() {
                    return confirm("Are you sure you want to clock out?");
                }
            </script>
            <div class="card" style="height: 462px;display: none;">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Meeting schedule') }}</h5>
                </div>
                <div class="card-body" style="height: 320px">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Meeting title') }}</th>
                                    <th>{{ __('Meeting Date') }}</th>
                                    <th>{{ __('Meeting Time') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach ($meetings as $meeting)
                                    <tr>
                                        <td>{{ $meeting->title }}</td>
                                        <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                        <td>{{ \Auth::user()->timeFormat($meeting->time) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-12 col-lg-12 col-md-12" style="display:none;">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Announcement List') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach ($announcements as $announcement)
                                    <tr>
                                        <td>{{ $announcement->title }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>
                                        <td>{{ $announcement->description }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-xxl-12">

            {{-- start --}}
            <div class="row">

                <div class="col-lg-4 col-md-6">

                    <div class="card stats-wrapper dash-info-card">
                        <div class="card-body stats">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-primary">
                                            <i class="ti ti-users"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0"><a
                                                href="{{ route('user.index') }}">{{ __('Staff') }}</a></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-primary">{{ $countUser + $countEmployee }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="col-lg-4 col-md-6">

                    <div class="card stats-wrapper dash-info-card">
                        <div class="card-body stats">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-info">
                                            <i class="ti ti-ticket"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0"><a
                                                href="{{ route('ticket.index') }}">{{ __('Ticket') }}</a></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-info"> {{ $countTicket }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="col-lg-4 col-md-6">

                    <div class="card stats-wrapper dash-info-card">
                        <div class="card-body stats">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-info">
                                            <i class="ti ti-ticket"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0"><a
                                                href="{{ route('ticket.index') }}">{{ __("Today's Not Clock In") }}</a></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-info"> {{ count($notClockIns) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="col-lg-4 col-md-6">

                    <div class="card stats-wrapper dash-info-card">
                        <div class="card-body stats">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-warning">
                                            <i class="ti ti-wallet"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0"><a
                                                href="{{ route('accountlist.index') }}">{{ __('Account Balance') }}</a></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-warning">{{ \Auth::user()->priceFormat($accountBalance) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="col-lg-4 col-md-6">

                    <div class="card stats-wrapper dash-info-card">
                        <div class="card-body stats">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-warning">
                                            <i class="ti ti-wallet"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0"><a
                                                href="{{ route('accountlist.index') }}">{{ __("Today's Clock In") }}</a></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-warning">{{ count($attendanceEmployee) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="col-lg-4 col-md-6" style="display: none;">
            <div class="card stats-wrapper dash-info-card">
                <div class="card-body stats">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="badge theme-avtar bg-primary">
                                    <i class="ti ti-briefcase"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0"><a
                                        href="{{ route('job.index') }}">{{ __('Jobs') }}</a></h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0 text-primary">{{ $activeJob + $inActiveJOb }}</h4>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-lg-4 col-md-6" style="display: none;">

            <div class="card stats-wrapper dash-info-card">
                <div class="card-body stats">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="badge theme-avtar bg-info">
                                    <svg xmlns="{{ asset($icons . 'active.svg') }}" width="40" height="40" viewBox="0 0 40 40">
                                        <rect width="20" height="20" fill="none"></rect>
                                        <image href="{{ asset($icons . 'active.svg') }}" x="0" y="0" width="40" height="40" />
                                    </svg>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0"><a
                                        href="{{ route('job.index') }}">{{ __('Active Jobs') }}</a></h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0 text-info"> {{ $activeJob }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6" style="display: none;">

            <div class="card stats-wrapper dash-info-card">
                <div class="card-body stats">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="badge theme-avtar bg-warning">
                                    <svg xmlns="{{ asset($icons . 'inactive.svg') }}" width="20" height="20" viewBox="0 0 40 40">
                                        <rect width="20" height="20" fill="none"></rect>
                                        <image href="{{ asset($icons . 'inactive.svg') }}" x="0" y="0" width="40" height="40" />
                                    </svg>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0"><a
                                        href="{{ route('job.index') }}">{{ __('Inactive Jobs') }}</a></h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0 text-warning">{{ $inActiveJOb }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- </div> --}}

        {{-- end --}}

        <div class="col-xxl-12">
            <div class="row">
                
                    <div class="col-xl-5">

                        <div class="card" style="display:none;">
                            <div class="card-header card-body table-border-style">
                                <h5>{{ __('Meeting schedule') }}</h5>
                            </div>
                            <div class="card-body" style="height: 324px; overflow:auto">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Time') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @foreach ($meetings as $meeting)
                                                <tr>
                                                    <td>{{ $meeting->title }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                                    <td>{{ \Auth::user()->timeFormat($meeting->time) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header card-body table-border-style">
                                <h5>{{ __("Today's Not Clock In") }}</h5>
                            </div>
                            <div class="card-body" style="height:300px; overflow:auto">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @foreach ($notClockIns as $notClockIn)
                                                <tr>
                                                    <td>{{ $notClockIn->name }}</td>
                                                    <td><span class="absent-btn">{{ __('Absent') }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header card-body table-border-style">
                                <h5>{{ __("Today's Clock In") }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table" id="pc-dt-simple">
                                        <thead>
                                            <tr>
                                                <!-- <th>{{ __('Date') }}</th> -->
                                                @if (\Auth::user()->type != 'employee')
                                                    <th>{{ __('Employee') }}</th>
                                                @endif
                                                <!-- <th>{{ __('Status') }}</th> -->
                                                <th>{{ __('Clock In') }}</th>
                                                <th>{{ __('Clock Out') }}</th>
                                                <!-- <th>{{ __('Late') }}</th>
                                                <th>{{ __('Early Leaving') }}</th>
                                                <th>{{ __('Overtime') }}</th> -->
                                                <th>{{ __('Total Hours') }}
                                                <!-- @if (Gate::check('Edit Attendance') || Gate::check('Delete Attendance'))
                                                    <th width="200px">{{ __('Action') }}</th>
                                                @endif -->
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach ($attendanceEmployee as $attendance)
                                                <tr>
                                                    <!-- <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td> -->
                                                    @if (\Auth::user()->type != 'employee')
                                                        <td>{{ !empty($attendance->employee) ? $attendance->employee->name : '' }}
                                                            @if($attendance->work_from_home)
                                                                <span class="badge bg-secondary p-1 px-1">WFH</span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    
                                                    <!-- <td>{{ $attendance->status }}</td> -->
                                                    <td>{{ $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00' }}
                                                    </td>
                                                    <td>
                                                    
                                                        @if ($attendance->clock_out == '00:00:00' && $attendance->date < date('Y-m-d'))
                                                            <span class="badge bg-danger p-1 px-1">Missed Checkout</span>
                                                        @else
                                                            {{ $attendance->clock_out != '00:00:00' ? date('h:i A', strtotime($attendance->clock_out)) : '00:00' }}
                                                        @endif
                                                    </td>
                                                    <!-- <td>{{ $attendance->late }}</td>
                                                    <td>{{ $attendance->early_leaving }}</td>
                                                    <td>{{ $attendance->overtime }}</td> -->
                                                    <td>{{ $attendance->checkout_time_diff != '' ? $attendance->checkout_time_diff : '00:00:00' }}</td>
                                                   <!--  @if (Gate::check('Edit Attendance') || Gate::check('Delete Attendance'))
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
                                                    @endif -->
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>


               

                <div class="col-xl-7">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h5>{{ __('Calendar') }}</h5>
                                    <input type="hidden" id="path_admin" value="{{ url('/') }}">
                                </div>
                                <div class="col-lg-6">
                                    {{-- <div class="form-group"> --}}
                                        <label for=""></label>
                                        @if (isset($setting['is_enabled']) && $setting['is_enabled'] == 'on')
                                            <!-- <select class="form-control" name="calender_type" id="calender_type"
                                            style="float: right;width: 155px;" onchange="get_data()">
                                                <option value="google_calender">{{ __('Google Calendar') }}</option>
                                                <option value="local_calender" selected="true">
                                                    {{ __('Local Calendar') }}</option>
                                            </select> -->
                                        @endif
                                    {{-- </div> --}}
                                </div>
                            </div>
                        </div>
                        <div class="card-body card-635">
                            <!-- <div id='calendar' class='calendar'></div> -->
                            <iframe src="https://calendar.google.com/calendar/embed?src={{\Auth::user()->email}}&ctz=UTC&mode=AGENDA&showPrint=0" style="border: 0" width="100%" height="600" frameborder="0" scrolling="no"></iframe>
                        </div>
                    </div>
                </div>

                
                
            </div>
           
        </div>

        <div class="col-xl-12 col-lg-12 col-md-12" style="display:none;">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Announcement List') }}</h5>
                </div>
                <div class="card-body" style="height: 270px; overflow:auto">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach ($announcements as $announcement)
                                    <tr>
                                        <td>{{ $announcement->title }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>
                                        <td>{{ $announcement->description }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        </div>
    @endif
@endsection

@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>


    <script>
        const dataTable = new simpleDatatables.DataTable("#pc-dt-simple-dashbord");
    </script>

    @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr' || Auth::user()->type == 'CEO')
    <script type="text/javascript">
        $(document).ready(function() {
            get_data();
        });

        function get_data() {
            var calender_type = $('#calender_type :selected').val();
            console.log(calender_type);
            $('#calendar').removeClass('local_calender');
            $('#calendar').removeClass('google_calender');
            if (calender_type == undefined) {
                calender_type = 'local_calender';
            }
            $('#calendar').addClass(calender_type);

            $.ajax({
                url: $("#path_admin").val() + "/event/get_event_data",
                method: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'calender_type': calender_type
                },
                success: function(data) {
                    (function() {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                timeGridDay: "{{ __('Day') }}",
                                timeGridWeek: "{{ __('Week') }}",
                                dayGridMonth: "{{ __('Month') }}"
                            },
                            // slotLabelFormat: {
                            //     hour: '2-digit',
                            //     minute: '2-digit',
                            //     hour12: false,
                            // },
                            themeSystem: 'bootstrap',
                            slotDuration: '00:10:00',
                            allDaySlot: true,
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            events: data,
                            // height: 'auto',
                            // timeFormat: 'H(:mm)',
                        });
                        calendar.render();
                    })();
                }
            });

        }
    </script>
    @else

    <script>
        function calculateWorkTime(clockInTime, clockOutTime) {
            if (!clockInTime) {
                document.getElementById("total_work_time").innerText = "00:00:00";
                return;
            }

            let [inHours, inMinutes, inSeconds] = clockInTime.split(":").map(Number);
            let now = new Date();
            let clockInDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), inHours, inMinutes, inSeconds);

            let clockOutDate;
            if (!clockOutTime || clockOutTime === "00:00:00") {
                clockOutDate = new Date();
            } else {
                let [outHours, outMinutes, outSeconds] = clockOutTime.split(":").map(Number);
                clockOutDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), outHours, outMinutes, outSeconds);
            }

            let timeWorked = clockOutDate - clockInDate;
            let workedHours = Math.floor(timeWorked / (1000 * 60 * 60));
            let workedMinutes = Math.floor((timeWorked % (1000 * 60 * 60)) / (1000 * 60));
            let workedSeconds = Math.floor((timeWorked % (1000 * 60)) / 1000);

            document.getElementById("total_work_time").innerText =
                `${workedHours.toString().padStart(2, '0')}:${workedMinutes.toString().padStart(2, '0')}:${workedSeconds.toString().padStart(2, '0')}`;
        }

        let clockInTime = @json($employeeAttendance->clock_in ?? '');
        let clockOutTime = @json($employeeAttendance->clock_out ?? '');

        setInterval(() => {
            calculateWorkTime(clockInTime, clockOutTime);
        }, 1000);
        calculateWorkTime(clockInTime, clockOutTime);
    </script>
    <script>
        $(document).ready(function() {
            get_data();
        });

        function get_data() {
            var calender_type = $('#calender_type :selected').val();
            console.log(calender_type);
            $('#event_calendar').removeClass('local_calender');
            $('#event_calendar').removeClass('google_calender');
            if (calender_type == undefined) {
                calender_type = 'local_calender';
            }
            $('#event_calendar').addClass(calender_type);

            $.ajax({
                url: $("#path_admin").val() + "/event/get_event_data",
                method: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'calender_type': calender_type
                },
                success: function(data) {
                    (function() {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById(
                        'event_calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                timeGridDay: "{{ __('Day') }}",
                                timeGridWeek: "{{ __('Week') }}",
                                dayGridMonth: "{{ __('Month') }}"
                            },
                            // slotLabelFormat: {
                            //     hour: '2-digit',
                            //     minute: '2-digit',
                            //     hour12: false,
                            // },
                            themeSystem: 'bootstrap',
                            slotDuration: '00:10:00',
                            allDaySlot: true,
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            events: data,
                            // height: 'auto',
                            // timeFormat: 'H(:mm)',
                        });
                        calendar.render();
                    })();
                }
            });

        }
    </script>
    @endif
@endpush
