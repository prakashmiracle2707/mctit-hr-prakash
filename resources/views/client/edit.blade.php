@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Client') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('client.index') }}">{{ __('Client') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Client') }}</li>
@endsection

@section('content')
    <div class="">
        <div class="row">
            {!! Form::model($client, ['route' => ['client.update', $client->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) !!}
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Client Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Name --}}
                            <div class="form-group col-md-6">
                                {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}<x-required />
                                {!! Form::text('name', null, ['class' => 'form-control', 'required' => true, 'placeholder' => __('Enter client name')]) !!}
                            </div>

                            {{-- Email --}}
                            <div class="form-group col-md-6">
                                {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<x-required />
                                {!! Form::email('email', null, ['class' => 'form-control', 'required' => true, 'placeholder' => __('Enter client email')]) !!}
                            </div>

                            {{-- Phone --}}
                            <div class="form-group col-md-6">
                                {!! Form::label('phone', __('Phone'), ['class' => 'form-label']) !!}
                                {!! Form::text('phone', null, ['class' => 'form-control', 'placeholder' => __('Enter phone number')]) !!}
                            </div>

                            {{-- Company Name --}}
                            <div class="form-group col-md-6">
                                {!! Form::label('company_name', __('Company Name'), ['class' => 'form-label']) !!}
                                {!! Form::text('company_name', null, ['class' => 'form-control', 'placeholder' => __('Enter company name')]) !!}
                            </div>

                            {{-- Address --}}
                            <div class="form-group col-md-12">
                                {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}
                                {!! Form::textarea('address', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Enter address')]) !!}
                            </div>
                        </div>

                        {{-- Document Upload (Optional) --}}
                        @if(isset($documents) && count($documents) > 0)
                            <hr>
                            <h6>{{ __('Client Documents') }}</h6>
                            @foreach ($documents as $key => $document)
                                <div class="form-group row align-items-center">
                                    <div class="col-md-4">
                                        <label for="document_{{ $document->id }}" class="form-label">{{ $document->name }} 
                                            @if ($document->is_required)
                                                <x-required />
                                            @endif
                                        </label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="file" name="document[{{ $document->id }}]" class="form-control" id="document_{{ $document->id }}">
                                        @php
                                            $docPath = isset($clientDocs[$document->id]) ? asset('uploads/client_documents/' . $clientDocs[$document->id]) : null;
                                        @endphp
                                        @if($docPath)
                                            <a href="{{ $docPath }}" target="_blank" class="text-sm text-primary mt-1 d-block">{{ __('View Uploaded') }}</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <div class="text-end mt-3">
                            <a href="{{ route('client.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
