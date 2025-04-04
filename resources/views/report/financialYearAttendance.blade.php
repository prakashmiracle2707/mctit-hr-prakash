@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Financial Year Attendance') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Financial Year Attendance Report') }}</li>
@endsection

@section('content')
{{ Form::open(['route' => ['report.financialYear.attendance'], 'method' => 'get', 'id' => 'financial_attendance_form']) }}
<div class="row align-items-end">
    <div class="col-md-3">
        {{ Form::label('financial_year_id', __('Financial Year'), ['class' => 'form-label']) }}
        {{ Form::select('financial_year_id', $financialYears, $selectedFY, ['class' => 'form-control select']) }}
    </div>
    <div class="col-md-3">
        {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
        {{ Form::select('employee_id', ['' => 'All'] + $employeeList->toArray(), request('employee_id'), ['class' => 'form-control select']) }}
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="ti ti-search"></i> {{ __('Apply') }}
        </button>
        <a href="{{ route('report.financialYear.attendance') }}" class="btn btn-sm btn-danger">
            <i class="ti ti-refresh"></i> {{ __('Reset') }}
        </a>
    </div>
</div>
{{ Form::close() }}

<style>
    .text-align-center { text-align: center; }
    .left-border { border-left: 2px solid #008ECC; text-align: center; }
    .right-border { border-right: 2px solid #008ECC; text-align: center; }
    .right-border-gray { border-right: 2px solid #f1f1f1; text-align: center; }
    .bottom-border { border-bottom: 2px solid #008ECC !important; }
</style>

@if (!empty($attendanceData))
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" id="monthTabs" role="tablist">
                    @foreach($attendanceData as $label => $monthData)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link @if($loop->first) active @endif" id="tab-{{ $loop->index }}" data-bs-toggle="tab" href="#content-{{ $loop->index }}" role="tab">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="monthTabsContent">
                    @foreach($attendanceData as $label => $monthData)
                        <div class="tab-pane fade @if($loop->first) show active @endif" id="content-{{ $loop->index }}" role="tabpanel">
                            <ul class="nav nav-tabs mb-3" id="subTab-{{ $loop->index }}" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="summary-tab-{{ $loop->index }}" data-bs-toggle="tab" href="#summary-{{ $loop->index }}" role="tab">{{ __('Summary') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="daily-tab-{{ $loop->index }}" data-bs-toggle="tab" href="#daily-{{ $loop->index }}" role="tab">{{ __('Daily Attendance') }}</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Summary Tab -->
                                <div class="tab-pane fade show active" id="summary-{{ $loop->index }}" role="tabpanel">
                                    <div class="table-responsive">
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
                                                    
                                                    <th class="right-border-gray">{{ __('SL') }}</th>
                                                    <th class="right-border-gray">{{ __('CL') }}</th>
                                                    <th class="right-border-gray">{{ __('OH') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($monthData as $attendance)
                                                    @php
                                                        $summary = $attendance['summary'];
                                                        $leave = $attendance['leave_balance'];
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

                                                        // Leave balances
                                                        $startSL = $leave['start']['SL'] ?? 0;
                                                        $endSL = $leave['end']['SL'] ?? 0;
                                                        $startCL = $leave['start']['CL'] ?? 0;
                                                        $endCL = $leave['end']['CL'] ?? 0;
                                                        $startOH = $leave['start']['OH'] ?? 0;
                                                        $endOH = $leave['end']['OH'] ?? 0;
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
                                                        <td class="right-border" @if($totalCal != $total) style="color:red;" @endif>{{ $total }}</td>

                                                        {{-- Leave Balance --}}
                                                        
                                                        <td class="right-border-gray">{{ $endSL }}</td>
                                                        <td class="right-border-gray">{{ $endCL }}</td>
                                                        <td class="right-border-gray">{{ $endOH }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Daily Attendance Tab -->
                                <div class="tab-pane fade" id="daily-{{ $loop->index }}" role="tabpanel">

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
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Name') }}</th>
                                                    @php
                                                        $firstEmployee = collect($monthData)->first();
                                                        $dates = isset($firstEmployee['status']) ? array_keys($firstEmployee['status']) : [];
                                                        $monthYear = \Carbon\Carbon::createFromFormat('F-Y', $label); // $label = "March-2025" etc.
                                                    @endphp
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
                                                @foreach ($monthData as $attendance)
                                                    <tr>
                                                        <td>{{ $attendance['name'] }}</td>
                                                        @foreach ($dates as $date)
                                                            @php $status = $attendance['status'][$date] ?? ''; @endphp
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
                                                                @elseif($status == 'OL')
                                                                    <i class="badge bg-danger p-2">{{ __('OL') }}</i>
                                                                @else
                                                                    <span>-</span>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div> <!-- end tab-content -->
                        </div> <!-- end month tab -->
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
