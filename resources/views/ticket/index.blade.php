@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Ticket') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Manage Ticket') }}</li>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@endpush

@push('script-page')
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
@endpush

@section('action-button')
    @can('Create Ticket')
        @if(session()->has('selected_project_id') && session('selected_project_id') !== 'all')
            @if($selectedProjectName)
                <a href="#"
                   data-url="{{ route('ticket.create') }}"
                   data-ajax-popup="true"
                   data-title="{{ __('Create New Ticket') . ' : ' . $selectedProjectName }}"
                   data-size="lg"
                   data-bs-toggle="tooltip"
                   title="{{ __('Create') }}"
                   class="btn btn-sm btn-primary">
                    <i class="ti ti-plus"></i>
                </a>
            @endif
        @endif
    @endcan
@endsection


@section('content')

    @php
        $selectedProjectId = session('selected_project_id', 'all'); // default to "all"
        $projectsList = ['all' => 'All Project'] + $projects->toArray();
    @endphp
    <div class="row">
        <div class="col-4">
            <div class="form-group col-md-12">
                {{ Form::label('project_id', __('Project'), ['class' => 'col-form-label']) }}
                {{ Form::select('project_id', $projectsList, $selectedProjectId, [
                    'class' => 'form-control',
                    'id' => 'project_id',
                    'required' => true
                ]) }}
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Total Ticket -->
        <div class="col-md-2 col-6">
            <div class="card">
                <div class="card-body text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px; background-color: #007bff;">
                        <i class="ti ti-ticket text-white fs-5"></i>
                    </div>
                    <h6 class="text-uppercase text-muted small mb-1">{{ __('Total Ticket') }}</h6>
                    <h3 class="fw-bold mb-0">{{ $countTicket }}</h3>
                </div>
            </div>
        </div>

        <!-- Dynamic Ticket Statuses -->
        @foreach ($ticketStatusCounts as $status)
            <div class="col-lg-2 col-6">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                             style="width: 50px; height: 50px; background-color: {{ $status['color'] }};">
                            <i class="ti ti-ticket text-white fs-5"></i>
                        </div>
                        <h6 class="text-uppercase text-muted small mb-1">{{ __($status['name']) }}</h6>
                        <h3 class="fw-bold mb-0">{{ $status['count'] }}</h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Ticket By Status') }}</h5>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <div id="projects-chart"></div>
                            </div>
                            <div class="col-6">
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <span class="d-flex align-items-center mb-2">
                                            <i class="f-10 lh-1 fas fa-circle text-danger"></i>
                                            <span class="ms-2 text-sm">{{ __('Close') }} </span>
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <span class="d-flex align-items-center mb-2">
                                            <i class="f-10 lh-1 fas fa-circle text-warning"></i>
                                            <span class="ms-2 text-sm">{{ __('Hold') }}</span>
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <span class="d-flex align-items-center mb-2">
                                            <i class="f-10 lh-1 fas fa-circle text-info"></i>
                                            <span class="ms-2 text-sm">{{ __('Total') }}</span>
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <span class="d-flex align-items-center mb-2">
                                            <i class="f-10 lh-1 fas fa-circle text-primary"></i>
                                            <span class="ms-2 text-sm">{{ __('Open') }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    {{-- <h5></h5> --}}
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Ticket Code') }}</th>
                                    @if (session('selected_project_id') === 'all')
                                        <th>{{ __('Project') }}</th>
                                    @endif
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Assignee') }}</th>
                                    <th>{{ __('Created By') }}</th>
                                    <th>{{ __('Created_at') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tickets as $ticket)
                                    <tr>
                                        <td>
                                            @if ($ticket->ticketUnread() > 0)
                                                <i title="New Message" class="fas fa-circle circle text-success"></i>
                                            @endif

                                            @if ($ticket->parent_id != null)
                                                <img src="{{ asset('public/uploads/ticket/ticket-type/SubTask.svg') }}"
                                                     alt="{{ $ticket->type->name }}"
                                                     title="{{ $ticket->type->name }}"
                                                     style="width: 20px; height: 20px; object-fit: contain; margin-right: 5px;">
                                            @else
                                                @if (!empty($ticket->type->image))
                                                    <img src="{{ asset('public/'.$ticket->type->image) }}"
                                                         alt="{{ $ticket->type->name }}"
                                                         title="{{ $ticket->type->name }}"
                                                         style="width: 20px; height: 20px; object-fit: contain; margin-right: 5px;">
                                                @endif
                                            @endif

                                            <a href="{{ URL::to('ticket/' . $ticket->id . '/reply') }}"
                                                           class="btn btn-sm align-items-center"
                                                           data-bs-toggle="tooltip" title="{{ __('Reply') }}">{{ $ticket->ticket_code }}
                                            </a>
                                        </td>
                                        @if (session('selected_project_id') === 'all')
                                        <td>
                                            {{ $ticket->project->name ?? '-' }}
                                        </td>
                                        @endif
                                        <td style="white-space: normal; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;width: 400px;">{{ $ticket->title }}</td>

                                        

                                        <td>
                                            @if ($ticket->getpriority)
                                                <div class="badge p-2 px-3"
                                                     style="background-color: {{ $ticket->getpriority->color ?? '#6c757d' }};">
                                                    {{ $ticket->getpriority->name }}
                                                </div>
                                            @else
                                                <span class="badge bg-light text-dark">{{ __('N/A') }}</span>
                                            @endif
                                        </td>

                                        

                                        <td>
                                            @if ($ticket->getstatus)
                                                <div class="badge p-2 px-3"
                                                     style="background-color: {{ $ticket->getstatus->color ?? '#6c757d' }};">
                                                    {{ $ticket->getstatus->name }}
                                                </div>
                                            @else
                                                <span class="badge bg-light text-dark">{{ __('N/A') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ ucfirst($ticket->getUsers->name) ?? '' }}</td>
                                        <td>{{ ucfirst($ticket->createdBy->name) ?? '' }}</td>
                                        <td>{{ \Auth::user()->dateFormat($ticket->created_at) }}</td>

                                        <td class="Action">
                                            <div class="dt-buttons">
                                                <span>
                                                    <div class="action-btn bg-primary me-2">
                                                        <a href="{{ URL::to('ticket/' . $ticket->id . '/reply') }}"
                                                           class="mx-3 btn btn-sm align-items-center"
                                                           data-bs-toggle="tooltip" title="{{ __('Reply') }}">
                                                            <span class="text-white"><i class="ti ti-arrow-back-up"></i></span>
                                                        </a>
                                                    </div>

                                                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'CEO' || \Auth::user()->type == 'client' || $ticket->ticket_created == \Auth::user()->id)
                                                        @can('Delete Ticket')
                                                            <div class="action-btn bg-danger">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['ticket.destroy', $ticket->id],
                                                                    'id' => 'delete-form-' . $ticket->id,
                                                                ]) !!}
                                                                <a href="#"
                                                                   class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                   data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                    <span class="text-white"><i class="ti ti-trash"></i></span>
                                                                </a>
                                                                </form>
                                                            </div>
                                                        @endcan
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
@push('scripts')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        (function() {
            var options = {
                chart: {
                    height: 140,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                series: {{ $ticket_arr }},
                colors: ["#3ec9d6", '#6fd943', '#fd7e14', '#ff3a6e'],
                labels: ["Total", "Open", "Hold", "Close"],
                legend: {
                    show: false
                }
            };
            var chart = new ApexCharts(document.querySelector("#projects-chart"), options);
            chart.render();
        })();
    </script>

    <script>
        $(document).ready(function () {
            $('#project_id').on('change', function () {
                const selected = $(this).val();
                // Store in session via AJAX
                $.ajax({
                    url: '{{ route("project.select") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        project_id: selected
                    },
                    success: function () {
                        location.reload(); // refresh page to apply filter
                    }
                });
            });
        });
    </script>

@endpush
