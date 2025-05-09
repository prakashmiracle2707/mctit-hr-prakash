@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Client') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Client') }}</li>
@endsection

@section('action-button')

    @can('Create Employee')
        <a href="{{ route('client.create') }}" data-title="{{ __('Create New Client') }}" data-bs-toggle="tooltip"
            title="" class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
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
                                <th>{{ __('Client ID') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Address') }}</th>
                                @if (Gate::check('Edit Client') || Gate::check('Delete Client'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $client)
                                <tr>
                                    <td>
                                        @can('Show Client')
                                            <a class="btn btn-outline-primary"
                                                href="{{ route('client.show', Crypt::encrypt($client->id)) }}">
                                                {{ 'CLT-' . str_pad($client->id, 4, '0', STR_PAD_LEFT) }}
                                            </a>
                                        @else
                                            <span class="btn btn-outline-secondary">
                                                {{ 'CLT-' . str_pad($client->id, 4, '0', STR_PAD_LEFT) }}
                                            </span>
                                        @endcan
                                    </td>
                                    <td>{{ $client->name }}</td>
                                    <td>{{ $client->email }}</td>
                                    <td>{{ $client->phone }}</td>
                                    <td>{{ $client->address }}</td>
                                    @if (Gate::check('Edit Client') || Gate::check('Delete Client'))
                                        <td class="Action">
                                            <div class="d-flex align-items-center">
                                                @can('Edit Client')
                                                    <div class="action-btn bg-info me-2">
                                                        <a href="{{ route('client.edit', Crypt::encrypt($client->id)) }}"
                                                            class="btn btn-sm align-items-center"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('Delete Client')
                                                    <div class="action-btn bg-danger">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['client.destroy', $client->id], 'id' => 'delete-form-' . $client->id]) !!}
                                                        <a href="#" class="btn btn-sm align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="Delete" aria-label="Delete">
                                                            <span class="text-white"><i class="ti ti-trash"></i></span>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
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
