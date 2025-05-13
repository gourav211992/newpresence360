@php
    $total = $scheduledCount + $selectedCount + $rejectCount + $holdCount;

    $getPercent = function ($count) use ($total) {
        return $total > 0 ? round(($count / $total) * 100) : 0;
    };
@endphp
<div class="row mt-2">
    <div class="col-md-12 leacveprogress totalleave">
        <div class="leavetype-list">
            <h3>Total Qualified</h3>
            <h6>{{ $selectedCount }}({{ $getPercent($selectedCount) }}%)</h6>
        </div>
        <div class="progress progress-bar-success">
            <div class="progress-bar" role="progressbar" aria-valuenow="25" aria-valuemin="25" aria-valuemax="100"
                aria-describedby="example-caption-2" style="width: {{ $getPercent($selectedCount) }}%"></div>
        </div>
    </div>
    <div class="col-md-12 leacveprogress">
        <div class="leavetype-list">
            <h3>Scheduled</h3>
            <h6>{{ $scheduledCount }}({{ $getPercent($scheduledCount) }}%)</h6>
        </div>
        <div class="progress progress-bar-primary">
            <div class="progress-bar" role="progressbar" aria-valuenow="25" aria-valuemin="25" aria-valuemax="100"
                style="width: {{ $getPercent($scheduledCount) }}%" aria-describedby="example-caption-2"></div>
        </div>
    </div>
    <div class="col-md-12 leacveprogress sickleave">
        <div class="leavetype-list">
            <h3>On Hold</h3>
            <h6>{{ $holdCount }}({{ $getPercent($holdCount) }}%)</h6>
        </div>
        <div class="progress progress-bar-warning">
            <div class="progress-bar" role="progressbar" aria-valuenow="25" aria-valuemin="25" aria-valuemax="100"
                style="width: {{ $getPercent($holdCount) }}%" aria-describedby="example-caption-2"></div>
        </div>
    </div>
    <div class="col-md-12 leacveprogress paidleave">
        <div class="leavetype-list">
            <h3>Rejected</h3>
            <h6>{{ $rejectCount }}({{ $getPercent($rejectCount) }}%)</h6>
        </div>
        <div class="progress progress-bar-danger">
            <div class="progress-bar" role="progressbar" aria-valuenow="25" aria-valuemin="25" aria-valuemax="100"
                style="width: {{ $getPercent($rejectCount) }}%" aria-describedby="example-caption-2"></div>
        </div>
    </div>
</div>
