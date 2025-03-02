@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Projects') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Projects') }}</li>
@endsection

@section('action-button')
    @can('Create Project')
        <a href="#" data-url="{{ route('projects.create') }}" data-ajax-popup="true" data-title="{{ __('Create New Project') }}" data-size="lg"
            data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
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
                                <th>{{ __('Project Name') }}</th>
                                <!-- <th>{{ __('Created By') }}</th> -->
                                <th>{{ __('Assigned Employees') }}</th>
                                <th>{{ __('Created At') }}</th>
                                @if (Gate::check('Edit Project') || Gate::check('Delete Project'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($projects as $project)
                                <tr>
                                    <td>{{ $project->name }}</td>
                                    <!-- <td>{{ $project->creator ? $project->creator->name : 'Unknown' }}</td> -->
                                    <td>
                                        @if($project->employees->count() > 0)
                                            @foreach($project->employees as $employee)
                                                <span class="badge bg-primary">{{ $employee->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No employees assigned</span>
                                        @endif
                                    </td>
                                    <td>{{ $project->created_at }}</td>
                                    <td class="Action">
                                        @if (Gate::check('Edit Project') || Gate::check('Delete Project'))
                                        <div class="dt-buttons">
                                        <span>
                                                @can('Edit Project')
                                                    <div class="action-btn bg-info me-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-size="lg"
                                                            data-url="{{ URL::to('projects/' . $project->id . '/edit') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Edit Project') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('Delete Project')
                                                    <div class="action-btn bg-danger">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['projects.destroy', $project->id], 'id' => 'delete-form-' . $project->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                            aria-label="Delete"><span class="text-white"><i
                                                                class="ti ti-trash"></i></span></a>
                                                        </form>
                                                    </div>
                                                @endcan
                                            </span>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection