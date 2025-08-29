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

                    {!! leaveStatusBadge($leave) !!}
                </td>
                <td>{{ \Carbon\Carbon::parse($leave->applied_on)->format('d/m/Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
