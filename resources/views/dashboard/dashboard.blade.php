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

<style type="text/css">
.leave-reason-column {
    white-space: normal; /* Allow text to wrap normally */
    word-wrap: break-word; /* Break long words when necessary */
    word-break: break-word; /* Ensure that long words or URLs break and wrap */
    overflow-wrap: break-word; /* Ensures word wrapping when text is too long */
}
</style>
@section('content')
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif


    @if (\Auth::user()->type == 'employee')
        <div class="row">
            <div class="col-xxl-12">

                {{-- start --}}
                <div class="row">

                    <div class="col-lg-4 col-md-6">
                        <div class="card stats-wrapper dash-info-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-primary">
                                            <i class="ti ti-users"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="mb-0">Attendance Summary</h5>
                                            <div>
                                                <p class="text-muted text-sm mb-0">
                                                    This Month Attandance: {{ $ThisMonthattendanceCount }}
                                                </p>
                                                <p class="text-muted text-sm mb-0">
                                                    Last Month Attandance: {{ $LastMonthattendanceCount }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card stats-wrapper dash-info-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-danger">
                                            <i class="ti ti-clock"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="mb-0">Hours Summary</h5>
                                            <div>
                                                <p class="text-muted text-sm mb-0">
                                                    This Month Hours: {{ $thisMonthNetHourse }}
                                                </p>
                                                <p class="text-muted text-sm mb-0">
                                                    Last Month Hours: {{ $lastMonthNetHourse }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">

                        <div class="card stats-wrapper dash-info-card">
                            <div class="card-body stats">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mb-3 mb-sm-0">
                                        <div class="d-flex align-items-center">
                                            <div class="badge theme-avtar bg-success">
                                                <i class="ti ti-car"></i>
                                            </div>
                                            <div class="ms-3">
                                                <small class="text-muted">{{ __('This Month') }}</small>
                                                <h6 class="m-0"><a
                                                    href="#">{{ __("Holiday ") }}</a></h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto text-end">
                                        <h4 class="m-0 text-info"> {{ $monthlyHolidayCount}}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-secondary">
                                            <i class="ti ti-file-report"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="mb-0">Allowed Leave</h5>
                                            <div>
                                                @foreach($leaveTypesAll as $type)
                                                    @if(  $type->title != 'Coming Late Office')
                                                        <p class="text-muted text-sm mb-0">
                                                            Total {{ $type->title }} : {{ $type->allowed_leave }}
                                                        </p>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        // Simulate the same leaveCounts structure
                        // $leaveCounts = [leave_type_id => ['Approved' => x, 'Rejected' => y, 'Pending' => z]];
                        // $leaveTypes = [leave_type_id => 'Leave Type Name'];
                    @endphp
                    {{-- Approved --}}
                    <div class="col-lg-4 col-md-6">
                        <div class="card stats-wrapper dash-info-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-warning">
                                            <i class="ti ti-info-circle"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="mb-0">Approved Leave</h5>
                                            <div>
                                                @foreach ($leaveCounts as $leaveTypeId => $statuses)
                                                    @if(  $leaveTypes[$leaveTypeId] != 'Coming Late Office')
                                                        <p class="text-muted text-sm mb-0">
                                                            Total {{ $leaveTypes[$leaveTypeId] ?? 'Unknown' }} :
                                                            {{ $statuses['Approved'] ?? 0 }}
                                                        </p>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Rejected --}}
                    <div class="col-lg-4 col-md-6">
                        <div class="card stats-wrapper dash-info-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-danger">
                                            <i class="ti ti-wallet"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="mb-0">Rejected Leave</h5>
                                            <div>
                                                @foreach ($leaveCounts as $leaveTypeId => $statuses)
                                                    @if(  $leaveTypes[$leaveTypeId] != 'Coming Late Office')
                                                        <p class="text-muted text-sm mb-0">
                                                            Total {{ $leaveTypes[$leaveTypeId] ?? 'Unknown' }} :
                                                            {{ $statuses['Rejected'] ?? 0 }}
                                                        </p>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pending --}}
                    <!-- <div class="col-lg-4 col-md-6">
                        <div class="card stats-wrapper dash-info-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-secondary">
                                            <i class="ti ti-file-report"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5 class="mb-0">Pending Leave</h5>
                                            <div>
                                                @foreach ($leaveCounts as $leaveTypeId => $statuses)
                                                    <p class="text-muted text-sm mb-0">
                                                        Total {{ $leaveTypes[$leaveTypeId] ?? 'Unknown' }} :
                                                        {{ $statuses['Pending'] ?? 0 }}
                                                    </p>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    
                </div>
            </div>
        </div>
       
        <div class="row">
        <div class="col-xxl-5 col-md-5" >
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
                    <iframe src="https://calendar.google.com/calendar/embed?src={{\Auth::user()->email}}&ctz=Asia/Kolkata&mode=AGENDA&showPrint=0" style="border: 0" width="100%" height="750" frameborder="0" scrolling="no"></iframe>
                </div>
            </div>
        </div>
        <div class="col-xxl-7 col-md-7">
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
                                            <div class="badge theme-avtar bg-success">
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
                        @if (!$hasOngoingBreak)
                            <div class="col-xxl-6 col-md-12" id="total-break-widget" style="display: none;">
                        @else
                            <div class="col-xxl-6 col-md-12" id="total-break-widget">
                        @endif
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="badge theme-avtar bg-danger">
                                                <i class="ti ti-clock"></i>
                                            </div>
                                            <div class="ms-3">
                                                <h5 class="mb-0">Total Break Log</h5>
                                                <p class="text-muted text-sm mb-0">
                                                    <span id="total_break_time">{{ $totalBreakDuration ?? '00:00:00' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            function updateBreakTime() {
                                let totalSeconds = {{ $totalSeconds }};
                                
                                setInterval(() => {
                                    let hours = Math.floor(totalSeconds / 3600);
                                    let minutes = Math.floor((totalSeconds % 3600) / 60);
                                    let seconds = totalSeconds % 60;

                                    document.getElementById("total_break_time").innerText = 
                                        `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

                                    totalSeconds++;
                                }, 1000);
                            }

                            @if ($hasOngoingBreak)
                                updateBreakTime(); // Start updating time if an active break is in progress
                            @endif
                        </script>
                    </div>
                    @endif
                    
                    
                    <div class="row">
                        @php
                            $hasOngoingBreak = isset($employeeAttendance) && $employeeAttendance->breaks()->whereNull('break_end')->exists();
                            $isClockedIn = isset($employeeAttendance) && $employeeAttendance->clock_in != null; 
                        @endphp

                        @if (!$hasOngoingBreak)  <!-- Hide buttons if a break is active -->
                            <!-- Clock In & Clock Out -->
                            <div class="col-6 border-right">
                                {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post', 'id' => 'clockInForm']) }}
                                @if (empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                    <button type="submit" value="0" name="in" id="clock_in" class="btn btn-primary w-100">{{ __('CLOCK IN') }}</button>

                                    @if($employeesinfo->work_from_home == 1)
                                        <div class="form-check">
                                            {{ Form::checkbox('work_from_home', 1, false, ['class' => 'form-check-input', 'id' => 'work_from_home_in']) }}
                                            <label class="form-check-label" for="work_from_home_in">{{ __('Work from Home') }}</label>
                                        </div>
                                    @endif
                                @else
                                    <button type="submit" class="btn btn-primary w-100 disabled" disabled>{{ __('CLOCK IN') }}</button>

                                    @if($employeesinfo->work_from_home == 1)
                                        <div class="form-check">
                                            {{ Form::checkbox(
                                                'work_from_home',
                                                1,
                                                $employeeAttendance->work_from_home == 1,  // Check if already enabled
                                                [
                                                    'class' => 'form-check-input update-work-from-home-class',
                                                    'id' => 'work_from_home_in',
                                                    'data-id' => $employeeAttendance->id
                                                ]
                                            ) }}
                                            <label class="form-check-label" for="work_from_home_in">{{ __('Work from Home') }}</label>
                                        </div>
                                    @endif
                                @endif
                                {{ Form::close() }}
                            </div>

                            <div class="col-6">
                                @if (!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                    {{ Form::model($employeeAttendance, ['route' => ['attendance.employee_clockout', $employeeAttendance->id], 'method' => 'post']) }}
                                    <button type="submit" id="clock_out" class="btn btn-danger w-100" onclick="return confirmClockOut();">{{ __('CLOCK OUT') }}</button>
                                @else
                                    <button type="submit" class="btn btn-danger w-100 disabled" disabled>{{ __('CLOCK OUT') }}</button>
                                @endif
                                {{ Form::close() }}
                            </div>
                        @endif
                    </div>

                    <!-- Break Time Management -->
                    <!-- Show Break Buttons Only if Employee is Clocked In -->
                    @if ($isClockedIn)
                        
                        @if (!$hasOngoingBreak)
                            <div id="break-log-card" style="display: none;">
                        @else
                            <div id="break-log-card">
                        @endif
                            <br />
                            <hr />
                            <div class="row mt-3">
                                <div class="col-6">
                                    <button id="start_break" class="btn btn-warning w-100"
                                        onclick="startBreak()" 
                                        {{ !empty($employeeAttendance) && $employeeAttendance->breaks()->whereNull('break_end')->exists() ? 'disabled' : '' }}>
                                        {{ __('Start Break') }}
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button id="end_break" class="btn btn-success w-100"
                                        onclick="endBreak()"
                                        {{ empty($employeeAttendance) || !$employeeAttendance->breaks()->whereNull('break_end')->exists() ? 'disabled' : '' }}>
                                        {{ __('End Break') }}
                                    </button>
                                </div>
                            </div>


                            <!-- Break Log -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5>{{ __('Break Log') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Break Start') }}</th>
                                                    <th>{{ __('Break End') }}</th>
                                                    <th>{{ __('Duration') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="break-log">
                                                @foreach ($breakLogs as $break)
                                                    <tr>
                                                        <td>{{ date('d/m/Y', strtotime($break->break_start_date)).' '.\Carbon\Carbon::parse($break->break_start)->format('h:i A') }}</td>
                                                        <td>
                                                            @if ($break->break_end)
                                                                {{date('d/m/Y', strtotime($break->break_end_date)).' '. \Carbon\Carbon::parse($break->break_end)->format('h:i A')}}
                                                            @else
                                                                <span class="badge bg-danger p-1 px-1">In Progress</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($break->break_end)
                                                                @php
                                                                    $start = \Carbon\Carbon::parse($break->break_start);
                                                                    $end = \Carbon\Carbon::parse($break->break_end);
                                                                    $diff = $start->diff($end);
                                                                    echo sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
                                                                @endphp
                                                            @else
                                                                --
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody> 
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif


                    @if(count($FindOnBreakEmployee) > 0)
                        <br />
                        <hr />
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('ON-BREAK') }}</h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $badgeClasses = ['bg-warning', 'bg-success', 'bg-danger', 'bg-info'];
                                @endphp

                                @foreach ($FindOnBreakEmployee as $OnBreakattendance)
                                    @php
                                        $randomClass = $badgeClasses[array_rand($badgeClasses)];
                                    @endphp
                                    <div class="badge {{ $randomClass }} p-2 px-3">
                                        {{ !empty($OnBreakattendance->employee) ? $OnBreakattendance->employee->name : '' }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <script>
                // Function to show confirmation alert before clocking out
                function confirmClockOut() {
                    return confirm("Are you sure you want to clock out?");
                }
            </script>

            <script>
                function startBreak() {
                    fetch("{{ route('attendance.break_start') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        },
                        body: JSON.stringify({ employee_id: {{ \Auth::user()->employee->id }} })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            alert(data.message);
                            document.getElementById("start_break").disabled = true;
                            document.getElementById("end_break").disabled = false;
                            location.reload(); // Refresh to update break log
                        }
                    })
                    .catch(error => console.error("Error:", error));
                }

                function endBreak() {
                    fetch("{{ route('attendance.break_end') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        },
                        body: JSON.stringify({ employee_id: {{ \Auth::user()->employee->id }} })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            alert(data.message);
                            document.getElementById("start_break").disabled = false;
                            document.getElementById("end_break").disabled = true;
                            location.reload(); // Refresh to update break log
                        }
                    })
                    .catch(error => console.error("Error:", error));
                }
            </script>


            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Last 5 Days Attandance') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('CLOCK IN') }}</th>
                                    <th>{{ __('CLOCK OUT') }}</th>
                                    <th>{{ __('TOTAL HOURS') }}</th>
                                    <th>{{ __('Break Log') }}</th> 
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach ($attendanceEmployee as $attendance)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}
                                            @if($attendance->work_from_home)
                                                <span class="badge bg-secondary p-1 px-1">WFH</span>
                                            @endif
                                        </td>
                                        @if (\Auth::user()->type != 'employee')
                                            <td>{{ !empty($attendance->employee) ? $attendance->employee->name : '' }}</td>
                                        @endif
                                        <td>{{ $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00' }}
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
                                        </td>
                                        <td>{{ $attendance->checkout_time_diff != '' ? $attendance->checkout_time_diff : '00:00:00' }}</td>
                                        <td>{{ $attendance->totalBreakDuration ?? '00:00:00' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
        @can('Manage Leave')
        <div class="col-xl-5 col-lg-5 col-md-5">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Scheduled Leave Overview') }}</h5>
                </div>
                <div class="card-body">
                    {{-- <h5> </h5> --}}
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple-123">
                            <thead>
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <th>{{ __('Employee') }}</th>
                                    @endif
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Leave Date') }}</th>
                                    <!-- <th>{{ __('End Date') }}</th> -->
                                    <!-- <th>{{ __('Total Days') }}</th> -->
                                    <!-- <th>{{ __('Leave Reason') }}</th> -->
                                    <th>{{ __('status') }}</th>
                                    <!-- <th>{{ __('Applied On') }}</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaves as $leave)
                                    <tr>
                                        @if (\Auth::user()->type != 'employee')
                                            <td>{{ !empty($leave->employee_id) ? $leave->employees->name : '' }}
                                            </td>
                                        @endif
                                        <td>{{ !empty($leave->leave_type_id) ? $leave->leaveType->title : '' }}
                                            @if ($leave->leave_type_id == 5 && !empty($leave->early_time))
                                                <br /> <span class="badge bg-primary">{{ $leave->early_time }}</span>
                                            @endif
                                            <br />
                                            <?php echo indexHalfLabel($leave); ?>
                                        </td>
                                        <td>
                                            @if($leave->start_date == $leave->end_date)
                                                {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} <b>To</b> {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                                            @endif
                                            
                                        </td>
                                        <!-- <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td> -->

                                        <!-- <td>{{ $leave->total_leave_days }}</td> -->
                                        <!-- <td style="white-space: normal; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;width: 350px;">{{ $leave->leave_reason }}</td> -->
                                        <td>
                                            @if ($leave->status == 'Pending')
                                                <div class="badge bg-warning p-2 px-3 ">{{ $leave->status }}</div>
                                            @elseif($leave->status == 'Approved')
                                                <div class="badge bg-success p-2 px-3 ">{{ $leave->status }}</div>
                                            @elseif($leave->status == "Reject")
                                                <div class="badge bg-danger p-2 px-3 ">{{ $leave->status }}</div>
                                            @elseif($leave->status == "Draft")
                                                <div class="badge bg-info p-2 px-3 ">{{ $leave->status }}</div>
                                            @elseif($leave->status == "Cancelled")
                                                <div class="badge bg-danger p-2 px-3 ">{{ $leave->status }}</div>
                                            @elseif($leave->status == 'Pre-Approved')
                                                <div class="badge bg-success p-2 px-3 ">{{ $leave->status }}</div>
                                            @endif
                                        </td>
                                        <!-- <td>{{ \Carbon\Carbon::parse($leave->applied_on)->format('d/m/Y') }}</td> -->
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($leaves_cc)
        <div class="col-xl-7 col-lg-7 col-md-7">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Team Members\' CC Leave Overview') }}</h5>
                </div>
                <div class="card-body">
                    {{-- <h5> </h5> --}}
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Leave Date') }}</th>
                                    <!-- <th>{{ __('End Date') }}</th> -->
                                    <!-- <th>{{ __('Total Days') }}</th> -->
                                    <!-- <th>{{ __('Leave Reason') }}</th> -->
                                    <th>{{ __('status') }}</th>
                                    <!-- <th>{{ __('Applied On') }}</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaves_cc as $leave)
                                    <tr>
                                        <td>{{ !empty($leave->employee_id) ? $leave->employees->name : '' }}
                                            </td>
                                        <td>{{ !empty($leave->leave_type_id) ? $leave->leaveType->title : '' }}
                                            <br />
                                            <?php echo indexHalfLabel($leave); ?>

                                            @if ($leave->leave_type_id == 5 && !empty($leave->early_time))
                                                <span class="badge bg-primary">{{ $leave->early_time }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($leave->start_date == $leave->end_date)
                                                {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} <b>To</b> {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                                            @endif
                                            
                                        </td>
                                        <!-- <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td> -->

                                        <!-- <td>{{ $leave->total_leave_days }}</td> -->
                                        <!-- <td style="white-space: normal; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;width: 350px;">{{ $leave->leave_reason }}</td> -->
                                        <td>
                                            {!! leaveStatusBadge($leave) !!}
                                        </td>
                                        <!-- <td>{{ \Carbon\Carbon::parse($leave->applied_on)->format('d/m/Y') }}</td> -->
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endcan

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
    @elseif (\Auth::user()->type != 'employee' && \Auth::user()->type != 'client')
        <div class="col-xxl-12">

            {{-- start --}}
            <div class="row">

                <div class="col-lg-3 col-md-6">

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
                                                href="#">{{ __('Staff') }}</a></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-primary">{{ $countEmployee - $relievedCount}}</h4>
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

                <div class="col-lg-3 col-md-6">

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
                                                href="#">{{ __("Today's Not Clock In") }}</a></h6>
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

                <div class="col-lg-3 col-md-6">

                    <div class="card stats-wrapper dash-info-card">
                        <div class="card-body stats">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-warning">
                                            <i class="ti ti-clock"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0"><a
                                                href="#">{{ __("Today's Clock In") }}</a></h6>
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

                <div class="col-lg-3 col-md-6">

                    <div class="card stats-wrapper dash-info-card">
                        <div class="card-body stats">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-danger">
                                            <i class="ti ti-user"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0"><a
                                                href="#">{{ __("Today's Clock Out") }}</a></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-danger">{{ $todaysClockOutCount }}</h4>
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
                
                <div class="col-xl-7">

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
                                            <th>{{ __('Leave Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($notClockInDetails as $notClockIn)
                                            <tr>
                                                <td>{{ $notClockIn['employee_name'] }}</td>
                                                <td><span class="absent-btn {{ $notClockIn['leave_type'] != 'Absent' ? 'text-danger' : '' }}">
                                                        {{ $notClockIn['leave_type'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($notClockIn['leave_id'] != 0)
                                                        {!! leaveStatusBadgeList($notClockIn['leave_id']) !!}
                                                    @endif
                                                </td>
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
                                            <th>{{ __('Total Hours') }}</th>
                                            <th>{{ __('Total Break Log') }}</th>
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

                                                        @if ($attendance->isInBreak)
                                                            <br /><span class="badge bg-danger p-1 px-1">On Break</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                
                                                <!-- <td>{{ $attendance->status }}</td> -->
                                                @php
                                                    $clockInValue = $attendance->clock_in ?? '00:00:00';
                                                    $clockOutValue = $attendance->clock_out ?? '00:00:00';
                                                    $displayClockIn = $clockInValue != '00:00:00' ? \Auth::user()->timeFormat($clockInValue) : '00:00';
                                                    $displayClockOut = $clockOutValue != '00:00:00' ? \Auth::user()->timeFormat($clockOutValue) : '00:00';
                                                @endphp
                                                <td>
                                                    <a href="javascript:void(0)"
                                                       class="edit-time btn-link"
                                                       id="clock-in-{{ $attendance->id }}"
                                                       data-id="{{ $attendance->id }}"
                                                       data-time="{{ \Carbon\Carbon::createFromFormat('H:i:s', $clockInValue)->format('H:i') }}"
                                                       data-date="{{ $attendance->date }}"
                                                       data-clockout="{{ \Carbon\Carbon::createFromFormat('H:i:s', $clockOutValue)->format('H:i') }}"
                                                       data-checkoutdate="{{ $attendance->checkout_date ?? '' }}"
                                                       data-employee="{{ $attendance->employee->name ?? '' }}">
                                                       <span class="clock-text">{{ $displayClockIn }}</span>
                                                    </a>

                                                    {!! Get_Device_Type_Icon($attendance->device_type_clockin,\Auth::user()->id) !!}
                                                </td>
                                                <td id="clock-out-{{ $attendance->id }}">
                                                    <span class="clock-out-text">{{ $displayClockOut }}</span>
                                                    {!! Get_Device_Type_Icon($attendance->device_type_clockout,\Auth::user()->id) !!}
                                                </td>
                                                <!-- <td>{{ $attendance->late }}</td>
                                                <td>{{ $attendance->early_leaving }}</td>
                                                <td>{{ $attendance->overtime }}</td> -->
                                                <td id="checkout-time-diff-{{ $attendance->id }}">
                                                    {{ $attendance->checkout_time_diff ?? '00:00:00' }}
                                                </td>
                                                <td>{{ $attendance->totalBreakDuration ?? '00:00:00' }}</td>
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


                    <!-- Edit Time Modal -->
                    <div class="modal fade" id="editTimeModal" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                        <form id="edit-time-form">
                          @csrf
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="editTimeModalLabel">Edit Attendance Time</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <input type="hidden" id="attendance_id" name="id">

                              <div class="row mb-3">
                                <div class="col-md-6">
                                  <label class="form-label">Clock In Date</label>
                                  <input type="date" id="attendance_date" name="date" class="form-control" required readonly>
                                </div>
                                <div class="col-md-6">
                                  <label class="form-label">Clock In Time</label>
                                  <input type="time" id="clock_in_time" name="clock_in" class="form-control" required>
                                </div>
                              </div>

                              <div class="row mb-3">
                                <div class="col-md-6">
                                  <label class="form-label">Clock Out Date (optional)</label>
                                  <input type="date" id="checkout_date" name="checkout_date" class="form-control">
                                  <small class="text-muted">Leave empty for NULL</small>
                                </div>
                                <div class="col-md-6">
                                  <label class="form-label">Clock Out Time (optional)</label>
                                  <input type="time" id="clock_out_time" name="clock_out" class="form-control">
                                  <small class="text-muted">Leave empty to set default 00:00:00</small>
                                </div>
                              </div>

                              <div id="edit-time-error" class="text-danger small" style="display:none;"></div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-primary btn-sm" id="save-time-btn">Save</button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>


                </div>


               

                <div class="col-xl-5">
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
        

        @can('Manage Leave')
        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs" id="leaveTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="people-on-leave-tab" data-bs-toggle="tab" href="#people-on-leave" role="tab" aria-controls="people-on-leave" aria-selected="true">
                                <h6><span class="text-danger">{{ __('Today on Leave') }} ({{count($Todayleaves)}})</span></h6>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="next-working-day-leave-tab" data-bs-toggle="tab" href="#next-working-day-leave" role="tab" aria-controls="next-working-day-leave" aria-selected="true">
                                <h6><span class="text-success">Tomorrow on Leave ({{ $nextWorkingDay->format('d/m/Y') }} | {{ $nextWorkingDay->format('l') }})</span></h6>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pending-tab" data-bs-toggle="tab" href="#pending-leaves" role="tab" aria-controls="pending-leaves" aria-selected="false">
                                <h6><span class="text-primary">{{ __('Pending Leave Applications') }} ({{count($leaves)}})</span></h6>
                            </a>
                        </li>
                        
                    </ul>
                </div>

                <div class="tab-content" id="leaveTabsContent">
                    <!-- Pending Leave Applications Tab -->
                    <div class="tab-pane fade" id="pending-leaves" role="tabpanel" aria-labelledby="pending-tab">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table datatable">
                                    <thead>
                                        <tr>
                                            @if (\Auth::user()->type != 'employee')
                                                <th>{{ __('Employee') }}</th>
                                            @endif
                                            <th>{{ __('Leave Type') }}</th>
                                            <th>{{ __('Leave Date') }}</th>
                                            <!-- <th>{{ __('End Date') }}</th> -->
                                            <th>{{ __('Total Days') }}</th>
                                            <th>{{ __('Leave Reason') }}</th>
                                            <th>{{ __('status') }}</th>
                                            <th>{{ __('Applied On') }}</th>
                                            <th width="200px">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($leaves as $leave)
                                            <tr>
                                                @if (\Auth::user()->type != 'employee')
                                                    <td>{{ !empty($leave->employee_id) ? $leave->employees->name : '' }}
                                                    </td>
                                                @endif
                                                <td>{{ !empty($leave->leave_type_id) ? $leave->leaveType->title : '' }}
                                                    @if ($leave->leave_type_id == 5 && !empty($leave->early_time))
                                                        <br />
                                                        <span class="badge bg-primary">{{ $leave->early_time }}</span>
                                                    @endif
                                                    <br />
                                                    <?php echo indexHalfLabel($leave); ?>
                                                </td>
                                                <td>
                                                    @if($leave->start_date == $leave->end_date)
                                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}
                                                    @else
                                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} <b>To</b> {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                                                    @endif
                                                    
                                                </td>
                                                <!-- <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td> -->

                                                <td>{{ $leave->total_leave_days }}</td>
                                                <td style="white-space: normal; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;width: 350px;">{{ $leave->leave_reason }}</td>
                                                <td>
                                                    {!! leaveStatusBadge($leave) !!}
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($leave->applied_on)->format('d/m/Y') }}</td>

                                                <td class="Action">
                                                    <div class="dt-buttons">
                                                    <span>

                                                        @if (\Auth::user()->type != 'employee')
                                                            <div class="action-btn bg-success me-2">
                                                                <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                    data-size="lg"
                                                                    data-url="{{ URL::to('leave/' . $leave->id . '/action') }}"
                                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                    title="" data-title="{{ __('Leave Action') }}"
                                                                    data-bs-original-title="{{ __('Manage Leave') }}">
                                                                    <span class="text-white"><i class="ti ti-caret-right"></i></span>
                                                                </a>
                                                            </div>
                                                            @can('Edit Leave')
                                                                @if(\Auth::user()->type != 'CEO')
                                                                    <div class="action-btn bg-info me-2">
                                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                            data-size="lg"
                                                                            data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                            title="" data-title="{{ __('Edit Leave') }}"
                                                                            data-bs-original-title="{{ __('Edit') }}">
                                                                            <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            @endcan
                                                            @can('Delete Leave')
                                                                @if (\Auth::user()->type != 'employee' && \Auth::user()->type != 'CEO')
                                                                    <div class="action-btn">
                                                                        {!! Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['leave.destroy', $leave->id],
                                                                            'id' => 'delete-form-' . $leave->id,
                                                                        ]) !!}
                                                                        <a href="#"
                                                                            class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="Delete" aria-label="Delete"><span class="text-white"><i
                                                                                class="ti ti-trash"></i></span></a>
                                                                        </form>
                                                                    </div>
                                                                @endif
                                                            @endcan
                                                        @else
                                                            <div class="action-btn bg-success me-2">
                                                                <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                    data-size="lg"
                                                                    data-url="{{ URL::to('leave/' . $leave->id . '/action') }}"
                                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                    title="" data-title="{{ __('Leave Action') }}"
                                                                    data-bs-original-title="{{ __('Manage Leave') }}">
                                                                    <span class="text-white"><i class="ti ti-caret-right"></i></span>
                                                                </a>
                                                            </div>
                                                        @endif

                                                        @if ($leave->status == "Draft")
                                                            <div class="action-btn bg-info me-2">
                                                                <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                    data-size="lg"
                                                                    data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                    title="" data-title="{{ __('Edit Leave') }}"
                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                </a>
                                                            </div>
                                                            @if (\Auth::user()->type != 'CEO')
                                                                <div class="action-btn bg-danger">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['leave.destroy', $leave->id],
                                                                        'id' => 'delete-form-' . $leave->id,
                                                                    ]) !!}
                                                                    <a href="#"
                                                                        class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-bs-original-title="Delete" aria-label="Delete"><span class="text-white"><i
                                                                            class="ti ti-trash"></i></span></a>
                                                                    </form>
                                                                </div>
                                                            @endif
                                                        @endif

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

                    <!-- People Today on Leave Tab -->
                    <div class="tab-pane fade" id="next-working-day-leave" role="tabpanel" aria-labelledby="next-working-day-leave-tab">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table datatable">
                                    <thead>
                                        <tr>
                                            @if (\Auth::user()->type != 'employee')
                                                <th>{{ __('Employee') }}</th>
                                            @endif
                                            <th>{{ __('Leave Type') }}</th>
                                            <th>{{ __('Leave Date') }}</th>
                                            <!-- <th>{{ __('End Date') }}</th> -->
                                            <th>{{ __('Total Days') }}</th>
                                            <th>{{ __('Leave Reason') }}</th>
                                            <th>{{ __('status') }}</th>
                                            <th>{{ __('Applied On') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($NextDayLeaves as $leave)
                                            <tr>
                                                @if (\Auth::user()->type != 'employee')
                                                    <td>{{ !empty($leave->employee_id) ? $leave->employees->name : '' }}
                                                    </td>
                                                @endif
                                                <td>{{ !empty($leave->leave_type_id) ? $leave->leaveType->title : '' }}
                                                    @if ($leave->leave_type_id == 5 && !empty($leave->early_time))
                                                        <span class="badge bg-primary">{{ $leave->early_time }}</span>
                                                    @endif
                                                    <br />
                                                    <?php echo indexHalfLabel($leave); ?>
                                                </td>
                                                <td>
                                                    @if($leave->start_date == $leave->end_date)
                                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}
                                                    @else
                                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} <b>To</b> {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                                                    @endif
                                                    
                                                </td>
                                                <!-- <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td> -->

                                                <td>{{ $leave->total_leave_days }}</td>
                                                <td style="white-space: normal; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;width: 350px;">{{ $leave->leave_reason }}</td>
                                                <td>
                                                    {!! leaveStatusBadge($leave) !!}
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($leave->applied_on)->format('d/m/Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- People Today on Leave Tab -->
                    <div class="tab-pane fade show active" id="people-on-leave" role="tabpanel" aria-labelledby="people-on-leave-tab">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table datatable">
                                    <thead>
                                        <tr>
                                            @if (\Auth::user()->type != 'employee')
                                                <th>{{ __('Employee') }}</th>
                                            @endif
                                            <th>{{ __('Leave Type') }}</th>
                                            <th>{{ __('Leave Date') }}</th>
                                            <!-- <th>{{ __('End Date') }}</th> -->
                                            <th>{{ __('Total Days') }}</th>
                                            <th>{{ __('Leave Reason') }}</th>
                                            <th>{{ __('status') }}</th>
                                            <th>{{ __('Applied On') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($Todayleaves as $leave)
                                            <tr>
                                                @if (\Auth::user()->type != 'employee')
                                                    <td>{{ !empty($leave->employee_id) ? $leave->employees->name : '' }}
                                                    </td>
                                                @endif
                                                <td>{{ !empty($leave->leave_type_id) ? $leave->leaveType->title : '' }}
                                                    @if ($leave->leave_type_id == 5 && !empty($leave->early_time))
                                                        <span class="badge bg-primary">{{ $leave->early_time }}</span>
                                                    @endif
                                                    <br />
                                                   <?php echo indexHalfLabel($leave); ?>
                                                </td>
                                                <td>
                                                    @if($leave->start_date == $leave->end_date)
                                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}
                                                    @else
                                                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} <b>To</b> {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                                                    @endif
                                                    
                                                </td>
                                                <!-- <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td> -->

                                                <td>{{ $leave->total_leave_days }}</td>
                                                <td style="white-space: normal; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;width: 350px;">{{ $leave->leave_reason }}</td>
                                                <td>
                                                    {!! leaveStatusBadge($leave) !!}
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($leave->applied_on)->format('d/m/Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        <div class="col-xl-12 col-lg-12 col-md-12" style="display: none;">
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
        // const dataTablePending = new simpleDatatables.DataTable("#pc-dt-simple-pending");
    </script>

    <script>
        document.getElementById('clockInForm').addEventListener('submit', function(event) {
            event.preventDefault();
            var button = document.getElementById('clock_in');
            button.disabled = true;
            button.innerText = "Processing..."; 
            button.classList.remove("btn-primary");
            button.classList.add("btn-secondary");
            this.submit();
        });
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkbox = document.querySelector('.update-work-from-home-class');

            if (checkbox) {
                checkbox.addEventListener('change', function () {
                    const isChecked = this.checked ? 1 : 0;
                    const attendanceId = this.getAttribute('data-id');

                    fetch("{{ url('attendanceemployee/update-work-from-home') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id: attendanceId,
                            work_from_home: isChecked
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Work from Home status updated.');
                            // alert('Work from Home status updated.');
                            toastr.success(data.message);
                        } else {
                            alert('Error updating status');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            }
        });
    </script>

    <script>
        function toggleBreakLog() {
            let workFromHome = document.getElementById('work_from_home_in');
            let breakLogCard = document.getElementById('break-log-card');
            let breakWidgetCard = document.getElementById('break-widget-card');
            let totalBreakWidget = document.getElementById('total-break-widget');
            const show = workFromHome && workFromHome.checked;

            if (breakLogCard) breakLogCard.style.display = show ? 'block' : 'none';
            if (breakWidgetCard) breakWidgetCard.style.display = show ? 'block' : 'none';
            if (totalBreakWidget) totalBreakWidget.style.display = show ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const workFromHomeCheckbox = document.getElementById('work_from_home_in');

            if (workFromHomeCheckbox) {
                toggleBreakLog(); // On page load
                workFromHomeCheckbox.addEventListener('change', toggleBreakLog); // On change
            }
        });
    </script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- bootstrap CSS/JS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script> -->
    <script>
    (function() {
        function detectDeviceByWidth() {
            let width = screen.width;
            return (width <= 768) ? 'mobile' : 'desktop'; // threshold can be changed
        }

        let device = detectDeviceByWidth();

        // Save in cookie for PHP
        document.cookie = "device_type=" + device + "; path=/; max-age=" + (60*60*24) + "; SameSite=Lax";

        // Also save in Laravel session (optional, for backend use)
        fetch("{{ url('/set-device') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({ device_type: device })
        });
    })();
    </script>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // delegated click handler so it works across pagination/pages
        document.addEventListener('click', function (e) {
          var anchor = e.target.closest('.edit-time');
          if (!anchor) return;

          e.preventDefault();

          var id = anchor.dataset.id;
          var time = anchor.dataset.time || '';
          var date = anchor.dataset.date || '';
          var clockout = anchor.dataset.clockout || '';
          var checkoutdate = anchor.dataset.checkoutdate || '';
          var employee = anchor.dataset.employee || '';

          document.getElementById('attendance_id').value = id;
          document.getElementById('clock_in_time').value = time;
          document.getElementById('attendance_date').value = date;
          document.getElementById('clock_out_time').value = clockout;
          document.getElementById('checkout_date').value = checkoutdate;

          // Set modal title with employee name
          var title = document.getElementById('editTimeModalLabel');
          title.textContent = 'Edit Attendance Time (' + employee + ')';

          document.getElementById('edit-time-error').style.display = 'none';

          var modalEl = document.getElementById('editTimeModal');
          var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
          modal.show();
        });

      // AJAX submit
      document.getElementById('edit-time-form').addEventListener('submit', function(e){
        e.preventDefault();

        var id = document.getElementById('attendance_id').value;
        var clock_in = document.getElementById('clock_in_time').value; // HH:MM
        var date = document.getElementById('attendance_date').value;
        var clock_out = document.getElementById('clock_out_time').value || null; // optional
        var checkout_date = document.getElementById('checkout_date').value || null;
        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (!id || !clock_in || !date) {
          var err = document.getElementById('edit-time-error');
          err.style.display = 'block';
          err.textContent = 'Please fill required fields.';
          return;
        }

        var saveBtn = document.getElementById('save-time-btn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        fetch("{{ url('/attendanceemployee/update-time') }}", {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            id: id,
            clock_in: clock_in,
            date: date,
            clock_out: clock_out,
            checkout_date: checkout_date
          })
        }).then(function(res) {
          return res.json();
        }).then(function(json) {
          saveBtn.disabled = false;
          saveBtn.textContent = 'Save';

          if (json.success) {
            // Update UI: clock-in anchor text & data attributes
            var anchor = document.getElementById('clock-in-' + id);
            if (anchor) {
              anchor.setAttribute('data-time', json.clock_in_24 || clock_in);
              anchor.setAttribute('data-date', date);
              anchor.setAttribute('data-clockout', json.clock_out_24 || '00:00');
              anchor.setAttribute('data-checkoutdate', json.checkout_date || '');
              var span = anchor.querySelector('.clock-text');
              if (span) span.textContent = json.clock_in_display || (clock_in + ' AM');
            }

            // Update clock-out display cell
            var coEl = document.getElementById('clock-out-' + id);
            if (coEl) {
              var textEl = coEl.querySelector('.clock-out-text');
              if (textEl) textEl.textContent = json.clock_out_display || '00:00';
            }

            // Update checkout_time_diff display cell
            var diffEl = document.getElementById('checkout-time-diff-' + id);
            if (diffEl) diffEl.textContent = json.checkout_time_diff || '00:00:00';

            // Close modal
            var modalEl = document.getElementById('editTimeModal');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.hide();
          } else {
            var err = document.getElementById('edit-time-error');
            err.style.display = 'block';
            err.textContent = json.message || 'Update failed';
          }
        }).catch(function(err) {
          saveBtn.disabled = false;
          saveBtn.textContent = 'Save';
          var eDiv = document.getElementById('edit-time-error');
          eDiv.style.display = 'block';
          eDiv.textContent = 'Network error';
          console.error(err);
        });

      });
    });
    </script>


@endpush
