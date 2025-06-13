<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        .badge {
            padding: 4px 8px;
            color: white;
            border-radius: 4px;
            display: inline-block;
        }
        .bg-success { background-color: #28a745; }
        .bg-warning { background-color: #ffc107; }
        .bg-danger { background-color: #dc3545; }
        .bg-info { background-color: #17a2b8; }
        .bg-primary { background-color: #007bff; }
        .bg-dark { background-color: #343a40; }
    </style>
</head>
<body>

    {{-- ✅ Today’s Section --}}
    <h2>🟢 Today's Leave Summary ({{ \Carbon\Carbon::today()->format('d M Y') }} | {{ \Carbon\Carbon::today()->format('l') }})</h2>

    @if($todayLeaves->isEmpty())
        <p>No approved leaves for today.</p>
    @else
        @include('email.partials.leave_table', ['leaves' => $todayLeaves])
    @endif

    {{-- ✅ Next Working Day Section --}}
    <h2>🟡 Next Working Day Leave Summary ({{ $nextWorkingDay->format('d M Y') }} | {{ $nextWorkingDay->format('l') }})</h2>

    @if($nextDayLeaves->isEmpty())
        <p>No approved leaves for {{ $nextWorkingDay->format('l') }}.</p>
    @else
        @include('email.partials.leave_table', ['leaves' => $nextDayLeaves])
    @endif

</body>
</html>
