@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Salary Slips') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Salary Slips') }}</li>
@endsection

@section('action-button')
    @if(\Auth::user()->type == 'management')
        @can('Create Pay Slip')
            <a href="#" 
                data-url="{{ route('salary_slips.create') }}" 
                data-ajax-popup="true" 
                data-title="{{ __('Create New Salary Slip') }}" 
                data-size="lg" 
                data-bs-toggle="tooltip" 
                title="" 
                class="btn btn-sm btn-primary" 
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-upload"></i> Bulk Create
            </a>
        @endcan
    @endif
@endsection



@section('content')

    {{ Form::open(['route' => ['salary_slips.index'], 'method' => 'get', 'id' => 'salary_slips_form']) }}
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            {{ Form::label('financial_year_id', __('Financial Year'), ['class' => 'form-label']) }}
                            {{ Form::select('financial_year_id', $financialYears, $selectedFY, ['class' => 'form-control select']) }}
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
                            <a href="{{ route('salary_slips.index') }}" class="btn btn-sm btn-danger">
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
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                @if(\Auth::user()->type != 'employee')
                                    <th>{{ __('Employee') }}</th>
                                @endif
                                <th>{{ __('Month') }}-{{ __('Year') }}</th>
                                <!-- <th>{{ __('Year') }}</th> -->
                                <th>{{ __('Salary Slip') }}</th>
                                @if(\Auth::user()->type == 'management')
                                    @if (Gate::check('Edit Pay Slip') || Gate::check('Delete Pay Slip'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($salarySlips as $slip)
                                <tr>
                                    @if(\Auth::user()->type != 'employee')
                                        <td>{{ $slip->employees->name }}</td>
                                    @endif
                                    <td>{{ $slip->month }}-{{ $slip->year }}</td>
                                    <!-- <td>{{ $slip->year }}</td> -->
                                    @if(file_exists(public_path('uploads/salary-slips/' . $slip->file_path)))
                                        <td>
                                            <!-- View Button -->
                                            <a href="{{ asset('public/uploads/salary-slips/' . $slip->file_path) }}" 
                                                target="_blank" class="btn btn-primary btn-sm">
                                                <i class="ti ti-eye"></i> {{ __('View') }}
                                            </a>

                                            <a href="{{ route('salary_slips.download', ['id' => $slip->id]) }}" class="btn btn-success btn-sm">
                                                <i class="ti ti-download"></i> {{ __('Download') }}
                                            </a>
                                        </td>
                                    @else
                                        <td>
                                            <span class="text-danger">File not found</span>
                                        </td>
                                    @endif
                                    @if(\Auth::user()->type == 'management')
                                    <td class="Action">
                                        @if (Gate::check('Edit Pay Slip') || Gate::check('Delete Pay Slip'))
                                        <div class="dt-buttons">
                                            <span>
                                                @can('Edit Pay Slip')
                                                    <div class="action-btn bg-info me-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" 
                                                            data-size="lg"
                                                            data-url="{{ URL::to('salary_slips/' . $slip->id . '/edit') }}"
                                                            data-ajax-popup="true" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Edit Salary Slip') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('Delete Pay Slip')
                                                    <div class="action-btn bg-danger">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['salary_slips.destroy', $slip->id], 'id' => 'delete-form-' . $slip->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"aria-label="Delete">
                                                            <span class="text-white"><i class="ti ti-trash"></i></span>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </div>
                                        @endif
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
