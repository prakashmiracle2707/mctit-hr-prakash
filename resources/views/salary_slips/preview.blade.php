@extends('layouts.admin')

@section('page-title', __('Review Salary Slips'))

@section('content')
<div class="card">
    <div class="card-header">
        {{ __('Review Salary Slips') }}
    </div>
    <div class="card-body">
        @if(session('salary_slip_preview'))
            @php
                $successCount = collect(session('salary_slip_preview')['data'])->filter(fn($slip) => $slip['employee'])->count();
                $failedCount = collect(session('salary_slip_preview')['data'])->filter(fn($slip) => !$slip['employee'])->count();
            @endphp

            <div class="mb-3">
                <span class="badge bg-success p-2">{{ __('Success: ') }} <span id="successCount">{{ $successCount }}</span></span>
                <span class="badge bg-danger p-2 ms-2">{{ __('Failed: ') }} <span id="failedCount">{{ $failedCount }}</span></span>
            </div>

            <form id="confirm-upload-form" action="{{ route('salary_slips.confirm') }}" method="POST">
                @csrf
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('Original File Name') }}</th>
                            <th>{{ __('Employee Name') }}</th>
                            <th>{{ __('Employee Matched?') }}</th>
                            <th>{{ __('Duplicate?') }}</th>
                            <th>{{ __('File') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody id="preview-list">
                        @foreach(session('salary_slip_preview')['data'] as $index => $slip)
                            <tr id="row-{{ $index }}">
                                <td>{{ $slip['original_name'] }}</td>
                                <td>{{ $slip['employee_name'] }}</td>
                                <td class="match-status">
                                    @if($slip['employee'])
                                        <span class="text-success">Matched ({{ $slip['employee']->name }})</span>
                                    @else
                                        <span class="text-danger">Not Found</span>
                                    @endif
                                </td>
                                <td>
                                    @if($slip['is_duplicate'])
                                        <span class="text-danger">{{ __('Duplicate Found') }}</span>
                                    @else
                                        <span class="text-success">{{ __('Unique') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ asset($slip['file_path']) }}" target="_blank" class="btn btn-primary btn-sm">
                                        <i class="ti ti-eye"></i> {{ __('View') }}
                                    </a>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm delete-preview" data-index="{{ $index }}" data-matched="{{ $slip['employee'] ? 'true' : 'false' }}">
                                        <i class="ti ti-trash"></i> {{ __('Delete') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <br />
                <button type="button" id="confirm-upload-btn" class="btn btn-success">
                    {{ __('Confirm & Upload') }}
                </button>
            </form>
        @else
            <p class="text-danger">{{ __('No salary slips found.') }}</p>
        @endif
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".delete-preview").forEach(button => {
            button.addEventListener("click", function() {
                let index = this.dataset.index;
                let row = document.getElementById("row-" + index);
                let isMatched = this.dataset.matched === "true"; // Check if matched or not

                fetch("{{ route('salary_slips.delete_preview') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ index: index })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.remove(); // Remove row from the table

                        // Update counts
                        let successCount = parseInt(document.getElementById("successCount").innerText);
                        let failedCount = parseInt(document.getElementById("failedCount").innerText);

                        if (isMatched) {
                            document.getElementById("successCount").innerText = successCount - 1;
                        } else {
                            document.getElementById("failedCount").innerText = failedCount - 1;
                        }
                    } else {
                        alert("Error: Could not delete the record.");
                    }
                })
                .catch(error => console.error("Error:", error));
            });
        });

        // Add confirmation before uploading salary slips
        document.getElementById("confirm-upload-btn").addEventListener("click", function() {
            let confirmation = confirm("Are you sure you want to upload these salary slips?");
            if (confirmation) {
                document.getElementById("confirm-upload-form").submit();
            }
        });
    });
</script>
@endsection