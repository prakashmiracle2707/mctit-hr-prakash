@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Complaints') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Complaints') }}</li>
@endsection

@section('action-button')
    @can('Create Office-Complaint')
        <a href="#" data-url="{{ route('complaints.create') }}" data-ajax-popup="true"
            data-title="{{ __('Raise Complaint') }}" data-size="lg"
            data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Raise Complaint') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
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
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created At') }}</th>
                                @if (Gate::check('Edit Office-Complaint') || Gate::check('Delete Office-Complaint'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($complaints as $complaint)
                                <tr>
                                    @if(\Auth::user()->type != 'employee')
                                    <td>{{ $complaint->employee ? ucfirst($complaint->employee->name) : __('Unknown') }}</td>
                                    @endif
                                    <td>{{ $complaint->category->name ?? '-' }}</td>
                                    <td>{{ $complaint->title->name ?? '-' }}</td>
                                    <td>
                                        @if ($complaint->priority == 'Medium')
                                            <div class="badge bg-warning p-2 px-3 ">{{ strtoupper($complaint->priority) }}</div>
                                        @elseif($complaint->priority == 'Low')
                                            <div class="badge bg-success p-2 px-3 ">{{ strtoupper($complaint->priority) }}</div>
                                        @elseif($complaint->priority == "High")
                                            <div class="badge bg-danger p-2 px-3 ">{{ strtoupper($complaint->priority) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($complaint->status == "Pending")
                                            <div class="text-danger"><b>{{ strtoupper($complaint->status) }}</b></div>
                                        @elseif ($complaint->status == 'In Progress')
                                            <div class="text-warning"><b>{{ strtoupper($complaint->status) }}</b></div>
                                        @elseif($complaint->status == 'Resolved')
                                            <div class="text-info"><b>{{ strtoupper($complaint->status) }}</b></div>
                                        @elseif($complaint->status == "Rejected")
                                            <div class="text-muted"><b>{{ strtoupper($complaint->status) }}</b></div>
                                        @endif
                                    </td>
                                    <td>{{ $complaint->created_at ? \Carbon\Carbon::parse($complaint->created_at)->format('d/m/Y') : '-' }}</td>

                                    @if (Gate::check('Edit Office-Complaint') || Gate::check('Delete Office-Complaint'))
                                        <td class="Action">
                                            <div class="dt-buttons">
                                                <span>
                                                    @can('Edit Office-Complaint')
                                                        <div class="action-btn bg-info me-2">
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                                data-size="lg"
                                                                data-url="{{ route('complaints.edit', $complaint->id) }}"
                                                                data-ajax-popup="true" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Complaint') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('Delete Office-Complaint')
                                                        <div class="action-btn bg-danger">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['complaints.destroy', $complaint->id], 'id' => 'delete-form-' . $complaint->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                                aria-label="Delete">
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
