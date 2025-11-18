@extends('layouts.admin')

@section('page-title')
    {{ __('HR-Dashboard') }}
@endsection

@php
    $setting = App\Models\Utility::settings();
    $icons = \App\Models\Utility::get_file('uploads/job/icons/');
@endphp


<style type="text/css">
.leave-reason-column {
    white-space: normal; /* Allow text to wrap normally */
    word-wrap: break-word; /* Break long words when necessary */
    word-break: break-word; /* Ensure that long words or URLs break and wrap */
    overflow-wrap: break-word; /* Ensures word wrapping when text is too long */
}
</style>
@section('content')
    @if($isReviewer)
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif



        <div class="col-xxl-12">
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
                                    <h4 class="m-0 text-primary">{{ $countEmployee - $relievedCount }}</h4>
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

        <div class="col-xxl-12">
            <div class="row">
                <div class="col-xl-12">
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
                                            <th>{{ __('Employee') }}</th>
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
                                               
                                                <td>{{ !empty($attendance->employee) ? $attendance->employee->name : '' }}
                                                    @if($attendance->work_from_home)
                                                        <span class="badge bg-secondary p-1 px-1">WFH</span>
                                                    @endif

                                                    @if ($attendance->isInBreak)
                                                        <br /><span class="badge bg-danger p-1 px-1">On Break</span>
                                                    @endif
                                                </td>
                                                
                                                
                                                <!-- <td>{{ $attendance->status }}</td> -->
                                                <td>{{ $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00' }}
                                                    {!! Get_Device_Type_Icon($attendance->device_type_clockin,\Auth::user()->id) !!}
                                                </td>
                                                <td>
                                                
                                                    @if ($attendance->clock_out == '00:00:00' && $attendance->date < date('Y-m-d'))
                                                        <span class="badge bg-danger p-1 px-1">Missed Checkout</span>
                                                    @else
                                                        {{ $attendance->clock_out != '00:00:00' ? date('h:i A', strtotime($attendance->clock_out)) : '00:00' }}
                                                    @endif
                                                    {!! Get_Device_Type_Icon($attendance->device_type_clockout,\Auth::user()->id) !!}
                                                </td>
                                                <!-- <td>{{ $attendance->late }}</td>
                                                <td>{{ $attendance->early_leaving }}</td>
                                                <td>{{ $attendance->overtime }}</td> -->
                                                <td>{{ $attendance->checkout_time_diff != '' ? $attendance->checkout_time_diff : '00:00:00' }}</td>
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
                </div>
            </div>
        </div>
        

        @can('Manage Leave')
        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __("Today on Leave") }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Leave Date') }}</th>
                                    <!-- <th>{{ __('End Date') }}</th> -->
                                    <th>{{ __('Total Days') }}</th>
                                    <!-- <th>{{ __('Leave Reason') }}</th> -->
                                    <th>{{ __('status') }}</th>
                                    <th>{{ __('Applied On') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($Todayleaves as $leave)
                                    <tr>
                                        <td>{{ !empty($leave->employee_id) ? $leave->employees->name : '' }}
                                            </td>
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
                                        <!-- <td style="white-space: normal; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;width: 350px;">{{ $leave->leave_reason }}</td> -->
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
        @endcan
    @endif
@endsection



