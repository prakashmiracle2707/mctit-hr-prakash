@php
    $chatgpt = App\Models\Utility::getValByName('enable_chatgpt');
@endphp

{{ Form::open(['url' => route('it-tickets.store'), 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">

    @if ($chatgpt == 'on')
    <div class="card-footer text-end">
        <a href="#" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true"
            data-url="{{ route('generate', ['it_ticket']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
            title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
            <i class="fas fa-robot"></i> {{ __(' Generate With AI') }}
        </a>
    </div>
    @endif

    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('issue_category_id', __('Issue Category'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::select('issue_category_id', $categories, null, [
                'class' => 'form-control',
                'required' => 'required',
                'placeholder' => __('Select Category'),
                'id' => 'issue_category_id',
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('issue_title_id', __('Issue Title'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::select('issue_title_id', [], null, [
                'class' => 'form-control',
                'required' => 'required',
                'placeholder' => __('Select Title'),
                'id' => 'issue_title_id',
            ]) }}
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('description', __('Issue Description'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::textarea('description', null, [
                'class' => 'form-control',
                'required' => 'required',
                'placeholder' => __('Describe the issue in detail'),
                'rows' => 3
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('priority', __('Priority'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::select('priority', ['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low'], null, [
                'class' => 'form-control',
                'required' => 'required'
            ]) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>

{{ Form::close() }}

{{-- JS for dependent dropdown --}}
<script>
    $(document).ready(function () {
        $('#issue_category_id').on('change', function () {
            let categoryId = $(this).val();
            $('#issue_title_id').html('<option value="">{{ __("Loading...") }}</option>');

            $.ajax({
                url: '{{ url("get-titles-by-category") }}/' + categoryId,
                type: 'GET',
                success: function (data) {
                    console.log("Received data:", data);
                    let options = '<option value="">' + '{{ __("Select Title") }}' + '</option>';
                    $.each(data, function (key, value) {
                        options += `<option value="${key}">${value}</option>`;
                    });
                    $('#issue_title_id').html(options);
                },
                error: function () {
                    alert('Error fetching issue titles. Please check the console.');
                }
            });
        });
    });
</script>

