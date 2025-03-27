@php
    $chatgpt = App\Models\Utility::getValByName('enable_chatgpt');
@endphp

{{ Form::model($complaint, ['route' => ['complaints.update', $complaint->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">

    @if ($chatgpt == 'on')
    <div class="card-footer text-end">
        <a href="#" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true"
            data-url="{{ route('generate', ['complaint']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
            title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
            <i class="fas fa-robot"></i> {{ __(' Generate With AI') }}
        </a>
    </div>
    @endif

    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('issue_category_id', __('Issue Category'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::select('issue_category_id', $categories, $complaint->issue_category_id, [
                'class' => 'form-control select2',
                'required' => 'required',
                'id' => 'edit_issue_category_id',
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('issue_title_id', __('Issue Title'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::select('issue_title_id', $titles, $complaint->issue_title_id, [
                'class' => 'form-control select2',
                'required' => 'required',
                'id' => 'edit_issue_title_id',
            ]) }}
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::textarea('description', null, [
                'class' => 'form-control',
                'rows' => 3,
                'required' => true,
                'placeholder' => __('Describe the issue in detail'),
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('priority', __('Priority'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::select('priority', ['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low'], $complaint->priority, [
                'class' => 'form-control',
                'required' => true
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('status', __('Status'), ['class' => 'col-form-label']) }} <x-required></x-required>

            @php
                $allStatuses = [
                    'Open' => 'Open',
                    'Under Review' => 'Under Review',
                    'In Progress' => 'In Progress',
                    'Resolved' => 'Resolved',
                    'Closed' => 'Closed',
                    'Rejected' => 'Rejected',
                ];

                $reviewerStatuses = [
                    'Under Review' => 'Under Review',
                    'In Progress' => 'In Progress',
                    'Resolved' => 'Resolved',
                    'Rejected' => 'Rejected',
                ];

                $employeeStatuses = [
                    'Open' => 'Open',
                    'Closed' => 'Closed',
                ];

                $user = Auth::user();

                $hasReviewerRole = $user->secondaryRoleAssignments()
                    ->whereHas('role', fn($q) => $q->where('name', 'Complaint-Reviewer'))
                    ->exists();

                if ($hasReviewerRole && $complaint->employee_id == \Auth::user()->id) {
                    $statusOptions = $allStatuses;
                }elseif($hasReviewerRole){
                    $statusOptions = $reviewerStatuses;
                } else {
                     $statusOptions = $employeeStatuses;
                }
            @endphp

            {{ Form::select('status', $statusOptions, $complaint->status, [
                'class' => 'form-control',
                'required' => true
            ]) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>

{{ Form::close() }}

<script>
$(document).ready(function () {
    $('.select2').select2({
        placeholder: "Select option",
        allowClear: true
    });

    $('#edit_issue_category_id').on('change', function () {
        let categoryId = $(this).val();
        $('#edit_issue_title_id').html('<option>Loading...</option>');

        $.ajax({
            url: '{{ url("get-titles-by-category") }}/' + categoryId,
            method: 'GET',
            success: function (data) {
                let options = '<option value="">{{ __("Select Title") }}</option>';
                $.each(data, function (key, value) {
                    options += '<option value="' + key + '">' + value + '</option>';
                });
                $('#edit_issue_title_id').html(options);
            }
        });
    });
});
</script>
