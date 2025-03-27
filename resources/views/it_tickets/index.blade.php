@extends('layouts.admin')

@section('page-title')
    {{ __('Manage IT Tickets') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('IT Tickets') }}</li>
@endsection

@section('action-button')
    @if(\Auth::user()->type == 'employee')
        @can('Create IT Ticket')
            <a href="#" data-url="{{ route('it-tickets.create') }}" data-ajax-popup="true"
                data-title="{{ __('Raise IT Ticket') }}" data-size="lg"
                data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                data-bs-original-title="{{ __('Raise IT Ticket') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    @endif
@endsection

@php
    $user = \Auth::user();
    $hasReviewerRole = $user->secondaryRoleAssignments()
        ->whereHas('role', fn($q) => $q->where('name', 'IT-Support-Engineer'))
        ->exists();
@endphp

@section('content')
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                @if(\Auth::user()->type != 'employee' || $hasReviewerRole)
                                <th>{{ __('Employee') }}</th>
                                @endif
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created At') }}</th>
                                @if (Gate::check('Edit IT Ticket') || Gate::check('Delete IT Ticket') || \Auth::user()->type == 'company')
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tickets as $ticket)
                                <tr>
                                    @if(\Auth::user()->type != 'employee' || $hasReviewerRole)
                                    <td>{{ $ticket->employee ? ucfirst($ticket->employee->name) : __('Unknown') }}</td>
                                    @endif
                                    <td>{{ $ticket->category->name ?? '-' }}</td>
                                    <td>{{ $ticket->title->name ?? '-' }}</td>
                                    <td>{{ $ticket->description ?? '-' }}</td>
                                    <td>
                                        @if ($ticket->priority == 'Medium')
                                            <div class="badge bg-warning p-2 px-3 ">{{ strtoupper($ticket->priority) }}</div>
                                        @elseif($ticket->priority == 'Low')
                                            <div class="badge bg-success p-2 px-3 ">{{ strtoupper($ticket->priority) }}</div>
                                        @elseif($ticket->priority == "High")
                                            <div class="badge bg-danger p-2 px-3 ">{{ strtoupper($ticket->priority) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->status == "Open")
                                            <div class="text-danger"><b>{{ strtoupper($ticket->status) }}</b></div>
                                        @elseif ($ticket->status == 'In Progress')
                                            <div class="text-warning"><b>{{ strtoupper($ticket->status) }}</b></div>
                                        @elseif($ticket->status == 'Resolved')
                                            <div class="text-info"><b>{{ strtoupper($ticket->status) }}</b></div>
                                        @elseif($ticket->status == "Closed")
                                            <div class="text-success"><b>{{ strtoupper($ticket->status) }}</b></div>
                                        @endif
                                    </td>
                                    <td>{{ $ticket->created_at ? \Carbon\Carbon::parse($ticket->created_at)->format('d/m/Y') : '-' }}</td>

                                    @if (Gate::check('Edit IT Ticket') || Gate::check('Delete IT Ticket') || \Auth::user()->type == 'company')
                                        <td class="Action">
                                            <div class="dt-buttons">
                                                <span>
                                                    <div class="action-btn bg-success me-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                            data-size="lg"
                                                            data-url="{{ URL::to('it-tickets/' . $ticket->id . '/action') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('IT Ticket Action') }}"
                                                            data-bs-original-title="{{ __('Manage IT Ticket') }}">
                                                            <span class="text-white"><i class="ti ti-caret-right"></i></span>
                                                        </a>
                                                    </div>
                                                    @if($ticket->employee_id == auth()->id() && $ticket->status == 'Open')
                                                        @can('Edit IT Ticket')
                                                            <div class="action-btn bg-info me-2">
                                                                <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                                    data-size="lg"
                                                                    data-url="{{ route('it-tickets.edit', $ticket->id) }}"
                                                                    data-ajax-popup="true" data-bs-toggle="tooltip"
                                                                    title="" data-title="{{ __('Edit IT Ticket') }}"
                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                </a>
                                                            </div>
                                                        @endcan

                                                        @can('Delete IT Ticket')
                                                            <div class="action-btn bg-danger">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['it-tickets.destroy', $ticket->id], 'id' => 'delete-form-' . $ticket->id]) !!}
                                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                                    aria-label="Delete" onclick="event.preventDefault(); document.getElementById('delete-form-{{ $ticket->id }}').submit();">
                                                                    <span class="text-white"><i class="ti ti-trash"></i></span>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    @endif
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
