@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Employee') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('employee.export') }}" data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-original-title="{{ __('Export') }}" class="btn btn-sm btn-primary">
        <i class="ti ti-file-export"></i>
    </a>

    <a href="#" data-url="{{ route('employee.file.import') }}" data-ajax-popup="true"
        data-title="{{ __('Import  employee CSV file') }}" data-bs-toggle="tooltip" title=""
        class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Import') }}">
        <i class="ti ti-file"></i>
    </a>
    @can('Create Employee')
        <a href="{{ route('employee.create') }}" data-title="{{ __('Create New Employee') }}" data-bs-toggle="tooltip"
            title="" class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                {{-- <h5></h5> --}}
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Employee ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th>{{ __('Designation') }}</th>
                                <th>{{ __('Date Of Joining') }}</th>
                                @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <tr>
                                    <td>
                                        @if($employee->work_from_home)
                                            <i class="fa fa-desktop" aria-hidden="true" title="Work from home"></i> 
                                        @endif

                                        @can('Show Employee')
                                            <a class="btn btn-outline-primary"
                                                href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">
                                                {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}
                                            </a>
                                        @else
                                            <a href="#"
                                                class="btn btn-outline-primary">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</a>
                                        @endcan
                                        
                                        
                                    </td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ !empty($employee->email) ? $employee->email : '-' }}</td>
                                    <td>
                                        {{ !empty($employee->branch_id) ? $employee->branch->name : '-' }}
                                    </td>
                                    <td>
                                        {{ !empty($employee->department_id) ? $employee->department->name : '-' }}
                                    </td>
                                    <td>
                                        {{ !empty($employee->designation_id) ? $employee->designation->name : '-' }}
                                    </td>
                                    <td>
                                        {{ \Auth::user()->dateFormat($employee->company_doj) }}
                                    </td>
                                    @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                        <td class="Action">
                                            @if ($employee->is_active == 1)
                                                <div class="dt-buttons">
                                                    <span>
                                                        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
                                                            <div class="action-btn bg-warning me-2">
                                                                <a href="#"
                                                                    data-url="{{ route('employee.reset', \Crypt::encrypt($employee->user->id)) }}"
                                                                    class="mx-3 btn btn-sm  align-items-center"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-ajax-popup="true" data-size="md"
                                                                    data-bs-original-title="{{ __('Change Password') }}">
                                                                    <span class="text-white"><i
                                                                            class="ti ti-key"></i></span>
                                                                </a>
                                                            </div>
                                                        @endif
                                                        @can('Edit Employee')
                                                            <div class="action-btn bg-info me-2">
                                                                <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                                                    class="mx-3 btn btn-sm  align-items-center"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                </a>
                                                            </div>
                                                        @endcan

                                                        @can('Delete Employee')
                                                            <div class="action-btn bg-danger">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['employee.destroy', $employee->id],
                                                                    'id' => 'delete-form-' . $employee->id,
                                                                ]) !!}
                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Delete" aria-label="Delete"><span
                                                                        class="text-white"><i
                                                                            class="ti ti-trash"></i></span></a>
                                                                </form>
                                                            </div>
                                                        @endcan
                                                    </span>
                                                </div>
                                            @else
                                                <i class="ti ti-lock"></i>
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
