{{ Form::open(['url' => 'leave/changeaction', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table table-bordered" id="pc-dt-simple">
                <colgroup>
                    <col style="width: 30%;">
                    <col style="width: 70%;">
                </colgroup>
                <tr role="row">
                    <th>{{ __('Employee') }}</th>
                    <td>{{ !empty($employee->name) ? $employee->name : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Leave Type ') }}</th>
                    <td><b>{{ !empty($leavetype->title) ? $leavetype->title : '' }}</b>
                        @if ($leave->leave_type_id == 5 && !empty($leave->early_time))
                            <span class="badge bg-primary">{{ $leave->early_time }}</span>
                        @endif
                    </td>
                </tr>
                @if ($leave->leave_type_id != 5)
                <tr>
                    <th>{{ __('Leave(Full/Half Day) ') }}</th>
                    <td>
                        @switch($leave->half_day_type)
                            @case('full_day')
                                <div class="badge bg-success p-2 px-3">{{ __('Full Day') }}</div>
                                @break
                            @case('morning')
                                <div class="badge bg-dark p-2 px-3">{{ __('First Half (Morning)') }}</div>
                                @break
                            @case('afternoon')
                                <div class="badge bg-danger p-2 px-3">{{ __('Second Half (Afternoon)') }}</div>
                                @break
                            @default
                                <div class="badge bg-secondary p-2 px-3">{{ __('Not Specified') }}</div>
                        @endswitch
                    </td>
                </tr>
                @endif
                <tr>
                    <th>{{ __('Appplied On') }}</th>
                    <td>{{ \Carbon\Carbon::parse($leave->applied_on)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Leave Date') }}</th>
                    <td>
                        @if($leave->start_date == $leave->end_date)
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}
                        @else
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} <b>To</b> {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                        @endif
                    </td>
                </tr>
                <!-- <tr>
                    <th>{{ __('End Date') }}</th>
                    <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
                </tr> -->
                <tr>
                    <th>{{ __('Leave Reason') }}</th>
                    <td>{{ !empty($leave->leave_reason) ? $leave->leave_reason : '' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>
                        @if ($leave->status == 'Pending')
                            <div class="badge bg-warning p-2 px-3 ">{{ $leave->status }}</div>
                        @elseif ($leave->status == 'In_Process')
                            <div class="badge p-2 px-3" style="background:#9D00FF;">In-Process</div>
                        @elseif ($leave->status == 'Manager_Approved')
                            <div class="badge p-2 px-3" style="background:#004000;">Manager-Approved</div>
                        @elseif ($leave->status == 'Manager_Rejected')
                            <div class="badge p-2 px-3" style="background:#800040;">Manager-Rejected</div>
                        @elseif ($leave->status == 'Partially_Approved')
                            <div class="badge p-2 px-3" style="background:#9ACD32;">Partially-Approved</div>
                        @elseif($leave->status == 'Approved')
                            <div class="badge bg-success p-2 px-3 ">{{ $leave->status }}</div>
                        @elseif($leave->status == "Reject")
                            <div class="badge bg-danger p-2 px-3 ">{{ $leave->status }}</div>
                        @elseif($leave->status == "Draft")
                            <div class="badge bg-info p-2 px-3 ">{{ $leave->status }}</div>
                        @elseif($leave->status == "Cancelled")
                            <div class="badge bg-danger p-2 px-3 ">{{ $leave->status }}</div>
                        @elseif($leave->status == 'Pre-Approved')
                            <div class="text-success"><b>{{ $leave->status }}</b></div>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>{{ __('Remark') }}</th>
                    <td>
                        {{ !empty($leave->remark) ? $leave->remark : '' }}
                    </td>
                </tr>
                @if($leave->status == "Cancelled")
                <tr>
                    <th>{{ __('Cancelled Remark') }}</th>
                    <td>
                       <span style="color:red;">{{ !empty($leave->remark_cancelled) ? $leave->remark_cancelled : '' }}</span>
                    </td>
                </tr>
                @endif
                <input type="hidden" value="{{ $leave->id }}" name="leave_id">
                <input type="hidden" value="index" name="leave_page">
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if(count($leaveManagers) > 0)
                <h6 class="mt-3 text-primary">{{ __('Leave Manager Actions') }}</h6>
                <table class="table table-bordered">
                    <colgroup>
                        <col style="width: 20%;">
                        <col style="width: 10%;">
                        <col style="width: 10%;">
                        <col style="width: 60%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>{{ __('Manager Name') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Remark') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaveManagers as $managerEntry)
                            <tr>
                                <td>{{ $managerEntry->manager->name ?? '-' }}</td>
                                <td>
                                    @if($managerEntry->status == 'Approved')
                                        <span class="badge bg-success p-2 px-3">{{ $managerEntry->status }}</span>
                                    @elseif($managerEntry->status == 'Reject')
                                        <span class="badge bg-danger p-2 px-3">{{ $managerEntry->status }}</span>
                                    @else
                                        <span class="badge bg-warning p-2 px-3">{{ $managerEntry->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ !empty($managerEntry->action_date) ? \Carbon\Carbon::parse($managerEntry->action_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td>{{ $managerEntry->remark ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>


{{ Form::close() }}
