@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Holiday') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Holidays List') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('holidays.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a>

    @if (\Auth::user()->type != 'employee')
        <a href="#" data-url="{{ route('holidays.file.import') }}" data-ajax-popup="true"
            data-title="{{ __('Import Holiday CSV file') }}" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Import') }}">
            <i class="ti ti-file-import"></i>
        </a>
    @endif

    <a href="{{ route('holiday.calender') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Calendar View') }}">
        <i class="ti ti-calendar"></i>
    </a>

    @can('Create Holiday')
        <a href="#" data-url="{{ route('holiday.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Holiday') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="col-sm-6">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['holiday.index'], 'method' => 'get', 'id' => 'holiday_filter']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-xl-10 col-lg-10 col-md-10 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('financial_year_id', __('Financial Year'), ['class' => 'form-label']) }}
                                        {{ Form::select('financial_year_id', $financialYears, request()->get('financial_year_id', $activeYearId), [
                                            'class' => 'form-control',
                                            'id' => 'financial_year_id',
                                        ]) }}
                                    </div>
                                </div>
                                <div class="col-auto mt-4" style="padding-top: 10px;">
                                    <a href="#" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('holiday_filter').submit(); return false;"
                                        data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('holiday.index') }}" class="btn btn-sm btn-danger"
                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Reset">
                                        <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off "></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                       
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Holiday Table --}}
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Occasion') }}</th>
                                <th>{{ __('Date') }}</th>
                                @if (Gate::check('Edit Holiday') || Gate::check('Delete Holiday'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($holidays as $holiday)
                                <tr>
                                    <td style="color:{{ $holiday->is_optional ? 'grey' : 'black' }}">
                                        {{ $holiday->occasion }} {{ $holiday->is_optional ? '(Optional)' : '' }}
                                    </td>
                                    <td style="color:{{ $holiday->is_optional ? 'grey' : 'black' }}">
                                        {{ \Carbon\Carbon::parse($holiday->start_date)->format('d/m/Y') }}
                                        <b>({{ \Carbon\Carbon::parse($holiday->start_date)->format('l') }})</b>
                                    </td>
                                    @if (Gate::check('Edit Holiday') || Gate::check('Delete Holiday'))
                                        <td class="Action">
                                            <div class="dt-buttons">
                                                <span>
                                                    @can('Edit Holiday')
                                                        <div class="action-btn bg-info me-2">
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                                data-url="{{ route('holiday.edit', $holiday->id) }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Holiday') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('Delete Holiday')
                                                        <div class="action-btn bg-danger">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['holiday.destroy', $holiday->id], 'id' => 'delete-form-' . $holiday->id]) !!}
                                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Delete">
                                                                    <span class="text-white"><i class="ti ti-trash"></i></span>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </span>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
