@extends('layouts.admin')

@section('page-title')
    {{ __('Manage User Logs History') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('user.index') }}">{{ __('Users') }}</a></li>
    <li class="breadcrumb-item">{{ __('User Logs') }}</li>

@endsection

@section('content')
    <div class="">

        <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
            <div class=" mt-2 " id="" style="">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['lastlogin'], 'method' => 'get', 'id' => 'employee_filter']) }}
                        <div class="d-flex align-items-center justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                <div class="btn-box">
                                    {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                    {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'month-btn form-control month-btn']) }}
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mx-2">
                                <div class="btn-box">
                                    {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                                    {{ Form::select('employee', $usersList, isset($_GET['employee']) ? $_GET['employee'] : '', ['class' => 'form-control select ', 'id' => 'employee_id']) }}
                                </div>
                            </div>

                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('employee_filter').submit(); return false;"
                                    data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route('lastlogin') }}" class="btn btn-sm btn-danger"
                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Reset">
                                    <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off "></i></span>
                                </a>
                            </div>

                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    {{-- <h5></h5>  --}}
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    {{-- <th>#</th> --}}
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Last Login') }}</th>
                                    <th>{{ __('Ip') }}</th>
                                    <th>{{ __('Country') }}</th>
                                    <th>{{ __('Device') }}</th>
                                    <th>{{ __('OS') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($userdetails as $user)
                                    @php
                                        $userdetail = json_decode($user->Details);
                                    @endphp
                                    <tr>
                                        <td>{{ ucfirst($user->user_name) }}</td>
                                        <td>
                                            <span class="badge p-2 px-3 mt-2  bg-primary">
                                                {{ ucfirst($user->user_type) }}
                                            </span>
                                        </td>
                                        <td>{{ !empty($user->date) ? $user->date : '-' }}</td>
                                        <td>{{ $user->ip }}</td>
                                        <td>{{ !empty($userdetail->country) ? $userdetail->country : '-' }}</td>
                                        <td>{{ $userdetail->device_type }}</td>
                                        <td>{{ $userdetail->os_name }}</td>
                                        <td>
                                            <div class="dt-buttons">
                                                <span>
                                            <div class="action-btn bg-warning me-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center" data-size="lg"
                                                    data-url="{{ route('userlog.view', [$user->id]) }}"
                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                    title="" data-title="{{ __('View User Logs') }}"
                                                    data-bs-original-title="{{ __('View') }}">
                                                    <span class="text-white"><i class="ti ti-eye"></i></span>
                                                </a>
                                            </div>

                                            @can('Delete User')
                                                <div class="action-btn bg-danger">
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'route' => ['employee.logindestroy', $user->user_id],
                                                        'id' => 'delete-form-' . $user->user_id,
                                                    ]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete"><span class="text-white"><i
                                                            class="ti ti-trash"></i></span></a>
                                                    </form>
                                                </div>
                                            @endcan
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
