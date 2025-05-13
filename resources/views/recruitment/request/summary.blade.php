<div class="col-xl-12 col-md-6 col-12">
    <div class="card card-statistics">
        <div class="card-header newheader pb-0">
            <div class="header-left">
                <h4 class="card-title">Summary - <span class="font-small-3 fw-bold"
                        style="font-color:#999">{{ request('date_range') ? request('date_range') : 'As on ' . date('d-m-Y') }}</span>
                </h4>
            </div>
        </div>
        <div class="card-body statistics-body">
            <div class="row">
                <div class="col  mb-2 mb-xl-0">
                    <div class="d-flex flex-row">
                        <div class="avatar bg-light-primary me-2">
                            <div class="avatar-content">
                                <i data-feather="trending-up" class="avatar-icon"></i>
                            </div>
                        </div>
                        <div class="my-auto">
                            <h4 class="fw-bolder mb-0">{{ $requestCount }}</h4>
                            <p class="card-text font-small-3 mb-0">Total Requests</p>
                        </div>
                    </div>
                </div>
                <div class="col  mb-2 mb-sm-0">
                    <div class="d-flex flex-row">
                        <div class="avatar bg-light-success me-2">
                            <div class="avatar-content">
                                <i data-feather="user-check" class="avatar-icon"></i>
                            </div>
                        </div>
                        <div class="my-auto">
                            <h4 class="fw-bolder mb-0">{{ $requestForApprovalCount }}</h4>
                            <p class="card-text font-small-3 mb-0">For Approval</p>
                        </div>
                    </div>
                </div>
                <div class="col  mb-2 mb-xl-0">
                    <div class="d-flex flex-row">
                        <div class="avatar bg-light-info me-2">
                            <div class="avatar-content">
                                <i data-feather="check-circle" class="avatar-icon"></i>
                            </div>
                        </div>
                        <div class="my-auto">
                            <h4 class="fw-bolder mb-0">{{ $openRequestCount }}</h4>
                            <p class="card-text font-small-3 mb-0">Request Open</p>
                        </div>
                    </div>
                </div>
                <div class="col  ">
                    <div class="d-flex flex-row">
                        <div class="avatar bg-light-warning me-2">
                            <div class="avatar-content">
                                <i data-feather="calendar" class="avatar-icon"></i>
                            </div>
                        </div>
                        <div class="my-auto">
                            <h4 class="fw-bolder mb-0">{{ $interviewScheduledCount }}</h4>
                            <p class="card-text font-small-3 mb-0">Interview Scheduled</p>
                        </div>
                    </div>
                </div>
                <div class="col  ">
                    <div class="d-flex flex-row">
                        <div class="avatar bg-light-danger me-2">
                            <div class="avatar-content">
                                <i data-feather="x-circle" class="avatar-icon"></i>
                            </div>
                        </div>
                        <div class="my-auto">
                            <h4 class="fw-bolder mb-0">{{ $rejectedRequestCount }}</h4>
                            <p class="card-text font-small-3 mb-0">Rejected Requests</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
