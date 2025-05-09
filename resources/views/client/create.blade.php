@extends('layouts.admin')

@section('page-title')
    {{ __('Create Client') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('client.index') }}">{{ __('Client') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Client') }}</li>
@endsection

@section('content')
    <div class="">
        <div class="row">
            {{ Form::open(['route' => ['client.store'], 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Client Details') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}<x-required></x-required>
                                    {!! Form::text('name', old('name'), [
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'placeholder' => __('Enter client name'),
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<x-required></x-required>
                                    {!! Form::email('email', old('email'), [
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'placeholder' => __('Enter client email'),
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('phone', __('Phone'), ['class' => 'form-label']) !!}<x-required></x-required>
                                    {!! Form::text('phone', old('phone'), [
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'placeholder' => __('Enter client phone'),
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('password', __('Password'), ['class' => 'form-label']) !!}<x-required></x-required>
                                    {!! Form::password('password', [
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'placeholder' => __('Enter password'),
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-12">
                                    {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<x-required></x-required>
                                    {!! Form::textarea('address', old('address'), [
                                        'class' => 'form-control',
                                        'rows' => 3,
                                        'required' => 'required',
                                        'placeholder' => __('Enter client address'),
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <a class="btn btn-secondary" href="{{ route('client.index') }}">{{ __('Cancel') }}</a>
                <button class="btn btn-primary ms-1" type="submit">{{ __('Create') }}</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection
