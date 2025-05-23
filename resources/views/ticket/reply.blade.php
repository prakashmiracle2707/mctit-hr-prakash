@php
    $chatgpt = App\Models\Utility::getValByName('enable_chatgpt');
    $attechment = \App\Models\Utility::get_file('uploads/tickets/');
@endphp

@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{ __('Ticket Reply') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{ __('Ticket Reply') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('ticket') }}">{{ __('Ticket') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Ticket Reply') }}</li>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@endpush

@push('script-page')
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
@endpush

@section('action-button')
    @if (\Auth::user()->type == 'company' || $ticket->ticket_created == \Auth::user()->id)
        <div class="float-end">
            <a href="#" data-size="lg" data-url="{{ URL::to('ticket/' . $ticket->id . '/edit') }}"
                data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                data-title="{{ __('Edit Ticket') }}" class="btn btn-sm btn-info">
                <i class="ti ti-pencil"></i>
            </a>
        </div>
    @endif
@endsection

@section('content')
    <div class="">
        <div class="col-12">
            <div class="row gy-4">

                @if($ticket->parent)
                    <div class="alert alert-info">
                        <strong>{{ __('Parent Ticket:') }}</strong>
                        <a href="{{ route('ticket.reply', $ticket->parent->id) }}">{{ $ticket->parent->title }}</a>
                    </div>
                @endif

                @if($ticket->parent_id == null && $ticket->ticket_type_id == 2)
                <div class="row">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
                            <h5 class="mb-0 text-dark fw-bold">{{ __('Child Work Items') }}</h5>
                            <a href="#" 
                               data-url="{{ route('ticket.create', ['parent_id' => $ticket->id]) }}"
                               data-ajax-popup="true"
                               data-title="{{ __('Create Sub Task') }}"
                               data-size="lg"
                               class="btn btn-sm btn-outline-primary">
                               {{ __('Add Sub Task') }}
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    @php
                                        $total = $ticket->subtasks->count();
                                        $done = $ticket->subtasks->where('status', 'close')->count();
                                        $percent = $total ? round(($done / $total) * 100) : 0;
                                    @endphp

                                    <div class="progress mx-4 mt-3" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="px-4 pb-3 text-muted small">
                                        {{ $percent }}% {{ __('Done') }}
                                    </div>
                                </div>
                                <div class="col-12">
                                    <br />
                                    <br />
                                    <div class="table-responsive">
                                        <table class="table" id="pc-dt-simple">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Ticket Code') }}</th>
                                                    @if (session('selected_project_id') === 'all')
                                                        <th>{{ __('Project') }}</th>
                                                    @endif
                                                    <th>{{ __('Title') }}</th>
                                                    @role('company|client')
                                                        <th>{{ __('Assignee') }}</th>
                                                    @endrole
                                                    <th>{{ __('Priority') }}</th>
                                                    <th>{{ __('Date') }}</th>
                                                    <th>{{ __('Created By') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th width="200px">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($ticket->subtasks as $subtask)
                                                    <tr>
                                                        <td>
                                                            @if ($subtask->ticketUnread() > 0)
                                                                <i title="New Message" class="fas fa-circle circle text-success"></i>
                                                            @endif

                                                            @if ($subtask->parent_id != null)
                                                                <img src="{{ asset('public/uploads/ticket/ticket-type/SubTask.svg') }}"
                                                                     alt="{{ $subtask->type->name }}"
                                                                     title="{{ $subtask->type->name }}"
                                                                     style="width: 20px; height: 20px; object-fit: contain; margin-right: 5px;">
                                                            @else
                                                                @if (!empty($subtask->type->image))
                                                                    <img src="{{ asset('public/'.$subtask->type->image) }}"
                                                                         alt="{{ $subtask->type->name }}"
                                                                         title="{{ $subtask->type->name }}"
                                                                         style="width: 20px; height: 20px; object-fit: contain; margin-right: 5px;">
                                                                @endif
                                                            @endif

                                                            <a href="{{ URL::to('ticket/' . $subtask->id . '/reply') }}"
                                                                           class="btn btn-sm align-items-center"
                                                                           data-bs-toggle="tooltip" title="{{ __('Reply') }}">{{ $subtask->ticket_code }}
                                                            </a>
                                                        </td>
                                                        @if (session('selected_project_id') === 'all')
                                                        <td>
                                                            {{ $subtask->project->name ?? '-' }}
                                                        </td>
                                                        @endif
                                                        <td style="white-space: normal; word-wrap: break-word; word-break: break-word; overflow-wrap: break-word;width: 400px;">{{ $subtask->title }}</td>

                                                        @role('company|client')
                                                            <td>{{ ucfirst($subtask->getUsers->name) ?? '' }}</td>
                                                        @endrole

                                                        <td>
                                                            @if ($subtask->getpriority)
                                                                <div class="badge p-2 px-3"
                                                                     style="background-color: {{ $subtask->getpriority->color ?? '#6c757d' }};">
                                                                    {{ $subtask->getpriority->name }}
                                                                </div>
                                                            @else
                                                                <span class="badge bg-light text-dark">{{ __('N/A') }}</span>
                                                            @endif
                                                        </td>

                                                        <td>{{ \Auth::user()->dateFormat($subtask->end_date) }}</td>

                                                        <td>{{ ucfirst($subtask->createdBy->name) ?? '' }}</td>

                                                        <td>
                                                            @if ($subtask->getstatus)
                                                                <div class="badge p-2 px-3"
                                                                     style="background-color: {{ $subtask->getstatus->color ?? '#6c757d' }};">
                                                                    {{ $subtask->getstatus->name }}
                                                                </div>
                                                            @else
                                                                <span class="badge bg-light text-dark">{{ __('N/A') }}</span>
                                                            @endif
                                                        </td>

                                                        <td class="Action">
                                                            <div class="dt-buttons">
                                                                <span>
                                                                    <div class="action-btn bg-primary me-2">
                                                                        <a href="{{ URL::to('ticket/' . $subtask->id . '/reply') }}"
                                                                           class="mx-3 btn btn-sm align-items-center"
                                                                           data-bs-toggle="tooltip" title="{{ __('Reply') }}">
                                                                            <span class="text-white"><i class="ti ti-arrow-back-up"></i></span>
                                                                        </a>
                                                                    </div>

                                                                    @if (\Auth::user()->type == 'company' || $subtask->ticket_created == \Auth::user()->id)
                                                                        @can('Delete Ticket')
                                                                            <div class="action-btn bg-danger">
                                                                                {!! Form::open([
                                                                                    'method' => 'DELETE',
                                                                                    'route' => ['ticket.destroy', $subtask->id],
                                                                                    'id' => 'delete-form-' . $subtask->id,
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
                </div>
                @endif


                <div class="col-lg-6">
                    <div class="row">
                        <h5 class="mb-3">{{ __('Reply Ticket') }} - <span class="text-success">{{ $ticket->ticket_code }}</span></h5>
                        <div class="card border">
                            <div class="card-body p-0">
                                <div class="p-4 border-bottom">

                                    @if ($ticket->getpriority)
                                        <div class="badge mb-2" style="background-color: {{ $ticket->getpriority->color }}">
                                            {{ $ticket->getpriority->name }}
                                        </div>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="two-line-truncate">{{ $ticket->title }}</h5>
                                        @if ($ticket->getstatus)
                                            <span class="badge p-2 f-w-600" style="background-color: {{ $ticket->getstatus->color }}; color: #fff">
                                                {{ $ticket->getstatus->name }}
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mb-0">
                                        <b>{{ $ticket->createdBy->name ?? '' }}</b>.
                                        <span>{{ $ticket->createdBy->email ?? '' }}</span>.
                                        <span class="text-muted">{{ \Auth::user()->dateFormat($ticket->created_at) }}</span>
                                    </p>
                                </div>

                                @if (!empty($ticket->description))
                                    <div class="p-4">
                                        <p>{!! $ticket->description !!}</p>

                                        @if (!empty($ticket->attachment))
                                            @php
                                                $filename = $ticket->attachment;
                                                $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                                $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                                $filePath = $attechment.$ticket->project_id."/" . $filename;
                                            @endphp

                                            <h6>{{ __('Attachments') }}:</h6>
                                            <div class="row">
                                                <div class="col-md-3 col-sm-4 mb-3">
                                                    <div class="card shadow-sm border text-center">
                                                        @if ($isImage)
                                                            <a href="{{ $filePath }}" target="_blank">
                                                                <img src="{{ $filePath }}" class="card-img-top img-fluid" alt="{{ $filename }}" style="height: 150px; object-fit: cover;" onerror="this.src='{{ asset(Storage::url('uploads/avatar/default.png')) }}';">
                                                            </a>
                                                        @else
                                                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 150px;">
                                                                <i class="fas fa-file-alt fa-3x text-muted"></i>
                                                            </div>
                                                        @endif

                                                        <div class="card-body p-2">
                                                            <p class="small text-truncate mb-2" title="{{ $filename }}">{{ $filename }}</p>
                                                            <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-primary w-100">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($ticket->status != 5)
                        <div class="row">
                            <div class="card">
                                <div class="card-body">

                                    @if ($chatgpt == 'on')
                                        <div class="text-end">
                                            <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm"
                                                data-ajax-popup-over="true" id="grammarCheck"
                                                data-url="{{ route('grammar', ['grammar']) }}" data-bs-placement="top"
                                                data-title="{{ __('Grammar check with AI') }}">
                                                <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                                            </a>
                                        </div>
                                    @endif

                                    <h5 class="mb-3">{{ __('Comments') }}</h5>
                                    {{ Form::open(['url' => 'ticket/changereply', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                                    <input type="hidden" value="{{ $ticket->id }}" name="ticket_id">
                                    <textarea class="form-control summernote-simple-2" name="description" id="exampleFormControlTextarea1" rows="7"></textarea>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label class="form-label">{{ __('Attachments') }}</label>
                                            <div class="col-sm-12 col-md-12">
                                                <div class="form-group col-lg-12 col-md-12">
                                                    <div class="choose-file form-group">
                                                        <label for="file" class="form-label">
                                                            <input type="file" name="attachment" id="attachment"
                                                                class="form-control {{ $errors->has('attachment') ? ' is-invalid' : '' }}"
                                                                onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])"
                                                                data-filename="attachments">
                                                            <div class="invalid-feedback">
                                                                {{ $errors->first('attachment') }}
                                                            </div>
                                                        </label>
                                                        <p class="attachments"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="form-label"></label>
                                            <div class="col-sm-12 col-md-12">
                                                <div class="form-group col-lg-12 col-md-12">
                                                    <img src="" id="blah" width="60%" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-sm bg-primary w-100" style="color: white">
                                                <i class="ti ti-circle-plus me-1 mb-0"></i> {{ __('Send') }}</button>
                                        </div>
                                    </div>
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if(count($ticketreply) > 0)
                <div class="col-lg-6">
                    <h5 class="mb-3">{{ __('Replies') }}</h5>
                    @foreach ($ticketreply as $reply)
                        <div class="card border">
                            <div class="card-header row d-flex align-items-center justify-content-between">
                                <div class="header-right col d-flex align-items-start">
                                    <a href="#" class="avatar avatar-sm me-3">
                                        <img alt=""
                                             class="img-fluid rounded border-2 border border-primary"
                                             width="50px"
                                             style="height: 50px"
                                             src="{{ asset(Storage::url('uploads/avatar/' . ($reply->users->avatar ?? 'avatar.png'))) }}"
                                             onerror="this.onerror=null; this.src='{{ asset(Storage::url('uploads/avatar/avatar.png')) }}';">
                                    </a>
                                    <h6 class="mb-0">{{ !empty($reply->users) ? ucfirst($reply->users->name) : '' }}
                                        <div class="d-block text-muted">
                                            {{ !empty($reply->users) ? $reply->users->email : '' }}
                                        </div>
                                    </h6>
                                </div>
                                <p class="col-auto ms-1 mb-0"> <span
                                        class="text-muted">{{ $reply->created_at->diffForHumans() }}</span></p>
                            </div>
                            @if (!empty($reply->description))
                                <div class="p-4">
                                    <p>{!! $reply->description !!}</p>

                                    @if (!empty($reply->attachment))
                                        @php
                                            $filename = $reply->attachment;
                                            $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                            $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                            $filePath = $attechment.$ticket->project_id."/" . $filename; // example: asset('storage/uploads/attachments/')
                                        @endphp

                                        <h6>{{ __('Attachments') }}:</h6>
                                        <div class="row">
                                            <div class="col-md-3 col-sm-4 mb-3">
                                                <div class="card shadow-sm border text-center">
                                                    @if ($isImage)
                                                        <a href="{{ $filePath }}" target="_blank">
                                                            <img src="{{ $filePath }}"
                                                                 class="card-img-top img-fluid"
                                                                 alt="{{ $filename }}"
                                                                 style="height: 150px; object-fit: cover;"
                                                                 onerror="this.src='{{ asset(Storage::url('uploads/avatar/default.png')) }}';">
                                                        </a>
                                                    @else
                                                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light"
                                                             style="height: 150px;">
                                                            <i class="fas fa-file-alt fa-3x text-muted"></i>
                                                        </div>
                                                    @endif

                                                    <div class="card-body p-2">
                                                        <p class="small text-truncate mb-2" title="{{ $filename }}">{{ $filename }}</p>
                                                        <a href="{{ $filePath }}" download class="btn btn-sm btn-outline-primary w-100">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

@endsection
