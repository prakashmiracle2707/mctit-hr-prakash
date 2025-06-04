
@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Leave') }}
@endsection


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave ') }}</li>
@endsection

@section('action-button')
    <!-- <a href="{{ route('leave.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a>

    <a href="{{ route('leave.calender') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Calendar View') }}">
        <i class="ti ti-calendar"></i>
    </a> -->

    @if (\Auth::user()->type != 'CEO')
        @can('Create Leave')
            <a href="#" data-url="{{ route('leave.create') }}" data-ajax-popup="true" data-title="{{ __('Create New Leave') }}"
                data-size="lg" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    @endif
@endsection

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if(\Auth::user()->type == 'employee')
<div class="row">
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
                                    <p class="text-muted text-sm mb-0">
                                        Total {{ $type->title }} : {{ $type->days }}
                                    </p>
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
                                    <p class="text-muted text-sm mb-0">
                                        Total {{ $leaveTypes[$leaveTypeId] ?? 'Unknown' }} :
                                        {{ $statuses['Approved'] ?? 0 }}
                                    </p>
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
                                    <p class="text-muted text-sm mb-0">
                                        Total {{ $leaveTypes[$leaveTypeId] ?? 'Unknown' }} :
                                        {{ $statuses['Rejected'] ?? 0 }}
                                    </p>
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
@endif
<div class="row">

    {{ Form::open(['route' => ['leave.index'], 'method' => 'get', 'id' => 'leave_form']) }}
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                {{ Form::label('financial_year_id', __('Financial Year'), ['class' => 'form-label']) }}
                                {{ Form::select('financial_year_id', $financialYears, $selectedFinancialYearId ?? $activeYearId, ['class' => 'form-control select']) }}
                            </div>

                            @if(\Auth::user()->type != 'employee')
                            <div class="col-md-3">
                                {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                {{ Form::select('employee_id', ['' => 'All'] + $employeeList->toArray(), request('employee_id'), ['class' => 'form-control select']) }}
                            </div>
                            @endif

                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="ti ti-search"></i> {{ __('Apply') }}
                                </button>
                                <a href="{{ route('leave.index') }}" class="btn btn-sm btn-danger">
                                    <i class="ti ti-refresh"></i> {{ __('Reset') }}
                                </a>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    {{ Form::close() }}

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">

                {{-- <h5> </h5> --}}
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
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
                                @php
                                    $count = DB::table('leave_managers')->where('leave_id', $leave->id)->count();
                                @endphp
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <td>{{ !empty($leave->employee_id) ? $leave->employees->name : '' }}
                                        </td>
                                    @endif
                                    <td>
                                        @if (!empty($leave->leave_type_id))
                                            {{ $leave->leaveType->title }}

                                            @if ($leave->leave_type_id == 5 && !empty($leave->early_time))
                                                <br>
                                                <span class="badge bg-primary">{{ $leave->early_time }}</span>
                                            @endif
                                        @endif
                                        <br />

                                        @switch($leave->half_day_type)
                                            @case('morning')
                                                <div class="badge bg-dark">{{ __('1st H/D (Morning)') }}</div>
                                                @break
                                            @case('afternoon')
                                                <div class="badge bg-danger">{{ __('2nd H/D (Afternoon)') }}</div>
                                                @break
                                            @default
                                                <div></div>
                                        @endswitch
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
                                    <td style="white-space: normal; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;width: 300px;">{{ $leave->leave_reason }}</td>
                                    <td>
                                        @if ($leave->status == 'Pending')
                                            <div class="badge bg-warning p-2 px-3 ">{{ $leave->status }}</div>
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
                                    <td>{{ \Carbon\Carbon::parse($leave->applied_on)->format('d/m/Y') }}</td>

                                    <td class="Action">
                                        <div class="dt-buttons">
                                        <span>
                                            @if ($leave->status != 'Draft' && $leave->status != 'Cancelled' && \Auth::user()->type == 'employee'  && \Carbon\Carbon::parse($leave->end_date)->diffInDays(now(), false) <= 5)
                                                <div class="action-btn bg-danger me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-url="{{ route('leave.cancel.view', $leave->id) }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                        title="{{ __('Cancel Leave') }}"
                                                        data-title="{{ __('Cancel Leave Application') }}">
                                                        <span class="text-white"><i class="ti ti-ban"></i></span>
                                                    </a>
                                                </div>
                                            @endif

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
    </div>
</div>
@endsection

@push('script-page')
    <script>
        $(document).on('change', '#employee_id', function() {
            var employee_id = $(this).val();

            $.ajax({
                url: '{{ route('leave.jsoncount') }}',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    var oldval = $('#leave_type_id').val();
                    $('#leave_type_id').empty();
                    $('#leave_type_id').append(
                        '<option value="">{{ __('Select Leave Type') }}</option>');

                    $.each(data, function(key, value) {

                        if (value.total_leave == value.days && value.code != 'WFH' && value.code != 'EL' && value.code != 'LWP' && value.code != 'WKG') {
                            $('#leave_type_id').append('<option value="' + value.id +
                                '" disabled>' + value.title + '&nbsp(' +value.code + ')' + '&nbsp(' + value.total_leave +
                                '/' + value.days + ')</option>');
                        } else {
                            if(value.code != 'WFH' && value.code != 'EL' && value.code != 'LWP' && value.code != 'WKG'){
                                $('#leave_type_id').append('<option value="' + value.id + '">' +
                                value.title + '&nbsp(' +value.code + ')' + '&nbsp(' + value.total_leave + '/' + value
                                .days + ')</option>');
                            }else{
                                $('#leave_type_id').append('<option value="' + value.id + '">' +
                                value.title + '&nbsp(' +value.code + ')' + '</option>');
                            }
                        }
                        if (oldval) {
                            if (oldval == value.id) {
                                $("#leave_type_id option[value=" + oldval + "]").attr(
                                    "selected", "selected");
                            }
                        }
                    });

                }
            });
        });
    </script>

@endpush

