{{ Form::open(['url' => route('leave.cancel.store', $leave->id), 'method' => 'POST', 'id' => 'cancel-leave-form']) }}
<div class="modal-body">
    <p>{{ __('Are you sure you want to cancel this leave application?') }}</p>

    <div class="form-group">
        {{ Form::label('cancel_reason', __('Reason for Cancellation'), ['class' => 'form-label']) }}
        {{ Form::select('cancel_reason', [
            'Personal reasons' => 'Personal reasons',
            'Changed plans' => 'Changed plans',
            'Leave applied by mistake' => 'Leave applied by mistake',
            'Other' => 'Other'
        ], null, ['class' => 'form-control select2', 'id' => 'cancel_reason', 'required']) }}
    </div>

    <div class="form-group" id="other-reason-box" style="display:none;">
        {{ Form::label('other_reason', __('Specify Other Reason'), ['class' => 'form-label']) }}
        {{ Form::text('other_reason', null, ['class' => 'form-control']) }}
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <button type="submit" class="btn btn-danger">{{ __('Cancel Leave') }}</button>
</div>
{{ Form::close() }}

<script>
    $(document).ready(function () {
        $('#cancel_reason').on('change', function () {
            if ($(this).val() === 'Other') {
                $('#other-reason-box').show();
            } else {
                $('#other-reason-box').hide();
            }
        });

        $('#cancel-leave-form').on('submit', function (e) {
            const reason = $('#cancel_reason').val();
            const otherReason = $('#other_reason').val();

            if (reason === 'Other' && !otherReason.trim()) {
                alert("Please specify the reason for cancellation.");
                e.preventDefault();
                return false;
            }

            const confirmed = confirm("Are you sure you want to cancel this leave?");
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });
</script>
