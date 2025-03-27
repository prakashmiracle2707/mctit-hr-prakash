{{ Form::open(['url' => 'it-tickets/changeaction', 'method' => 'post']) }}

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
            <td colspan="3">{{ $ITTicket->title->name ?? '-' }}</td>
        </tr>

        <tr>
            <th>{{ __('Employee') }}</th>
            <td>{{ ucfirst($ITTicket->employee->name ?? '-') }}</td>
            <th>{{ __('Priority') }}</th>
            <td>
                <span class="badge bg-{{ $ITTicket->priority == 'High' ? 'danger' : ($ITTicket->priority == 'Medium' ? 'warning' : 'success') }}">
                    {{ strtoupper($ITTicket->priority) }}
                </span>
            </td>
        </tr>

        <tr>
            <th>{{ __('Category') }}</th>
            <td>{{ $ITTicket->category->name ?? '-' }}</td>
            <th>{{ __('Created On') }}</th>
            <td colspan="3">{{ \Carbon\Carbon::parse($ITTicket->created_at)->format('d/m/Y') }}</td>
        </tr>

        <tr>
            <th>{{ __('Description') }}</th>
            <td colspan="3">{{ $ITTicket->description ?? '-' }}</td>
        </tr>

        <tr>
            <th>{{ __('Status') }}</th>
            <td colspan="3">
                @if($ITTicket->status == "Open")
                    <div class="text-danger"><b>{{ strtoupper($ITTicket->status) }}</b></div>
                @elseif ($ITTicket->status == 'In Progress')
                    <div class="text-warning"><b>{{ strtoupper($ITTicket->status) }}</b></div>
                @elseif($ITTicket->status == 'Resolved')
                    <div class="text-info"><b>{{ strtoupper($ITTicket->status) }}</b></div>
                @elseif($ITTicket->status == "Closed")
                    <div class="text-success"><b>{{ strtoupper($ITTicket->status) }}</b></div>
                @endif
            </td>
        </tr>
       
        @if(((!empty($isReadOnly) && $isReadOnly) || \Auth::user()->type == 'company' || \Auth::user()->type == 'management' || \Auth::user()->type == 'CEO') && $ITTicket->status != 'Resolved' && $ITTicket->status != 'Closed')
        <tr>
            <th>{{ __('Change Status') }}</th>
            <td colspan="3">
                
                @php
                    $statusOptions = ['Open', 'In Progress', 'Resolved'];
                    $statusClass = match($ITTicket->status) {
                        'Open' => 'status-open',
                        'In Progress' => 'status-progress',
                        'Resolved' => 'status-resolved'
                    };
                @endphp

                
                {{ Form::select('status', array_combine($statusOptions, $statusOptions), $ITTicket->status, [
                    'class' => 'form-select form-select-sm',
                ]) }}
                
            </td>
        </tr>
        @endif
        
        <tr>
            <th>{{ __('Remark') }}</th>
            <td colspan="3">
                @if(((!empty($isReadOnly) && $isReadOnly) || \Auth::user()->type == 'company' || \Auth::user()->type == 'management' || \Auth::user()->type == 'CEO') && $ITTicket->status != 'Resolved' && $ITTicket->status != 'Closed')
                    {{ Form::textarea('remark', $ITTicket->remark, ['class' => 'form-control form-control-sm', 'placeholder' => __('Add remark'), 'rows' => 5]) }}
                @else
                    {{ $ITTicket->remark ?? '-' }}
                @endif
            </td>
        </tr>
    </table>

    <input type="hidden" name="itticket_id" value="{{ $ITTicket->id }}">
</div>

<div class="modal-footer">
    @if(((!empty($isReadOnly) && $isReadOnly) || \Auth::user()->type == 'company' || \Auth::user()->type == 'management' || \Auth::user()->type == 'CEO') && $ITTicket->status != 'Resolved' && $ITTicket->status != 'Closed')
        <button type="submit" class="btn btn-primary">{{ __('Update IT-Tickets') }}</button>
    @endif

    @if(Auth::user()->type == 'employee' && $ITTicket->status == 'Resolved' && Auth::user()->id == $ITTicket->employee_id)
        <input type="hidden" name="status" value="Closed">
        <button type="submit" class="btn btn-success">{{ __('Close IT-Tickets') }}</button>
    @endif
</div>

{{ Form::close() }}
