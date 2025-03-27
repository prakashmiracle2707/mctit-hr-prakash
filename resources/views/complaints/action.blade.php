{{ Form::open(['url' => 'complaints/changeaction', 'method' => 'post']) }}

<style>
    .simple-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .simple-table th, .simple-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e9ecef;
        text-align: left;
        vertical-align: top;
    }

    .simple-table th {
        width: 25%;
        color: #6c757d;
        font-weight: 500;
        background: #f9f9f9;
    }

    .badge-status {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 13px;
        display: inline-block;
    }

    .status-open       { background-color: #e3f2fd; color: #0d6efd; }
    .status-review     { background-color: #f0f0f0; color: #6c757d; }
    .status-progress   { background-color: #fff3cd; color: #856404; }
    .status-resolved   { background-color: #d1ecf1; color: #0c5460; }
    .status-rejected   { background-color: #f8d7da; color: #721c24; }

    .btn-view {
        color: #0d6efd;
        font-weight: 500;
        text-decoration: underline;
    }

    .modal-footer .btn {
        min-width: 120px;
    }
</style>

<div class="modal-body">
    <table class="simple-table">
        <tr>
            <th>{{ __('Title') }}</th>
            <td colspan="3">{{ $complaint->title->name ?? '-' }}</td>
        </tr>

        <tr>
            <th>{{ __('Employee') }}</th>
            <td>{{ ucfirst($complaint->employee->name ?? '-') }}</td>
            <th>{{ __('Priority') }}</th>
            <td>
                <span class="badge bg-{{ $complaint->priority == 'High' ? 'danger' : ($complaint->priority == 'Medium' ? 'warning' : 'success') }}">
                    {{ strtoupper($complaint->priority) }}
                </span>
            </td>
        </tr>

        <tr>
            <th>{{ __('Category') }}</th>
            <td>{{ $complaint->category->name ?? '-' }}</td>
            <th>{{ __('Created On') }}</th>
            <td colspan="3">{{ \Carbon\Carbon::parse($complaint->created_at)->format('d/m/Y') }}</td>
        </tr>

        <tr>
            <th>{{ __('Description') }}</th>
            <td colspan="3">{{ $complaint->description ?? '-' }}</td>
        </tr>

        <tr>
            <th>{{ __('Status') }}</th>
            <td colspan="3">
                @php
                    $status = strtoupper($complaint->status);
                @endphp

                @switch($complaint->status)
                    @case('Open')
                        <div class="text-primary"><b>{{ $status }}</b></div>
                        @break

                    @case('Under Review')
                        <div class="text-secondary"><b>{{ $status }}</b></div>
                        @break

                    @case('In Progress')
                        <div class="text-warning"><b>{{ $status }}</b></div>
                        @break

                    @case('Resolved')
                        <div class="text-info"><b>{{ $status }}</b></div>
                        @break

                    @case('Closed')
                        <div class="text-success"><b>{{ $status }}</b></div>
                        @break

                    @case('Rejected')
                        <div class="text-danger"><b>{{ $status }}</b></div>
                        @break

                    @default
                        <div><b>{{ $status }}</b></div>
                @endswitch
            </td>
        </tr>
       
        @if(((!empty($isReadOnly) && $isReadOnly) || \Auth::user()->type == 'company' || \Auth::user()->type == 'management' || \Auth::user()->type == 'CEO') && $complaint->status != 'Resolved' && $complaint->status != 'Closed')
        <tr>
            <th>{{ __('Change Status') }}</th>
            <td colspan="3">
                
                @php
                    $statusOptions = ['Open', 'Under Review', 'In Progress', 'Resolved', 'Rejected'];
                    $statusClass = match($complaint->status) {
                        'Open' => 'status-open',
                        'Under Review' => 'status-review',
                        'In Progress' => 'status-progress',
                        'Resolved' => 'status-resolved',
                        'Rejected' => 'status-rejected',
                        default => ''
                    };
                @endphp

                
                {{ Form::select('status', array_combine($statusOptions, $statusOptions), $complaint->status, [
                    'class' => 'form-select form-select-sm',
                ]) }}
                
            </td>
        </tr>
        @endif
        
        <tr>
            <th>{{ __('Remark') }}</th>
            <td colspan="3">
                @if(((!empty($isReadOnly) && $isReadOnly) || \Auth::user()->type == 'company' || \Auth::user()->type == 'management' || \Auth::user()->type == 'CEO') && $complaint->status != 'Resolved' && $complaint->status != 'Closed')
                    {{ Form::textarea('remark', $complaint->remark, ['class' => 'form-control form-control-sm', 'placeholder' => __('Add remark'), 'rows' => 5]) }}
                @else
                    {{ $complaint->remark ?? '-' }}
                @endif
            </td>
        </tr>
    </table>

    <input type="hidden" name="complaint_id" value="{{ $complaint->id }}">
</div>

<div class="modal-footer">
    @if(((!empty($isReadOnly) && $isReadOnly) || \Auth::user()->type == 'company' || \Auth::user()->type == 'management' || \Auth::user()->type == 'CEO') && $complaint->status != 'Resolved' && $complaint->status != 'Closed')
        <button type="submit" class="btn btn-primary">{{ __('Update Complaint') }}</button>
    @endif

    @if(Auth::user()->type == 'employee' && $complaint->status == 'Resolved' && Auth::user()->id == $complaint->employee_id)
        <input type="hidden" name="status" value="Closed">
        <button type="submit" class="btn btn-success">{{ __('Close Complaint') }}</button>
    @endif
</div>

{{ Form::close() }}
