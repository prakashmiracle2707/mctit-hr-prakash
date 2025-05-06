@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Monthly Attendance') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Manage Monthly Attendance Report') }}</li>
@endsection


<style type="text/css">
    .text-align-center{
        text-align: center;
    }

    .left-border{
        border-left: 2px solid #008ECC;
        text-align: center;
    }

    .right-border{
        border-right: 2px solid #008ECC;
        text-align: center;
    }

    .right-border-gray{
        border-right: 2px solid #f1f1f1;
        text-align: center;
    }

    .bottom-border{
        border-bottom: 2px solid #008ECC !important;
    }
</style>


@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endpush

@section('content')

    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['report.monthly.attendance'], 'method' => 'get', 'id' => 'report_monthly_attendance']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    <div class="btn-box">
                                        {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                        {{ Form::month('month', request('month'), [
                                                'class' => 'month-btn form-control current_date',
                                                'autocomplete' => 'off',
                                                'id' => 'monthInput'
                                            ]) }}
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                    {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                    {{ Form::select('employee_id', ['' => 'All'] + $employeeList->toArray(), request('employee_id'), [
                                        'class' => 'form-control select',
                                        'id' => 'employeeSelect'
                                    ]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="ti ti-search"></i> {{ __('Apply') }}
                            </button>
                            <a href="{{ route('report.monthly.attendance') }}" class="btn btn-sm btn-danger">
                                <i class="ti ti-refresh"></i> {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div id="printableArea">
        <div class="row">
           
            <div class="col-3">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="badge theme-avtar bg-secondary">
                                    <i class="ti ti-sum"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ __('Duration') }}</h5>
                                    <p class="text-muted text-sm mb-0">{{ $data['curMonth'] }}
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="display: none;">
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="badge theme-avtar bg-primary">
                                    <i class="ti ti-file-report"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ __('Attendance') }}</h5>
                                    <div>
                                        <p class="text-muted text-sm mb-0">{{ __('Total present') }}:
                                            {{ $data['totalPresent'] }}</p>
                                        <p class="text-muted text-sm mb-0">{{ __('Total leave') }}:
                                            {{ $data['totalLeave'] }}</p>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="badge theme-avtar bg-secondary">
                                    <i class="ti ti-clock"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ __('Overtime') }}</h5>
                                    <p class="text-muted text-sm mb-0">
                                        {{ __('Total overtime in hours') }} :
                                        {{ number_format($data['totalOvertime'], 2) }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="badge theme-avtar bg-primary">
                                    <i class="ti ti-info-circle"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ __('Early leave') }}</h5>
                                    <p class="text-muted text-sm mb-0">{{ __('Total early leave in hours') }}:
                                        {{ number_format($data['totalEarlyLeave'], 2) }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="badge theme-avtar bg-secondary">
                                    <i class="ti ti-alarm"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ __('Employee late') }}</h5>
                                    <p class="text-muted text-sm mb-0">{{ __('Total late in hours') }} :
                                        {{ number_format($data['totalLate'], 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card">
            <div class="card-body table-border-style">
                <ul class="nav nav-tabs mb-3" id="attendanceTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="daily-tab" data-bs-toggle="tab" href="#daily" role="tab" aria-controls="daily" aria-selected="true">{{ __('Daily Attendance') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="summary-tab" data-bs-toggle="tab" href="#summary" role="tab" aria-controls="summary" aria-selected="false">{{ __('Employee Summary') }}</a>
                    </li>
                </ul>

                <div class="tab-content" id="attendanceTabContent">
                    <!-- Daily Attendance Tab -->
                    <div class="tab-pane fade show active" id="daily" role="tabpanel" aria-labelledby="daily-tab">

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="me-3"><span class="badge bg-success p-2">P</span> = Present</div>
                                    <div class="me-3"><span class="badge bg-danger p-2">L</span> = Leave</div>
                                    <div class="me-3"><span class="badge bg-warning p-2">A</span> = Absent</div>
                                    <div class="me-3"><span class="badge bg-primary p-2">H</span> = Holiday</div>
                                    <div class="me-3"><span class="badge bg-black p-2">LWP</span> = Leave Without Pay</div>
                                    <div class="me-3"><span class="badge bg-indigo-500 p-2">H/F</span> = Half-Day</div>
                                    <div class="me-3"><span class="badge bg-danger p-2">OL</span> = Optional Leave</div>
                                    <!-- <div class="me-3"><span class="text-muted">X</span> = Not Applicable</div>
                                    <div class="me-3"><span class="text-muted">-</span> = Weekend / No Data</div> -->
                                </div>
                            </div>
                        </div>

                        <br />
                        
                        <div class="table-responsive py-4 attendance-table-responsive">
                            <table class="table ">
                                @php
                                    $monthYear = \Carbon\Carbon::createFromFormat('M-Y', $data['curMonth']); // 'Apr-2025'
                                @endphp
                                <thead>
                                    <tr>
                                        <th class="active">{{ __('Name') }}</th>
                                        @foreach ($dates as $date)
                                            @php
                                                $fullDate = \Carbon\Carbon::createFromDate($monthYear->year, $monthYear->month, (int)$date);
                                                $isWeekend = $fullDate->isSaturday() || $fullDate->isSunday();
                                            @endphp
                                            <th class="{{ $isWeekend ? 'text-danger' : '' }}">{{ (int)$date }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employeesAttendance as $attendance)
                                        <tr>
                                            <td>{{ $attendance['name'] }}</td>
                                            @foreach ($attendance['status'] as $status)
                                                <td>
                                                    @if ($status == 'P')
                                                        <i class="badge bg-success p-2">{{ __('P') }}</i>
                                                    @elseif($status == 'A')
                                                        <i class="badge bg-warning p-2">{{ __('A') }}</i>
                                                    @elseif($status == 'H')
                                                        <i class="badge bg-primary p-2">{{ __('H') }}</i>
                                                    @elseif($status == 'L')
                                                        <i class="badge bg-danger p-2">{{ __('L') }}</i>
                                                    @elseif($status == 'LWP')
                                                        <i class="badge bg-black p-2">{{ __('LWP') }}</i>
                                                    @elseif($status == 'H/F')
                                                        <i class="badge bg-indigo-500 p-2">{{ __('H/F') }}</i>
                                                    @elseif($status == 'OH')
                                                        <i class="badge bg-danger p-2">{{ __('OH') }}</i>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Employee Summary Tab -->
                    <div class="tab-pane fade" id="summary" role="tabpanel" aria-labelledby="summary-tab">
                        <div class="card">
                            <div class="card-body table-border-style">

                                <div class="table-responsive py-4 attendance-table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="text-align-center">{{ __('Name') }}</th>
                                                <th colspan="3" class="left-border right-border bottom-border">{{ __('Total Present') }}</th>
                                                <th colspan="6" class="right-border bottom-border">{{ __('Leave') }}</th>
                                                <th colspan="3" class="right-border bottom-border">{{ __('Calendar Days') }}</th>
                                                <th colspan="3" class="text-align-center bottom-border">{{ __('Leave Balance') }}</th>
                                            </tr>
                                            <tr>
                                                <th class="left-border right-border-gray">{{ __('MON-FRI') }}</th>
                                                <th class="right-border-gray">{{ __('SAT-SUN') }}</th>
                                                <th class="right-border">{{ __('Holiday') }}</th>
                                                <th class="right-border-gray">{{ __('SL') }}</th>
                                                <th class="right-border-gray">{{ __('CL') }}</th>
                                                <th class="right-border-gray">{{ __('OH') }}</th>
                                                <th class="right-border-gray">{{ __('WFH') }}</th>
                                                <th class="right-border-gray">{{ __('LWP') }}</th>
                                                <th class="right-border">{{ __('Absent') }}</th>
                                                <th class="right-border-gray">{{ __('Holiday') }}</th>
                                                <th class="right-border-gray">{{ __('Total Weekend') }}</th>
                                                <th class="right-border">{{ __('Total Days') }}</th>
                                                <!-- <th class="right-border-gray">{{ __('SL Start') }}</th> -->
                                                <th class="right-border-gray">{{ __('SL') }}</th>
                                                <!-- <th class="right-border-gray">{{ __('CL Start') }}</th> -->
                                                <th class="right-border-gray">{{ __('CL') }}</th>
                                                <!-- <th class="right-border-gray">{{ __('OH Start') }}</th> -->
                                                <th class="right-border-gray">{{ __('OH') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($employeesAttendance as $attendance)
                                                @php
                                                    $summary = $attendance['summary'];
                                                    $leaveBalance = $attendance['leave_balance'] ?? ['start' => [], 'end' => []];
                                                    $Present = $summary['Present'] ?? 0;
                                                    $WeekdayPresent = $summary['WeekdayPresent'] ?? 0;
                                                    $isPresentHoliday = $summary['isPresentHoliday'] ?? 0;
                                                    $sl = $summary['SL'] ?? 0;
                                                    $cfl = $summary['CFL'] ?? 0;
                                                    $chl = $summary['CHL'] ?? 0;
                                                    $oh = $summary['OH'] ?? 0;
                                                    $lwp = $summary['LWP'] ?? 0;
                                                    $wfh = $summary['WFH'] ?? 0;
                                                    $a = $summary['A'] ?? 0;
                                                    $h = $summary['H'] ?? 0;
                                                    $TotalWeekDay = $summary['TotalWeekDay'] ?? 0;
                                                    $total = $summary['TotalMonthDays'] ?? 0;

                                                    $totalCal = $Present + $WeekdayPresent + $isPresentHoliday + ($h - $isPresentHoliday) + ($sl + $cfl + $chl + $oh) + $lwp + $a + ($TotalWeekDay - $WeekdayPresent);

                                                    $startSL = $leaveBalance['start']['SL'] ?? 0;
                                                    $endSL = $leaveBalance['end']['SL'] ?? 0;
                                                    $startCL = $leaveBalance['start']['CL'] ?? 0;
                                                    $endCL = $leaveBalance['end']['CL'] ?? 0;
                                                    $startOH = $leaveBalance['start']['OH'] ?? 0;
                                                    $endOH = $leaveBalance['end']['OH'] ?? 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ $attendance['name'] }}</td>
                                                    <td class="left-border right-border-gray">{{ $Present }}</td>
                                                    <td class="right-border-gray">{{ $WeekdayPresent }}</td>
                                                    <td class="right-border">{{ $isPresentHoliday }}</td>
                                                    <td class="right-border-gray">{{ $sl }}</td>
                                                    <td class="right-border-gray">{{ $cfl + $chl }}</td>
                                                    <td class="right-border-gray">{{ $oh }}</td>
                                                    <td class="right-border-gray">{{ $wfh }}</td>
                                                    <td class="right-border-gray">{{ $lwp }}</td>
                                                    <td class="right-border">{{ $a }}</td>
                                                    <td class="right-border-gray">{{ $h + $oh }}</td>
                                                    <td class="right-border-gray">{{ $TotalWeekDay }}</td>
                                                    <td class="right-border" @if($totalCal != $total) style="color: red;" @endif>{{ $total }}</td>
                                                    <!-- <td class="right-border-gray">{{ $startSL }}</td> -->
                                                    <td class="right-border-gray">{{ $endSL }}</td>
                                                    <!-- <td class="right-border-gray">{{ $startCL }}</td> -->
                                                    <td class="right-border-gray">{{ $endCL }}</td>
                                                    <!-- <td class="right-border-gray">{{ $startOH }}</td> -->
                                                    <td class="right-border-gray">{{ $endOH }}</td>
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
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {

            var b_id = $('#branch_id').val();
            // getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch]', function() {
            var branch_id = $(this).val();

            getDepartment(branch_id);
        });

        function getDepartment(bid) {

            $.ajax({
                url: '{{ route('monthly.getdepartment') }}',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('.department_id').empty();
                    var emp_selct = `<select class="department_id form-control multi-select" id="choices-multiple" multiple="" required="required" name="department_id[]">
                </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value=""> {{ __('Select Department') }} </option>');
                    $.each(data, function(key, value) {
                        $('.department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        }

        $(document).on('change', '.department_id', function() {
            var department_id = $(this).val();
            getEmployee(department_id);
        });

        function getEmployee(did) {

            $.ajax({
                url: '{{ route('monthly.getemployee') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#employee_id').empty();

                    $("#employee_div").html('');
                    // $('#employee_div').append('<select class="form-control" id="employee_id" name="employee_id[]"  multiple></select>');
                    $('#employee_div').append(
                        '<label for="employee" class="form-label">{{ __('Employee') }}</label><select class="form-control" id="employee_id" name="employee_id[]"  multiple></select>'
                    );

                    $('#employee_id').append('<option value="">{{ __('Select Employee') }}</option>');
                    $('#employee_id').append('<option value=""> {{ __('Select Employee') }} </option>');

                    $.each(data, function(key, value) {
                        $('#employee_id').append('<option value="' + key + '">' + value + '</option>');
                    });

                    var multipleCancelButton = new Choices('#employee_id', {
                        removeItemButton: true,
                    });
                }
            });
        }
    </script>

    <script>
        $(document).ready(function () {
            // Get URL parameter
            function getUrlParameter(name) {
                name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                var results = regex.exec(location.search);
                return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, ' '));
            }

            var monthFromUrl = getUrlParameter('month');

            if (monthFromUrl) {
                $('.current_date').val(monthFromUrl);
            } else {
                // If not in URL, use current month
                var now = new Date();
                var year = now.getFullYear();
                var month = (now.getMonth() + 1).toString().padStart(2, '0');
                var today = year + '-' + month;
                $('.current_date').val(today);
            }
        });
    </script>

@endpush
