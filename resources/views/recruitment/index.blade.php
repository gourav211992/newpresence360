@extends('recruitment.layouts.app')

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
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row travelexp-summry">

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
                                                                @forelse($jobTtitles as $title)
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
                                    <div class="card">
                                        <div
                                            class="card-header newheader d-flex justify-content-between align-items-start">
                                            <div class="header-left">
                                                <h4 class="card-title">Interview Summary</h4>
                                            </div>
                                            <div class="dropdown">
                                                <div data-bs-toggle="dropdown" class="newcolortheme cursor-pointer"><i
                                                        data-feather='bell' class="me-25"></i> This Month <img
                                                        src="../../../assets/css/down-arrow.png"></div>
                                                <div class="dropdown-menu dropdown-menu-end"
                                                    aria-labelledby="heat-chart-dd">
                                                    <a class="dropdown-item" href="#">Last Month</a>
                                                    <a class="dropdown-item" href="#">Last 3 Months</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div id="radialbar-chart"></div>

                                            <div class="row mt-2">
                                                <div class="col-md-12 leacveprogress totalleave">
                                                    <div class="leavetype-list">
                                                        <h3>Total Qualified</h3>
                                                        <h6>20</h6>
                                                    </div>
                                                    <div class="progress progress-bar-secondary">
                                                        <div class="progress-bar" role="progressbar" aria-valuenow="25"
                                                            aria-valuemin="25" aria-valuemax="100"
                                                            aria-describedby="example-caption-2"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 leacveprogress">
                                                    <div class="leavetype-list">
                                                        <h3>Scheduled</h3>
                                                        <h6>12</h6>
                                                    </div>
                                                    <div class="progress progress-bar-primary">
                                                        <div class="progress-bar" role="progressbar" aria-valuenow="25"
                                                            aria-valuemin="25" aria-valuemax="100" style="width: 25%"
                                                            aria-describedby="example-caption-2"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 leacveprogress sickleave">
                                                    <div class="leavetype-list">
                                                        <h3>Pending</h3>
                                                        <h6>05</h6>
                                                    </div>
                                                    <div class="progress progress-bar-warning">
                                                        <div class="progress-bar" role="progressbar" aria-valuenow="25"
                                                            aria-valuemin="25" aria-valuemax="100" style="width: 25%"
                                                            aria-describedby="example-caption-2"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 leacveprogress paidleave">
                                                    <div class="leavetype-list">
                                                        <h3>Closed</h3>
                                                        <h6>03</h6>
                                                    </div>
                                                    <div class="progress progress-bar-success">
                                                        <div class="progress-bar" role="progressbar" aria-valuenow="25"
                                                            aria-valuemin="25" aria-valuemax="100" style="width: 25%"
                                                            aria-describedby="example-caption-2"></div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="card">
                                        <div
                                            class="card-header newheader d-flex justify-content-between align-items-start">
                                            <div class="header-left">
                                                <h4 class="card-title">New Applicants</h4>
                                            </div>
                                            <div class="dropdown">
                                                <div data-bs-toggle="dropdown" class="newcolortheme cursor-pointer">Today
                                                    <img src="../../../assets/css/down-arrow.png">
                                                </div>
                                                <div class="dropdown-menu dropdown-menu-end"
                                                    aria-labelledby="heat-chart-dd">
                                                    <a class="dropdown-item" href="#">Last Week</a>
                                                    <a class="dropdown-item" href="#">Last Month</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="card-employee-task">
                                                @forelse($applicants as $applicant)
                                                    <div
                                                        class="employee-task d-flex justify-content-between align-items-center">
                                                        <div class="d-flex flex-row">
                                                            <div
                                                                style="background-color: #ddb6ff; color: #6b12b7; line-height: 30px; width: 30px; height: 30px; border-radius: 50%; position: relative; font-size: 1rem; text-align: center; margin-right: 5px; font-weight: 600;">
                                                                {{ strtoupper(substr($applicant->name, 0, 1)) }}
                                                            </div>
                                                            <div class="my-auto text-dark">
                                                                <h6 class="mb-0 fw-bolder text-dark">
                                                                    {{ $applicant->name }}</h6>
                                                                <small>Applied for
                                                                    <strong>{{ $applicant->jobDetail->job_title_name }}</strong></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div
                                                        class="employee-task d-flex justify-content-between align-items-center">
                                                        <h5>No data found</h5>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 right-calendar">
                            <div class="card card-profile border">
                                <img src="../../../app-assets/images/banner/banner-12.jpg" class="img-fluid card-img-top"
                                    alt="Profile Cover Photo" />
                                <div class="card-body">
                                    <div class="profile-image-wrapper">
                                        <div class="profile-image">
                                            <div class="avatar">
                                                <img src="../../../app-assets/images/portrait/small/avatar-s-9.jpg"
                                                    alt="Profile Picture" />
                                            </div>
                                        </div>
                                    </div>
                                    <h3>Shubham Diwedi</h3>
                                    <h6 class="text-muted">Assistant Manager (IT)</h6>
                                </div>
                            </div>

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

                            <div class="newheader border-bottom pb-50  mt-5 mb-2 pb-25">
                                <h4 class="card-title text-primary-new">Activity History/Scheduled</h4>
                            </div>

                            <div class=" employee-task card-employee-tasknew2">

                                <ul class="timeline">
                                    <li class="timeline-item">
                                        <span class="timeline-point timeline-point-primary">
                                            <i data-feather="user"></i>
                                        </span>
                                        <div class="timeline-event">
                                            <div
                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                <h6 class="mb-50 text-dark"><strong>Assisant Manager</strong></h6>
                                                <span class="timeline-event-time">10 min ago</span>
                                            </div>
                                            <p class="font-small-3"><strong>1st</strong> Round Interview with
                                                <strong>Deepak Kumar</strong> on <strong>17-12-2024</strong> at
                                                <strong>2:00</strong> PM
                                            </p>


                                            <div class="d-flex flex-row align-items-center">
                                                <div class="avatar">
                                                    <img src="../../../app-assets/images/avatars/12-small.png"
                                                        alt="avatar" height="38" width="38" />
                                                </div>
                                                <div class="ms-50">
                                                    <h6 class="mb-0">Ashish Kumar (IT)</h6>
                                                    <span class="font-small-2">Team Lead</span>
                                                </div>
                                            </div>

                                            <div
                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1 mt-1">
                                                <div class="d-flex align-items-center cursor-pointer mt-sm-0 mt-50">
                                                    <i data-feather="video" class="me-1"></i>
                                                    <i data-feather="mail" class="me-1"></i>
                                                    <i data-feather="phone-call"></i>
                                                </div>
                                            </div>
                                            <hr />
                                        </div>
                                    </li>

                                    <li class="timeline-item">
                                        <span class="timeline-point timeline-point-primary">
                                            <i data-feather="user"></i>
                                        </span>
                                        <div class="timeline-event">
                                            <div
                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                <h6 class="mb-50 text-dark"><strong>Assisant Manager</strong></h6>
                                                <span class="timeline-event-time">1 hr ago</span>
                                            </div>
                                            <p class="font-small-3"><strong>1st</strong> Round Interview with
                                                <strong>Deepak Kumar</strong> on <strong>17-12-2024</strong> at
                                                <strong>2:00</strong> PM
                                            </p>


                                            <div>
                                                <span class="text-muted">Panel List</span>
                                                <div class="avatar-group mt-50">
                                                    <div data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                        data-bs-placement="top" title="Deepak (IT)"
                                                        class="avatar pull-up">
                                                        <img src="../../../app-assets/images/portrait/small/avatar-s-5.jpg"
                                                            alt="Avatar" height="30" width="30" />
                                                    </div>
                                                    <div data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                        data-bs-placement="top" title="Neha (HR)" class="avatar pull-up">
                                                        <img src="../../../app-assets/images/portrait/small/avatar-s-7.jpg"
                                                            alt="Avatar" height="30" width="30" />
                                                    </div>
                                                    <div data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                        data-bs-placement="top" title="Soni (React)"
                                                        class="avatar pull-up">
                                                        <img src="../../../app-assets/images/portrait/small/avatar-s-10.jpg"
                                                            alt="Avatar" height="30" width="30" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1 mt-1">
                                                <div class="d-flex align-items-center cursor-pointer mt-sm-0 mt-50">
                                                    <i data-feather="video" class="me-1"></i>
                                                    <i data-feather="mail" class="me-1"></i>
                                                    <i data-feather="phone-call"></i>
                                                </div>
                                            </div>
                                            <hr />
                                        </div>
                                    </li>

                                    <li class="timeline-item">
                                        <span class="timeline-point timeline-point-warning">
                                            <i data-feather="user"></i>
                                        </span>
                                        <div class="timeline-event">
                                            <div
                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                <h6 class="mb-50 text-dark"><strong>PHP Developer</strong></h6>
                                                <span class="timeline-event-time">45 min ago</span>
                                            </div>
                                            <p class="font-small-3"><strong>2nd</strong> Round Interview with
                                                <strong>Aniket Singh</strong> on <strong>17-12-2024</strong> at
                                                <strong>2:00</strong> PM
                                            </p>


                                            <div class="d-flex flex-row align-items-center">
                                                <div class="avatar">
                                                    <img src="../../../app-assets/images/avatars/12-small.png"
                                                        alt="avatar" height="38" width="38" />
                                                </div>
                                                <div class="ms-50">
                                                    <h6 class="mb-0">Brijesh Singh (IT)</h6>
                                                    <span class="font-small-2">Manager</span>
                                                </div>
                                            </div>

                                            <div
                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1 mt-1">
                                                <div class="d-flex align-items-center cursor-pointer mt-sm-0 mt-50">
                                                    <i data-feather="video" class="me-1"></i>
                                                    <i data-feather="mail" class="me-1"></i>
                                                    <i data-feather="phone-call"></i>
                                                </div>
                                            </div>
                                            <hr />
                                        </div>
                                    </li>

                                    <li class="timeline-item">
                                        <span class="timeline-point timeline-point-secondary">
                                            <i data-feather="user"></i>
                                        </span>
                                        <div class="timeline-event">
                                            <div
                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                <h6 class="mb-50 text-dark"><strong>Accounts</strong></h6>
                                                <span class="timeline-event-time">50 min ago</span>
                                            </div>
                                            <p class="font-small-3"><strong>3rd</strong> Round Interview with <strong>Diwan
                                                    Singh</strong> on <strong>17-12-2024</strong> at <strong>2:00</strong>
                                                PM</p>


                                            <div class="d-flex flex-row align-items-center">
                                                <div class="avatar">
                                                    <img src="../../../app-assets/images/avatars/12-small.png"
                                                        alt="avatar" height="38" width="38" />
                                                </div>
                                                <div class="ms-50">
                                                    <h6 class="mb-0">Neha Singh (HR)</h6>
                                                    <span class="font-small-2">Associate</span>
                                                </div>
                                            </div>

                                            <div
                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1 mt-1">
                                                <div class="d-flex align-items-center cursor-pointer mt-sm-0 mt-50">
                                                    <i data-feather="video" class="me-1"></i>
                                                    <i data-feather="mail" class="me-1"></i>
                                                    <i data-feather="phone-call"></i>
                                                </div>
                                            </div>
                                            <hr />
                                        </div>
                                    </li>




                                </ul>

                            </div>

                        </div>
                    </div>

                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/charts/chart.min.js') }}"></script>
    <script src="{{ asset('app-assets/js/scripts/cards/card-advance.js') }}"></script>
    <script src="{{ asset('app-assets/js/scripts/charts/chart-chartjs.js') }}"></script>
    <script src="{{ asset('app-assets/js/scripts/charts/chart-apex.js') }}"></script>
    <script src="{{ asset('app-assets/js/scripts/cards/card-statistics.js') }}"></script>
    <script src="{{ asset('app-assets/js/scripts/pages/dashboard-ecommerce.js') }}"></script>
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
                eventClick: function(event, jsEvent, view) {
                    alert();
                },
                //dateClick: function(info) {
                //alert();
                //},
                eventContent: function(info) {
                    return {
                        html: info.event.title
                    };
                },
                events: [{
                        title: '<div class="dotcirclatt"></div>',
                        start: '2024-12-01'
                    },
                    {
                        title: '<div class="dotcirclatt previous"></div>',
                        start: '2024-12-02'
                    },

                    {
                        title: '<div class="dotcirclatt previous"></div>',
                        start: '2024-12-05'
                    },
                    {
                        title: '<div class="dotcirclatt present"></div>',
                        start: '2024-12-06'
                    },
                    {
                        title: '<div class="dotcirclatt absent"></div>',
                        start: '2024-12-07'
                    },
                    {
                        title: '<div class="dotcirclatt"></div>',
                        start: '2024-12-08'
                    },
                    {
                        title: '<div class="dotcirclatt"></div>',
                        start: '2024-12-15'
                    },
                    {
                        title: '<div class="dotcirclatt"></div>',
                        start: '2024-12-22'
                    },

                    {
                        title: '<div class="dotcirclatt present"></div>',
                        start: '2024-12-24'
                    },
                    {
                        title: '<div class="dotcirclatt present"></div>',
                        start: '2024-12-27'
                    },
                    {
                        title: '<div class="dotcirclatt present"></div>',
                        start: '2024-12-26'
                    },

                    {
                        title: '<div class="dotcirclatt"></div>',
                        start: '2024-12-29'
                    },
                    {
                        title: '<div class="dotcirclatt"></div>',
                        start: '2024-12-14'
                    },
                    {
                        title: '<div class="dotcirclatt"></div>',
                        start: '2024-12-28'
                    }
                ]
            });

            calendar.render();
        });
    </script>
@endsection
