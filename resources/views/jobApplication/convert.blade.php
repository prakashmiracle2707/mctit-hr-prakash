@extends('layouts.admin')
@section('page-title')
    {{ __('Convert To Employee') }}
@endsection


@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('home') }}">{{ __('Home') }}</a>
    </li>
    <li class="breadcrumb-item"><a href="{{ route('job.on.board') }}">{{ __('Job OnBoard') }}</a></li>

    <li class="breadcrumb-item">{{ __('Convert To Employee') }}</li>
@endsection


@section('content')
    <div class="row">
        {{ Form::open(['route' => ['job.on.board.convert', $jobOnBoard->id], 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
    </div>

    <div class="col-md-6 ">
        <div class="card  em-card">
            <div class="card-header">
                <h6 class="mb-0">{{ __('Personal Detail') }}</h6>
            </div>
            <div class="card-body ">
                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}<x-required></x-required>
                        {!! Form::text('name', !empty($jobOnBoard->applications) ? $jobOnBoard->applications->name : '', [
                            'class' => 'form-control',
                            'required' => 'required',
                        ]) !!}
                    </div>
                    <x-mobile divClass="col-md-6" name="phone" label="{{ __('Phone') }}"
                            placeholder="{{ __('Enter employee phone') }}" id="phone" required="true" value="{{ !empty($jobOnBoard->applications) ? $jobOnBoard->applications->phone : '' }}">
                        </x-mobile>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<x-required></x-required>
                            {!! Form::date('dob', !empty($jobOnBoard->applications) ? $jobOnBoard->applications->dob : '', [
                                'class' => 'form-control ',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6 ">
                        <div class="form-group ">
                            {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<x-required></x-required>
                            <div class="d-flex radio-check">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="g_male" value="Male" name="gender"
                                        class="form-check-input"
                                        {{ !empty($jobOnBoard->applications) && $jobOnBoard->applications->gender == 'Male' ? 'checked' : '' }}>
                                    <label class="form-check-label " for="g_male">{{ __('Male') }}</label>
                                </div>
                                <div class="custom-control custom-radio ms-1 custom-control-inline">
                                    <input type="radio" id="g_female" value="Female" name="gender"
                                        class="form-check-input"
                                        {{ !empty($jobOnBoard->applications) && $jobOnBoard->applications->gender == 'Female' ? 'checked' : '' }}>
                                    <label class="form-check-label " for="g_female">{{ __('Female') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<x-required></x-required>
                        {!! Form::email('email', !empty($jobOnBoard->applications) ? $jobOnBoard->applications->email : '', ['class' => 'form-control', 'placeholder' => __('Enter Email'), 'required' => 'required']) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('password', __('Password'), ['class' => 'form-label']) !!}<x-required></x-required>
                        {!! Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter Password'), 'required' => 'required']) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<x-required></x-required>
                    {!! Form::textarea('address', old('address'), [
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => 'Enter Address',
                    ]) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 ">
        <div class="card  " style="min-height:550px;">
            <div class="card-header">
                <h6 class="mb-0">{{ __('Company Detail') }}</h6>
            </div>
            <div class="card-body employee-detail-create-body">
                <div class="row">
                    @csrf
                    <div class="form-group col-md-12">
                        {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}
                        {!! Form::text('employee_id', $employeesId, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                    </div>

                    <div class="form-group col-md-6">
                        {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}<x-required></x-required>
                        {{ Form::select('branch_id', $branches, !empty($jobOnBoard->applications) ? (!empty($jobOnBoard->applications->jobs) ? $jobOnBoard->applications->jobs->branch : '') : '', ['class' => 'form-control branch_id', 'required' => 'required', 'id' => 'branch_id']) }}
                    </div>

                    <div class="form-group col-md-6">
                        {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}<x-required></x-required>
                        {{ Form::select('department_id', $departments, null, ['class' => 'form-control department_id', 'id' => 'department_id', 'required' => 'required', 'placeholder' => 'Select Department']) }}
                    </div>

                    <div class="form-group ">
                        {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}<x-required></x-required>
                        <select class=" form-control select2-multiple" id="designation_id" name="designation_id"
                            data-toggle="select2" data-placeholder="{{ __('Select Designation ...') }}">
                            <option value="">{{ __('Select any Designation') }}</option>
                        </select>
                    </div>
                    <div class="form-group ">
                        {!! Form::label('company_doj', __('Company Date Of Joining'), ['class' => 'form-label']) !!}<x-required></x-required>
                        {!! Form::date('company_doj', $jobOnBoard->joining_date, ['class' => 'form-control ', 'required' => 'required']) !!}
                    </div>

                    {{-- <div class="form-group col-md-6">
                        {!! Form::label('termination_date', __('Termination Date'), ['class' => 'form-label']) !!}
                        {!! Form::date('termination_date', old('termination_date'), ['class' => 'form-control ', 'required' => 'required']) !!}
                    </div> --}}


                </div>
            </div>
        </div>
    </div>


    <div class="col-md-6 ">
        <div class="card  em-card">
            <div class="card-header">
                <h6 class="mb-0">{{ __('Document') }}</h6>
            </div>
            <div class="card-body employee-detail-create-body">
                @foreach ($documents as $key => $document)
                    {{-- <div class="row">
                        <div class="form-group col-12">
                            <div class="float-left col-4">
                                <label for="document" class="float-left pt-1 form-label">{{ $document->name }}
                                    @if ($document->is_required == 1)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                            </div>
                            <div class="float-right col-8">
                                <input type="hidden" name="emp_doc_id[{{ $document->id }}]" id=""
                                    value="{{ $document->id }}">
                                <div class="choose-file form-group">
                                    <label for="document[{{ $document->id }}]">
                                        <div>{{ __('Choose File') }}</div>
                                        <input class="form-control  @error('document') is-invalid @enderror border-0"
                                            @if ($document->is_required == 1) required @endif
                                            name="document[{{ $document->id }}]" type="file"
                                            id="document[{{ $document->id }}]"
                                            data-filename="{{ $document->id . '_filename' }}">
                                    </label>
                                    <p class="{{ $document->id . '_filename' }}"></p>
                                </div>

                            </div>

                        </div>
                    </div> --}}
                    <div class="row">
                        <div class="form-group col-12 d-flex">
                            <div class="float-left col-4">
                                <label for="document" class="float-left pt-1 form-label">{{ $document->name }}
                                    @if ($document->is_required == 1)
                                        <x-required></x-required>
                                    @endif
                                </label>
                            </div>
                            <div class="float-right col-8">
                                <input type="hidden" name="emp_doc_id[{{ $document->id }}]" id=""
                                    value="{{ $document->id }}">

                                <div class="choose-files ">
                                    <label for="document[{{ $document->id }}]">
                                        <div class=" bg-primary document "> <i
                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                        </div>
                                        <input type="file"
                                            class="form-control file  d-none @error('document') is-invalid @enderror"
                                            @if ($document->is_required == 1) required @endif
                                            name="document[{{ $document->id }}]" id="document[{{ $document->id }}]"
                                            data-filename="{{ $document->id . '_filename' }}">
                                    </label>
                                    <a href="#">
                                        <p class="{{ $document->id . '_filename' }} "></p>
                                    </a>
                                </div>

                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-6 ">
        <div class="card  em-card">
            <div class="card-header">
                <h6 class="mb-0">{{ __('Bank Account Detail') }}</h6>
            </div>
            <div class="card-body employee-detail-create-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                        {!! Form::text('account_holder_name', old('account_holder_name'), [
                            'class' => 'form-control',
                            'placeholder' => __('Enter Account Holder Name'),
                        ]) !!}

                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('account_number', __('Account Number'), ['class' => 'form-label']) !!}
                        {!! Form::number('account_number', old('account_number'), [
                            'class' => 'form-control',
                            'placeholder' => __('Enter Account Number'),
                        ]) !!}

                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) !!}
                        {!! Form::text('bank_name', old('bank_name'), ['class' => 'form-control', 'placeholder' => __('Enter Bank Name')]) !!}

                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('bank_identifier_code', __('Bank Identifier Code'), ['class' => 'form-label']) !!}
                        {!! Form::text('bank_identifier_code', old('bank_identifier_code'), [
                            'class' => 'form-control',
                            'placeholder' => __('Enter Bank Identifier Code'),
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('branch_location', __('Branch Location'), ['class' => 'form-label']) !!}
                        {!! Form::text('branch_location', old('branch_location'), [
                            'class' => 'form-control',
                            'placeholder' => __('Enter Branch Location'),
                        ]) !!}
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('tax_payer_id', __('Tax Payer Id'), ['class' => 'form-label']) !!}
                        {!! Form::text('tax_payer_id', old('tax_payer_id'), [
                            'class' => 'form-control',
                            'placeholder' => __('Enter Tax Payer Id'),
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="col-md-12 text-end">
        <a class="btn btn-secondary btn-submit" href="{{ route('job.on.board') }}">{{ __('Cancel') }}</a>
        {!! Form::submit('Create', ['class' => 'btn btn-primary btn-submit ms-1']) !!}
        {{ Form::close() }}
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            var b_id = $('#branch_id').val();
            // getDepartment(b_id);
        });
        $(document).on('change', 'select[name=branch_id]', function() {
            var branch_id = $(this).val();

            getDepartment(branch_id);
        });

        function getDepartment(bid) {

            $.ajax({
                url: '{{ route('monthly.getdepartment') }}',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('.department_id').empty();
                    var emp_selct = `<select class="form-control department_id" name="department_id" id="choices-multiple"
                                            placeholder="Select Department" required>
                                            </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value=""> {{ __('Select Department') }} </option>');
                    $.each(data, function(key, value) {
                        $('.department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        }

        $(document).ready(function() {
            var d_id = $('#department_id').val();
            getDesignation(d_id);
        });

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        function getDesignation(did) {

            $.ajax({
                url: '{{ route('employee.json') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#designation_id').empty();
                    $('#designation_id').append(
                        '<option value="">{{ __('Select any Designation') }}</option>');
                    $.each(data, function(key, value) {
                        $('#designation_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                }
            });
        }
    </script>
@endpush
