@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <div class="content-body manager-dashboard">

                <!-- ChartJS section start -->
                <section id="chartjs-chart">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="row match-height">

                                <div class="col-md-12">
                                    <div class="content-header row">
                                        <div class="content-header-left col-md-6 col-4 mb-2">
                                            <div class="row breadcrumbs-top">
                                                <div class="col-12">
                                                    <h2 class="content-header-title float-start mb-0">Dashboard</h2>
                                                    <div class="breadcrumb-wrapper">
                                                        <ol class="breadcrumb">
                                                            <li class="breadcrumb-item">
                                                                {{ request('date_range') ? request('date_range') : 'As on ' . date('d-m-Y') }}
                                                            </li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="content-header-right text-end col-md-6 col-8">
                                            <div class="form-group breadcrumb-right">
                                                <button class="btn btn-primary box-shadow-2 btn-sm" data-bs-target="#filter"
                                                    data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                                                <button class="btn btn-primary box-shadow-2 btn-sm"
                                                    data-bs-target="#setting" data-bs-toggle="modal"><i
                                                        data-feather="settings"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row travelexp-summry">
                                        @if ($configuration && $configuration->current_opening)
                                            <div class="col-md-3">
                                                <div class="card card-statistics">
                                                    <div class="card-body statistics-body">
                                                        <div class="d-flex flex-row justify-content-between">
                                                            <div class="my-auto">
                                                                <h4 class="fw-bolder mb-0"><a
                                                                        href="{{ route('recruitment.internal-jobs') }}">{{ $currentOpeningCount }}</a>
                                                                </h4>
                                                                <p class="card-text mb-0">Current Opening</p>
                                                            </div>
                                                            <div>
                                                                <div class="avatar bg-light-info">
                                                                    <div class="avatar-content">
                                                                        <i data-feather="file-text" class="avatar-icon"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-md-3">
                                            <div class="card card-statistics">
                                                <div class="card-body statistics-body">
                                                    <div class="d-flex flex-row justify-content-between">
                                                        <div class="my-auto">
                                                            <h4 class="fw-bolder mb-0"><a
                                                                    href="{{ route('recruitment.requests') }}">{{ $totalRequestCount }}</a>
                                                            </h4>
                                                            <p class="card-text mb-0">Total Request</p>
                                                        </div>
                                                        <div>
                                                            <div class="avatar bg-light-primary">
                                                                <div class="avatar-content">
                                                                    <i data-feather="trending-up" class="avatar-icon"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card card-statistics">
                                                <div class="card-body statistics-body">
                                                    <div class="d-flex flex-row justify-content-between">
                                                        <div class="my-auto">
                                                            <h4 class="fw-bolder mb-0">{{ $selectedCount }}</h4>
                                                            <p class="card-text mb-0">Selected</p>
                                                        </div>
                                                        <div>
                                                            <div class="avatar bg-light-success">
                                                                <div class="avatar-content">
                                                                    <i data-feather="user-check" class="avatar-icon"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="card card-statistics">
                                                <div class="card-body statistics-body">
                                                    <div class="d-flex flex-row justify-content-between">
                                                        <div class="my-auto">
                                                            <h4 class="fw-bolder mb-0">{{ $requestForApprovalCount }}</h4>
                                                            <p class="card-text mb-0">For my Approval</p>
                                                        </div>
                                                        <div>
                                                            <div class="avatar bg-light-danger">
                                                                <div class="avatar-content">
                                                                    <i data-feather="check-circle" class="avatar-icon"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8 col-12">
                                    <div class="card">
                                        <div class="card-header newheader d-flex justify-content-between align-items-start">
                                            <div class="header-left">
                                                <h4 class="card-title">Active Jobs</h4>
                                                <p class="card-text">info Details</p>
                                            </div>
                                        </div>
                                        <div class="card-body customernewsection-form activejob">
                                            <div class="row">
                                                @forelse ($activeJobs as $job)
                                                    <div class="col-md-6 newdashtask-overytime">
                                                        <div class="card task-card-body">
                                                            <div class="card-body">
                                                                <h3>{{ $loop->index + 1 }}.
                                                                    {{ $job->job_title_name }}</h3>
                                                                <div class="task-avtarboxpaper">
                                                                    <h4>
                                                                        <span>
                                                                            <i data-feather='box' class="text-info"></i>
                                                                            {{ $job->totalAssginedCandidate }}
                                                                            Candidate</span>
                                                                        <span>
                                                                            <i data-feather='check-circle'
                                                                                class="text-danger"></i>
                                                                            {{ $job->qualifiedCanidatesCount }} Shortlist
                                                                        </span>
                                                                        <br /><br />
                                                                        <span>
                                                                            <i data-feather='file-text'
                                                                                class="text-warning"></i>
                                                                            {{ $job->onholdCanidatesCount }} Hold
                                                                        </span>
                                                                        <span><i data-feather='calendar'
                                                                                class="text-success"></i>
                                                                            {{ $job->selectedCandidateCount }} Selected
                                                                        </span>
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                @empty
                                                @endforelse
                                            </div>

                                            <div class="row mt-1 align-items-center">
                                                <div class="col-md-12">
                                                    <div
                                                        class="table-responsive mt-2 candidates-tables border rounded manager-dash-data">
                                                        <table
                                                            class="datatables-basic table table-striped myrequesttablecbox loanapplicationlist">
                                                            <thead>
                                                                <tr>
                                                                    <th>Job Title</th>
                                                                    <th>Application</th>
                                                                    <th>Open</th>
                                                                    <th>Closed</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($jobTitles as $title)
                                                                    <tr>
                                                                        <td>{{ $title->title }}</td>
                                                                        <td><span
                                                                                class="badge rounded-pill badge-light-success badgeborder-radius">{{ $title->requestCount }}</span>
                                                                        </td>
                                                                        <td><span
                                                                                class="badge rounded-pill badge-light-warning badgeborder-radius">{{ $title->openJobCount }}</span>
                                                                        </td>
                                                                        <td><span
                                                                                class="badge rounded-pill badge-light-danger badgeborder-radius">{{ $title->closedJobCount }}</span>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                @endforelse


                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                            </div>


                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-4 col-12">
                                    @if ($configuration && $configuration->interview_summary)
                                        <div class="card">
                                            <div
                                                class="card-header newheader d-flex justify-content-between align-items-start">
                                                <div class="header-left">
                                                    <h4 class="card-title">Interview Summary</h4>
                                                </div>
                                                <div class="dropdown">
                                                    <div data-bs-toggle="dropdown" class="newcolortheme cursor-pointer">
                                                        <i data-feather='bell' class="me-25"></i>
                                                        <span id="dropdown-label">This Month</span>
                                                        <img src="{{ asset('assets/css/down-arrow.png') }}">
                                                    </div>
                                                    <div class="dropdown-menu dropdown-menu-end"
                                                        aria-labelledby="heat-chart-dd">
                                                        <a class="dropdown-item" href="#"
                                                            onclick="updateDropdownLabel('This Month','dropdown-label'); fetchInterviewSummary('this_month')">This
                                                            Month</a>
                                                        <a class="dropdown-item" href="#"
                                                            onclick="updateDropdownLabel('Last Month','dropdown-label'); fetchInterviewSummary('last_month')">Last
                                                            Month</a>
                                                        <a class="dropdown-item" href="#"
                                                            onclick="updateDropdownLabel('Last 3 Month','dropdown-label'); fetchInterviewSummary('last_3_month')">Last
                                                            3
                                                            Months</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div id="radialbar-chart"></div>
                                                <div class="row mt-2" id="interview-summary-section"></div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($configuration && $configuration->new_applicants)
                                        <div class="card">
                                            <div
                                                class="card-header newheader d-flex justify-content-between align-items-start">
                                                <div class="header-left">
                                                    <h4 class="card-title">New Applicants</h4>
                                                </div>
                                                <div class="dropdown">
                                                    <div data-bs-toggle="dropdown" class="newcolortheme cursor-pointer">
                                                        <span id="applicant-label">Today</span>
                                                        <img src="{{ asset('assets/css/down-arrow.png') }}">
                                                    </div>
                                                    <div class="dropdown-menu dropdown-menu-end"
                                                        aria-labelledby="heat-chart-dd">
                                                        <a class="dropdown-item" href="#"
                                                            onclick="updateDropdownLabel('Today','applicant-label'); fetchApplicantList('last_week')">Today</a>
                                                        <a class="dropdown-item" href="#"
                                                            onclick="updateDropdownLabel('Last Week','applicant-label'); fetchApplicantList('last_week')">Last
                                                            Week</a>
                                                        <a class="dropdown-item" href="#"
                                                            onclick="updateDropdownLabel('Last Month','applicant-label'); fetchApplicantList('last_month')">Last
                                                            Month</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="card-employee-task" id="applicants-section">

                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 right-calendar">
                            <div class="card card-profile border">
                                <img src="{{ asset('app-assets/images/banner/banner-12.jpg') }}"
                                    class="img-fluid card-img-top" alt="Profile Cover Photo" />
                                <div class="card-body">
                                    <div class="profile-image-wrapper">
                                        <div class="profile-image">
                                            <div class="avatar">
                                                <div
                                                    style="background-color: #ddb6ff; color: #6b12b7; line-height: 62px; width: 70px; height: 70px; border-radius: 50%; position: relative; font-size: 2rem; text-align: center; font-weight: 600;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h3>{{ $user->name }}</h3>
                                    <h6 class="text-muted">Assistant Manager (IT)</h6>
                                </div>
                            </div>

                            @if ($configuration && $configuration->my_scheduled)
                                <div class="newheader border-bottom pb-50  mt-5 mb-2 pb-25">
                                    <h4 class="card-title text-primary-new">My Scheduled</h4>
                                </div>

                                <div class="calbg">
                                    <div id="calendar"></div>
                                </div>

                                <div class="row leave-indicator myatteandance-leave">
                                    <div class="col-4">
                                        <div class="presentleave"><span></span> Interview</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="sickleave"><span></span> Previous</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="holyleave"><span></span> Holiday</div>
                                    </div>
                                </div>
                            @endif

                            @if ($configuration && $configuration->activity)
                                <div class="newheader border-bottom pb-50  mt-5 mb-2 pb-25">
                                    <h4 class="card-title text-primary-new">Activity History/Scheduled</h4>
                                </div>

                                <div class=" employee-task card-employee-tasknew2">

                                    <ul class="timeline">
                                        @forelse($interviewLogs as $log)
                                            <li class="timeline-item">
                                                <span class="timeline-point timeline-point-primary">
                                                    <i data-feather="user"></i>
                                                </span>
                                                <div class="timeline-event">
                                                    <div
                                                        class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                        <h6 class="mb-50 text-dark">
                                                            <strong>{{ isset($log->job->job_title_name) ? $log->job->job_title_name : '' }}</strong>
                                                            <!-- Adjust this field name based on your DB -->
                                                        </h6>
                                                        <span class="timeline-event-time">
                                                            {{ isset($log->interview->date_time) ? $log->interview->date_time->diffForHumans() : 'No Date Available' }}
                                                        </span>
                                                    </div>
                                                    <p class="font-small-3">
                                                        <strong>{{ isset($log->interview->round_name) ? $log->interview->round_name : '' }}</strong>
                                                        Round Interview with
                                                        <strong>{{ isset($log->interview->candidate_name) ? $log->interview->candidate_name : '' }}</strong>
                                                        on
                                                        <strong>{{ isset($log->interview->date_time) ? App\Helpers\CommonHelper::dateFormat($log->interview->date_time) : 'N/A' }}</strong>
                                                        at
                                                        <strong>{{ isset($log->interview->date_time) ? App\Helpers\CommonHelper::timeFormat($log->interview->date_time) : 'N/A' }}</strong>
                                                    </p>
                                                    <div>
                                                        <span class="text-muted">Panel List</span>
                                                        <div class="avatar-group mt-50">
                                                            @forelse($log->panels as $panel)
                                                                <div data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                                    data-bs-placement="top" title="{{ $panel->name }}"
                                                                    class="avatar pull-up">
                                                                    <div
                                                                        style="background-color: #ddb6ff; color: #6b12b7; line-height: 30px; width: 30px; height: 30px; border-radius: 50%; position: relative; font-size: 1rem; text-align: center; margin-right: 5px; font-weight: 600;">
                                                                        {{ strtoupper(substr($panel->name, 0, 1)) }}
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <div class="text-muted">No panels available</div>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                    <hr />
                                                </div>
                                            </li>
                                        @empty
                                            <div class="text-center">No interview logs available.</div>
                                        @endforelse
                                    </ul>

                                </div>
                            @endif

                        </div>
                    </div>

                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->

    <!-- Modal for Interview Details -->
    <div class="modal fade" id="interviewModal" tabindex="-1" aria-labelledby="interviewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-primary-new" id="interviewModalLabel"><strong>Interview Details</strong>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Candidate Name:</strong> <span id="candidateName"></span></p>
                    <p><strong>Interview Link:</strong> <span id="interviewLink"></span></p>
                    <p><strong>Scheduled Time:</strong> <span id="interviewTime"></span></p>
                    <p><strong>Status:</strong> <span id="interviewStatus"></span></p>
                    <!-- Add other details you need -->
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary waves-effect"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Filter -->
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="apply-filter add-new-record modal-content pt-0">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date Range</label>
                        <input type="text" id="fp-range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" name="date_range"
                            value="{{ request('date_range') }}" />
                    </div>


                    {{-- <div class="mb-1">
                        <label class="form-label">Select Job</label>
                        <select class="form-select select2">
                            <option>Select</option>
                        </select>
                    </div> --}}


                    {{-- <div class="mb-1">
                        <label class="form-label">Select Status</label>
                        <select class="form-select select2">
                            <option>Qualified</option>
                            <option>Non-Qualified</option>
                            <option>On Hold</option>
                        </select>
                    </div> --}}

                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="button" class="btn btn-outline-secondary" id="clear-filters">Reset</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Setting modal --}}
    <div class="modal modal-slide-in fade filterpopuplabel" id="setting">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0" role="post-data" method="POST" id="status-form"
                action="{{ route('recruitment.user-configuration') }}" redirect="{{ route('recruitment.dashboard') }}"
                data-message='Do you want to update configuration?'>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <label class="form-label fw-bolder text-dark mt-1 mb-50">Show/Hide Module Setting</label>

                    <div class="candidates-tables">
                        <table
                            class="datatables-basic table table-striped myrequesttablecbox loanapplicationlist tasklist">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Module</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td class="text-dark fw-bolder">Current Opening</td>
                                    <td>
                                        <div class="form-check form-check-primary form-switch">
                                            <input name="current_opening" type="checkbox" value="1"
                                                class="form-check-input"
                                                {{ @$configuration->current_opening == 1 ? 'checked' : '' }} />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td class="text-dark fw-bolder">Interview Summary</td>
                                    <td>
                                        <div class="form-check form-check-primary form-switch">
                                            <input name="interview_summary" type="checkbox" value="1"
                                                class="form-check-input"
                                                {{ @$configuration->interview_summary == 1 ? 'checked' : '' }} />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td class="text-dark fw-bolder">My Scheduled</td>
                                    <td>
                                        <div class="form-check form-check-primary form-switch">
                                            <input name="my_scheduled" type="checkbox" value="1"
                                                class="form-check-input"
                                                {{ @$configuration->my_scheduled == 1 ? 'checked' : '' }} />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td class="text-dark fw-bolder">Activity History/Scheduled</td>
                                    <td>
                                        <div class="form-check form-check-primary form-switch">
                                            <input name="activity" type="checkbox" value="1"
                                                class="form-check-input"
                                                {{ @$configuration->activity == 1 ? 'checked' : '' }} />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td class="text-dark fw-bolder">New Applicants</td>
                                    <td>
                                        <div class="form-check form-check-primary form-switch">
                                            <input name="new_applicants" type="checkbox" value="1"
                                                class="form-check-input"
                                                {{ @$configuration->new_applicants == 1 ? 'checked' : '' }} />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="modal-footer justify-content-start">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="button" class="btn btn-primary" data-request="confirm-and-save"
                        data-target="[role=post-data]">Submit</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/calendar/fullcalendar.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'prev,next'
                },

                editable: true,
                dayMaxEvents: true, // allow "more" link when too many events
                eventClick: function(info) {
                    // Retrieve interview details from the event
                    const interviewLink = info.event.extendedProps.interviewLink;
                    const interviewTime = info.event.extendedProps.interviewTime;
                    const status = info.event.extendedProps.status;
                    const candidateName = info.event.extendedProps.candidateName;
                    const eventType = info.event.extendedProps.holiday;
                    if (!eventType) {
                        // Set the modal data
                        $('#interviewLink').text(interviewLink);
                        $('#interviewTime').text(interviewTime);
                        $('#interviewStatus').text(status);
                        $('#candidateName').text(candidateName);

                        // Show the modal
                        $('#interviewModal').modal('show');
                    }
                },
                eventContent: function(info) {
                    const color = info.event.backgroundColor;
                    const title = info.event.title;
                    const start = info.event.start;
                    return {
                        html: `<div class="dotcirclatt ${color}" title="${title}"></div>`,
                    };
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    $.ajax({
                        url: "{{ route('recruitment.interview-events') }}",
                        type: 'GET',
                        cache: true,
                        data: {
                            start: fetchInfo.startStr,
                            end: fetchInfo.endStr
                        },
                        success: function(response) {
                            successCallback(response);
                        }
                    });

                }
            });

            calendar.render();
        });
    </script>

    {{-- For applcants --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetchApplicantList();
        });

        function fetchApplicantList(type = 'today') {
            const url = `{{ route('recruitment.fetch-applicants') }}`;

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    type: type
                },
                success: function(response) {
                    $('#applicants-section').html(response); // You can adjust this to target the right tab
                },
                error: function(xhr) {
                    console.error('Failed to load applicants');
                }
            });
        }
    </script>

    {{-- For Interview Summary --}}
    <script>
        let radialChart;
        document.addEventListener("DOMContentLoaded", function() {
            fetchInterviewSummary();
        });

        function fetchInterviewSummary(type = 'this_month') {
            const url = `{{ route('recruitment.fetch-interview-summary') }}`;

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    type: type
                },
                success: function(response) {
                    updateRadialChart([
                        response.scheduledCount,
                        response.selectedCount,
                        response.rejectCount,
                        response.holdCount,
                    ]);

                    $('#interview-summary-section').html(response.html);
                },
                error: function(xhr) {
                    console.error('Failed to load applicants');
                }
            });
        }

        function updateDropdownLabel(label, id) {
            document.getElementById(id).textContent = label;
        }

        function updateRadialChart(seriesData) {
            const options = {
                chart: {
                    height: 350,
                    type: 'radialBar'
                },
                colors: ['#7367f0', '#28c76f', '#ff6f61', '#ff9f43'],
                plotOptions: {
                    radialBar: {
                        size: 185,
                        hollow: {
                            size: '25%'
                        },
                        track: {
                            margin: 15
                        },
                        dataLabels: {
                            name: {
                                fontSize: '1rem'
                            },
                            value: {
                                fontSize: '1rem'
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function() {
                                    return seriesData.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                },
                legend: {
                    show: true,
                    position: 'bottom'
                },
                stroke: {
                    lineCap: 'round'
                },
                series: seriesData,
                labels: ['Scheduled', 'Selected', 'Rejected', 'On Hold']
            };

            if (radialChart) {
                radialChart.updateOptions(options);
            } else {
                radialChart = new ApexCharts(document.querySelector('#radialbar-chart'), options);
                radialChart.render();
            }
        }
    </script>
@endsection
