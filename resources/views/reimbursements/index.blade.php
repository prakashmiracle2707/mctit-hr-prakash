@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Reimbursements') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Reimbursements') }}</li>
@endsection

@section('action-button')
    @if(Auth::user()->type == 'employee')
        <a href="#" data-url="{{ route('reimbursements.create') }}" data-ajax-popup="true" data-title="{{ __('Create New Reimbursements') }}" data-size="lg"
            data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endif
@endsection

@section('content')
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                @if(Auth::user()->type != 'employee')
                                    <th>{{ __('Employee') }}</th>
                                @endif
                                <th>{{ __('Expense Date') }}</th>
                                <th>{{ __('Subject') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <!-- <th>{{ __('Description') }}</th> -->
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Approved By') }}</th>
                                <!-- <th>{{ __('Approved At') }}</th> -->
                                <th>{{ __('Paid By') }}</th>
                                <!-- <th>{{ __('Paid At') }}</th> -->
                                <th>{{ __('Receipt') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reimbursements as $reimbursement)
                                <tr>
                                    <td>R00{{ $reimbursement->id }}</td>
                                    @if(Auth::user()->type != 'employee')
                                        <td>{{ !empty($reimbursement->employee->name) ? ucfirst($reimbursement->employee->name) : '' }}</td>
                                    @endif
                                    <td>{{ $reimbursement->expense_date ? \Carbon\Carbon::parse($reimbursement->expense_date)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $reimbursement->title }}</td>
                                    <td>&#x20B9; {{ number_format($reimbursement->amount, 2) }}</td>
                                    <!-- <td>{{ $reimbursement->description }}</td> -->
                                    <td>
                                        @if ($reimbursement->status == 'Pending')
                                            <div class="badge bg-warning p-2 px-3 ">{{ $reimbursement->status }}</div>
                                        @elseif($reimbursement->status == 'Approved')
                                            <div class="badge bg-success p-2 px-3 ">{{ $reimbursement->status }}</div>
                                        @elseif($reimbursement->status == "Reject")
                                            <div class="badge bg-danger p-2 px-3 ">{{ $reimbursement->status }}</div>
                                        @elseif($reimbursement->status == "Draft")
                                            <div class="badge bg-info p-2 px-3 ">{{ $reimbursement->status }}</div>
                                        @elseif($reimbursement->status == "Paid")
                                            <div class="badge p-2 px-3" style="background: green;">{{ $reimbursement->status }}</div>
                                        @elseif($reimbursement->status == "Not_Received")
                                            <div class="badge bg-danger p-2 px-3 ">Not Received</div>
                                        @elseif($reimbursement->status == "Received")
                                            <div class="badge bg-success p-2 px-3 ">Received</div>
                                        @elseif($reimbursement->status == "Query_Raised")
                                            <div class="badge bg-warning p-2 px-3 ">Query Raised</div>
                                        @elseif($reimbursement->status == "Submitted")
                                            <div class="badge bg-success p-2 px-3 ">Submitted</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reimbursement->status != "Draft")
                                            <b>{{ $reimbursement->assignedUser->name ?? 'Not Approved' }}</b>
                                        @endif
                                    </td>
                                    <!-- <td>
                                        @if($reimbursement->status != "Draft")
                                            {{ $reimbursement->approved_at ? $reimbursement->approved_at->format('d/m/Y h:i A') : 'Not Approved' }}
                                        @endif
                                    </td> -->
                                    <td>
                                        @if($reimbursement->status != "Draft")
                                            {{ $reimbursement->payer->name ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <!-- <td>
                                        @if($reimbursement->status != "Draft")
                                            {{ $reimbursement->paid_at ? $reimbursement->paid_at->format('Y-m-d h:i A') : 'Not Paid' }}
                                        @endif
                                    </td> -->
                                    <td>
                                        @if($reimbursement->file_path)
                                            <a href="{{ asset('public/uploads/reimbursements/' . $reimbursement->file_path) }}" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="ti ti-eye"></i> {{ __('View') }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $reimbursement->created_at ? \Carbon\Carbon::parse($reimbursement->created_at)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <div class="dt-buttons">
                                        <span>

                                            @if (\Auth::user()->type != 'employee')
                                                <div class="action-btn bg-success me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-size="lg"
                                                        data-url="{{ URL::to('reimbursements/' . $reimbursement->id . '/action') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Reimbursement Action') }}"
                                                        data-bs-original-title="{{ __('Manage Reimbursement') }}">
                                                        <span class="text-white"><i class="ti ti-caret-right"></i></span>
                                                    </a>
                                                </div>
                                                @can('Edit Leave')
                                                    @if(\Auth::user()->type != 'CEO')
                                                        <div class="action-btn bg-info me-2">
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                                data-size="lg"
                                                                data-url="{{ URL::to('reimbursements/' . $reimbursement->id . '/edit') }}"
                                                                data-ajax-popup="true" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Reimbursement') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('Delete Leave')
                                                    @if (\Auth::user()->type != 'employee' && \Auth::user()->type != 'CEO')
                                                        <div class="action-btn bg-danger">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['reimbursements.destroy', $reimbursement->id],
                                                                'id' => 'delete-form-' . $reimbursement->id,
                                                            ]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para delete-confirm"
                                                                data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                                aria-label="Delete" data-id="{{ $reimbursement->id }}">
                                                                <span class="text-white"><i class="ti ti-trash"></i></span>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endif
                                                @endcan
                                            @else
                                                <div class="action-btn bg-success me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-size="lg"
                                                        data-url="{{ URL::to('reimbursements/' . $reimbursement->id . '/action') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Reimbursement Action') }}"
                                                        data-bs-original-title="{{ __('Manage Reimbursement') }}">
                                                        <span class="text-white"><i class="ti ti-caret-right"></i></span>
                                                    </a>
                                                </div>
                                            @endif

                                            @if ($reimbursement->status == "Draft" || (\Auth::user()->type == 'employee' && $reimbursement->status == "Query_Raised"))
                                                <div class="action-btn bg-info me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-size="lg"
                                                        data-url="{{ URL::to('reimbursements/' . $reimbursement->id . '/edit') }}"
                                                        data-ajax-popup="true" data-bs-toggle="tooltip"
                                                        title="" data-title="{{ __('Edit Reimbursement') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                    </a>
                                                </div>
                                                @if (\Auth::user()->type != 'CEO' && $reimbursement->status == "Draft")
                                                    <div class="action-btn bg-danger">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['reimbursements.destroy', $reimbursement->id],
                                                            'id' => 'delete-form-' . $reimbursement->id,
                                                        ]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para delete-confirm"
                                                            data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                            aria-label="Delete" data-id="{{ $reimbursement->id }}">
                                                            <span class="text-white"><i class="ti ti-trash"></i></span>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endif
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
@endsection


