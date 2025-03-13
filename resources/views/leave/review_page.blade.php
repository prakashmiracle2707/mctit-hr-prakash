
@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Leave') }}
@endsection


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave ') }}</li>
    <li class="breadcrumb-item">{{ !empty($employee->name) ? $employee->name : '' }}</li>
@endsection



@section('content')
    {{ Form::open(['url' => 'leave/changeaction', 'method' => 'post']) }}
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                {{-- <h5> </h5> --}}
                <div class="table-responsive">
                    <table class="table">
                        <tr role="row">
                            <th>{{ __('Employee') }}</th>
                            <td>{{ !empty($employee->name) ? $employee->name : '' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Leave Type ') }}</th>
                            <td><b>{{ !empty($leavetype->title) ? $leavetype->title : '' }}</b></td>
                        </tr>
                        <tr>
                            <th>{{ __('Leave(Full/Half Day) ') }}</th>
                            <td>
                                @switch($leave->half_day_type)
                                    @case('full_day')
                                        <div class="badge bg-success">{{ __('Full Day') }}</div>
                                        @break
                                    @case('morning')
                                        <div class="badge bg-dark">{{ __('First Half (Morning)') }}</div>
                                        @break
                                    @case('afternoon')
                                        <div class="badge bg-danger">{{ __('Second Half (Afternoon)') }}</div>
                                        @break
                                    @default
                                        <div class="badge bg-secondary">{{ __('Not Specified') }}</div>
                                @endswitch
                            </td>
                        </tr>
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
                        <tr>
                            <th>{{ __('Leave Reason') }}</th>
                            <td>{{ !empty($leave->leave_reason) ? $leave->leave_reason : '' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Status') }}</th>
                            <td>
                                @if ($leave->status == 'Pending')
                                    <div class="badge bg-warning">{{ $leave->status }}</div>
                                @elseif($leave->status == 'Approved')
                                    <div class="badge bg-success">{{ $leave->status }}</div>
                                @elseif($leave->status == "Reject")
                                    <div class="badge bg-danger">{{ $leave->status }}</div>
                                @elseif($leave->status == "Draft")
                                    <div class="badge bg-info">{{ $leave->status }}</div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('Remark') }}</th>
                            <td>
                                @if (Auth::user()->type != 'employee')
                                    {{ Form::textarea('remark', $leave->remark, ['class' => 'form-control grammer_textarea', 'placeholder' => __('Leave Remark'), 'rows' => '3']) }}
                                @else
                                    {{ !empty($leave->remark) ? $leave->remark : '' }}
                                @endif
                            </td>
                        </tr>
                        <input type="hidden" value="{{ $leave->id }}" name="leave_id">
                        <input type="hidden" value="samepage" name="leave_page">
                    </table>
                </div>
                <br />
                @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr' || Auth::user()->type == 'CEO')
                <div class="modal-footer">
                    <input type="submit" value="{{ __('Approved') }}" class="btn btn-success rounded" name="status" style="margin-right: 10px;">
                    <input type="submit" value="{{ __('Reject') }}" class="btn btn-danger rounded" name="status">
                </div>
                @endif

            </div>
        </div>
    </div>
    {{ Form::close() }}
@endsection



