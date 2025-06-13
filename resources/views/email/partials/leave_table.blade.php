<table>
    <thead>
        <tr>
            <th>Employee</th>
            <th>Leave Type</th>
            <th>Leave Date</th>
            <th>Total Days</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Applied On</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($leaves as $leave)
            <tr>
                <td>{{ $leave->employees->name ?? 'N/A' }}</td>
                <td>
                    {{ $leave->leaveType->title ?? 'N/A' }}
                    @if ($leave->leave_type_id == 5 && $leave->early_time)
                        <br><span class="badge bg-primary">{{ $leave->early_time }}</span>
                    @endif
                    @if ($leave->half_day_type)
                        <br>
                        @if ($leave->half_day_type == 'morning')
                            <span class="badge bg-dark">1st H/D (Morning)</span>
                        @elseif ($leave->half_day_type == 'afternoon')
                            <span class="badge bg-danger">2nd H/D (Afternoon)</span>
                        @endif
                    @endif
                </td>
                <td>
                    @if($leave->start_date == $leave->end_date)
                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}
                    @else
                        {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}
                        to
                        {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                    @endif
                </td>
                <td>{{ $leave->total_leave_days }}</td>
                <td>{{ $leave->leave_reason }}</td>
                <td>
                    @php
                        $status = $leave->status;
                    @endphp
                    @if ($leave->status == 'Pending')
                        <div class="badge bg-warning p-2 px-3 ">{{ $status }}</div>
                    @elseif ($leave->status == 'In_Process')
                        <div class="badge p-2 px-3" style="background:#9D00FF;">In-Process</div>
                    @elseif ($leave->status == 'Manager_Approved')
                        <div class="badge p-2 px-3" style="background:#50C878;">Awaiting Director Approval</div>
                    @elseif ($leave->status == 'Manager_Rejected')
                        <div class="badge p-2 px-3" style="background:#D2042D;">Manager-Rejected</div>
                    @elseif ($leave->status == 'Partially_Approved')
                        <div class="badge p-2 px-3" style="background:#9ACD32;">Partially-Approved</div>
                    <!-- @elseif (in_array($leave->status, ['In_Process', 'Manager_Approved','Partially_Approved']) && \Auth::user()->type === 'employee')
                        <div class="badge p-2 px-3" style="background:#FA5F55;">In-Process</div> -->
                    @elseif($leave->status == 'Approved')
                        <div class="badge bg-success p-2 px-3 ">{{ $status }}</div>
                    @elseif($leave->status == "Reject")
                        <div class="badge bg-danger p-2 px-3 ">{{ $status }}</div>
                    @elseif($leave->status == "Draft")
                        <div class="badge bg-info p-2 px-3 ">{{ $status }}</div>
                    @elseif($leave->status == "Cancelled")
                        <div class="badge bg-danger p-2 px-3 ">{{ $status }}</div>
                    @elseif($leave->status == 'Pre-Approved')
                        <div class="text-success"><b>{{ $status }}</b></div>
                    @endif
                    
                </td>
                <td>{{ \Carbon\Carbon::parse($leave->applied_on)->format('d/m/Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
