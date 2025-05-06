{{ Form::open(['route' => ['leave-employee.quick.action', $leave->id], 'method' => 'POST']) }}
    <div class="modal-body">
        <div class="form-group">
            {{ Form::label('manager_remark', __('Remark'), ['class' => 'form-label']) }}
            {{ Form::textarea('manager_remark', $managerEntry->remark ?? null, [
                'class' => 'form-control',
                'rows' => 4,
                'placeholder' => 'Add your remark...'
            ]) }}
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" name="action" value="approve" class="btn btn-success">
            <i class="ti ti-check"></i> {{ __('Approve') }}
        </button>
        <button type="submit" name="action" value="reject" class="btn btn-danger">
            <i class="ti ti-x"></i> {{ __('Reject') }}
        </button>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    </div>
{{ Form::close() }}
