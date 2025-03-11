
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
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <td>{{ !empty($leave->employee_id) ? $leave->employees->name : '' }}
                                        </td>
                                    @endif
                                    <td>{{ !empty($leave->leave_type_id) ? $leave->leaveType->title : '' }}
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
                                        @elseif($leave->status == 'Approved')
                                            <div class="badge bg-success p-2 px-3 ">{{ $leave->status }}</div>
                                        @elseif($leave->status == "Reject")
                                            <div class="badge bg-danger p-2 px-3 ">{{ $leave->status }}</div>
                                        @elseif($leave->status == "Draft")
                                            <div class="badge bg-info p-2 px-3 ">{{ $leave->status }}</div>
                                        @endif
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

                        if (value.total_leave == value.days) {
                            $('#leave_type_id').append('<option value="' + value.id +
                                '" disabled>' + value.title + '&nbsp(' + value.total_leave +
                                '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type_id').append('<option value="' + value.id + '">' +
                                value.title + '&nbsp(' + value.total_leave + '/' + value
                                .days + ')</option>');
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

